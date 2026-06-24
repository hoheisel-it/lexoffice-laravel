<?php

namespace HoheiselIT\Lexoffice;

use HoheiselIT\Lexoffice\Events\LexofficeWebhookReceived;
use HoheiselIT\Lexoffice\Events\Webhooks\ContactChanged;
use HoheiselIT\Lexoffice\Events\Webhooks\ContactCreated;
use HoheiselIT\Lexoffice\Events\Webhooks\ContactDeleted;
use HoheiselIT\Lexoffice\Events\Webhooks\InvoiceChanged;
use HoheiselIT\Lexoffice\Events\Webhooks\InvoiceCreated;
use HoheiselIT\Lexoffice\Events\Webhooks\InvoiceDeleted;
use HoheiselIT\Lexoffice\Events\Webhooks\PaymentChanged;
use HoheiselIT\Lexoffice\Events\Webhooks\VoucherChanged;

class WebhookProcessor
{
    /** Maps Lexoffice eventType strings to typed event classes. */
    private const EVENT_MAP = [
        'contact.created'  => ContactCreated::class,
        'contact.changed'  => ContactChanged::class,
        'contact.deleted'  => ContactDeleted::class,
        'invoice.created'  => InvoiceCreated::class,
        'invoice.changed'  => InvoiceChanged::class,
        'invoice.deleted'  => InvoiceDeleted::class,
        'payment.changed'  => PaymentChanged::class,
        'voucher.changed'  => VoucherChanged::class,
    ];

    public function process(array $payload): void
    {
        $eventType      = $payload['eventType'] ?? 'unknown';
        $resourceId     = $payload['resourceId'] ?? '';
        $organizationId = $payload['organizationId'] ?? '';

        $eventClass = self::EVENT_MAP[$eventType] ?? LexofficeWebhookReceived::class;

        event(new $eventClass($eventType, $resourceId, $organizationId, $payload));
    }
}
