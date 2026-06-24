---
name: lexoffice-laravel
description: Sync Laravel models with Lexoffice — contacts, invoices, and products via event-driven queue jobs, webhook handling, and direct API access.
---

# Lexoffice Laravel Development

## When to use this skill

Use this skill when working with Lexoffice integration tasks, including syncing contacts, invoices, or products to Lexoffice, receiving Lexoffice webhooks, or making direct Lexoffice API calls.

## Installation

```bash
composer require hoheiselIT/lexoffice-laravel
php artisan vendor:publish --tag=lexoffice-config
php artisan migrate
```

Required `.env` keys:

```env
LEXOFFICE_API_KEY=your-api-key
LEXOFFICE_QUEUE=lexoffice
LEXOFFICE_WEBHOOK_SECRET=your-webhook-secret
```

## Syncing a Model

Add `HasLexofficeSync` trait and implement the matching contract. The trait auto-dispatches a queue job on every `saved` event.

### Contact sync

```php
use HoheiselIT\Lexoffice\Contracts\SyncableContact;
use HoheiselIT\Lexoffice\Traits\HasLexofficeSync;

class Customer extends Model implements SyncableContact
{
    use HasLexofficeSync;

    public function toLexofficeContact(): array
    {
        return [
            'roles' => ['customer' => []],
            'company' => ['name' => $this->company_name],
            'emailAddresses' => ['business' => [$this->email]],
        ];
    }

    public function getLexofficeId(): ?string
    {
        return $this->lexoffice_id;
    }

    public function setLexofficeId(string $id): void
    {
        $this->update(['lexoffice_id' => $id]);
    }
}
```

### Invoice sync

```php
use HoheiselIT\Lexoffice\Contracts\SyncableInvoice;
use HoheiselIT\Lexoffice\Traits\HasLexofficeSync;

class Order extends Model implements SyncableInvoice
{
    use HasLexofficeSync;

    public function toLexofficeInvoice(): array
    {
        return [
            'voucherDate' => $this->created_at->toIso8601String(),
            'address' => [
                'name' => $this->customer->company_name,
                'contactId' => $this->customer->lexoffice_id,
            ],
            'lineItems' => $this->items->map(fn ($item) => [
                'type' => 'custom',
                'name' => $item->name,
                'quantity' => $item->quantity,
                'unitName' => 'Stück',
                'unitPrice' => [
                    'currency' => 'EUR',
                    'netAmount' => $item->price_net,
                    'taxRatePercentage' => 19,
                ],
            ])->toArray(),
            'taxConditions' => ['taxType' => 'net'],
            'totalPrice' => ['currency' => 'EUR'],
        ];
    }

    public function getLexofficeId(): ?string { return $this->lexoffice_id; }
    public function setLexofficeId(string $id): void { $this->update(['lexoffice_id' => $id]); }
}
```

### Product sync

```php
use HoheiselIT\Lexoffice\Contracts\SyncableProduct;
use HoheiselIT\Lexoffice\Traits\HasLexofficeSync;

class Product extends Model implements SyncableProduct
{
    use HasLexofficeSync;

    public function toLexofficeProduct(): array
    {
        return [
            'title' => $this->name,
            'description' => $this->description,
            'type' => 'PRODUCT',
            'unitName' => 'Stück',
            'price' => [
                'netPrice' => $this->price,
                'currency' => 'EUR',
                'leadingPrice' => 'NET',
            ],
        ];
    }

    public function getLexofficeId(): ?string { return $this->lexoffice_id; }
    public function setLexofficeId(string $id): void { $this->update(['lexoffice_id' => $id]); }
}
```

## Manual Sync

```php
// Synchronous (skips queue):
$customer->syncToLexoffice();

// Dispatch to queue:
use HoheiselIT\Lexoffice\Jobs\SyncContactJob;
SyncContactJob::dispatch($customer);
```

## Artisan Command

```bash
# Sync all records:
php artisan lexoffice:sync "App\Models\Customer"

# Sync specific IDs:
php artisan lexoffice:sync "App\Models\Customer" --ids=1 --ids=2
```

## Direct API Access

```php
use HoheiselIT\Lexoffice\Facades\Lexoffice;

// Contacts
Lexoffice::contacts->find('uuid');
Lexoffice::contacts->search(email: 'max@example.com');
Lexoffice::contacts->create([...]);
Lexoffice::contacts->update('uuid', [...]);

// Invoices
$invoice = Lexoffice::invoices->create([...]);
Lexoffice::invoices->finalize($invoice['id']);

// Webhook subscriptions
Lexoffice::webhooks->subscribe('contact.changed', 'https://your-app.com/lexoffice/webhook');
Lexoffice::webhooks->list();
Lexoffice::webhooks->unsubscribe($subscriptionId);
```

## Webhooks

The route `POST /lexoffice/webhook` is auto-registered with `X-Lxo-Signature` HMAC-SHA256 verification.

Listen for typed events in `EventServiceProvider`:

```php
use HoheiselIT\Lexoffice\Events\Webhooks\ContactChanged;
use HoheiselIT\Lexoffice\Events\Webhooks\InvoiceChanged;
use HoheiselIT\Lexoffice\Events\Webhooks\PaymentChanged;

protected $listen = [
    ContactChanged::class => [YourContactListener::class],
    InvoiceChanged::class => [YourInvoiceListener::class],
    PaymentChanged::class => [YourPaymentListener::class],
];
```

Available typed webhook events: `ContactCreated`, `ContactChanged`, `ContactDeleted`, `InvoiceCreated`, `InvoiceChanged`, `InvoiceDeleted`, `PaymentChanged`, `VoucherChanged`.

Unknown event types fall back to `LexofficeWebhookReceived`.

Each event exposes: `$event->eventType`, `$event->resourceId`, `$event->organizationId`, `$event->payload`.

## Sync Log

Every sync attempt is written to `lexoffice_sync_log`:

```php
use HoheiselIT\Lexoffice\Models\LexofficeSyncLog;

LexofficeSyncLog::failed()->latest()->get();
LexofficeSyncLog::forModel(Customer::class, $id)->successful()->latest()->first();
```

## Conventions

- Always store the returned Lexoffice UUID in `lexoffice_id` on the model.
- The `toLexoffice*()` methods must return a valid Lexoffice API payload — check the Lexoffice API docs for required fields.
- On HTTP 429 (`LexofficeRateLimitException`) jobs use exponential backoff: 30s → 60s → 120s. Other errors: 5s → 10s → 20s. Both configurable via `retry.rate_limit_base` and `retry.backoff_base`.
- Never call `toLexoffice*()` or `setLexofficeId()` directly — use the trait or job dispatch pattern.
- Sync can be disabled per type in `config/lexoffice.php` under `sync.contacts`, `sync.invoices`, `sync.products`.
