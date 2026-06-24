<?php

namespace HoheiselIT\Lexoffice\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LexofficeWebhookReceived
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly string $eventType,
        public readonly string $resourceId,
        public readonly string $organizationId,
        public readonly array $payload,
    ) {}
}
