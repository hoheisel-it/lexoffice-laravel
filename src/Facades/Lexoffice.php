<?php

namespace HoheiselIT\Lexoffice\Facades;

use Illuminate\Support\Facades\Facade;
use HoheiselIT\Lexoffice\LexofficeClient;

/**
 * @method static \HoheiselIT\Lexoffice\Api\ContactsApi contacts()
 * @method static \HoheiselIT\Lexoffice\Api\InvoicesApi invoices()
 * @method static \HoheiselIT\Lexoffice\Api\VouchersApi vouchers()
 * @method static \HoheiselIT\Lexoffice\Api\WebhooksApi webhooks()
 *
 * @see LexofficeClient
 */
class Lexoffice extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return LexofficeClient::class;
    }
}
