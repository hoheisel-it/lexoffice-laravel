# lexoffice-laravel

Laravel package for syncing models with [Lexoffice](https://www.lexoffice.de/) — contacts, invoices, and products via event-driven queue jobs.

## Installation

```bash
composer require stefanhoheisel/lexoffice-laravel
```

Publish config:

```bash
php artisan vendor:publish --tag=lexoffice-config
```

Run migrations (optional — for sync log):

```bash
php artisan migrate
```

## Configuration

Add to `.env`:

```env
LEXOFFICE_API_KEY=your-api-key
LEXOFFICE_QUEUE=lexoffice
```

## Usage

### 1. Implement contract on your model

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
            'company' => [
                'name' => $this->company_name,
            ],
            'emailAddresses' => [
                'business' => [$this->email],
            ],
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

Model saves → sync job dispatched automatically.

### 2. Manual sync

```php
$customer->syncToLexoffice(); // synchronous

// or dispatch job manually:
\HoheiselIT\Lexoffice\Jobs\SyncContactJob::dispatch($customer);
```

### 3. Artisan command

```bash
# Sync all customers
php artisan lexoffice:sync "App\Models\Customer"

# Sync specific IDs
php artisan lexoffice:sync "App\Models\Customer" --ids=1 --ids=2 --ids=3
```

### 4. Direct API access

```php
use HoheiselIT\Lexoffice\Facades\Lexoffice;

// contacts
$contact = Lexoffice::contacts->find('uuid');
$contacts = Lexoffice::contacts->search(email: 'foo@bar.com');

// invoices
$invoice = Lexoffice::invoices->create([...]);
Lexoffice::invoices->finalize($invoice['id']);

// vouchers
$list = Lexoffice::vouchers->list();
```

### 5. Listen for sync events

```php
use HoheiselIT\Lexoffice\Events\LexofficeSynced;

Event::listen(LexofficeSynced::class, function (LexofficeSynced $event) {
    // $event->model  — the Laravel model
    // $event->type   — 'contact' | 'invoice' | 'product'
    // $event->response — Lexoffice API response
});
```

### 6. Webhooks

**Receive webhooks from Lexoffice:**

Add to `.env`:

```env
LEXOFFICE_WEBHOOK_SECRET=your-webhook-secret
LEXOFFICE_WEBHOOK_PATH=lexoffice/webhook
```

Route is auto-registered at `POST /lexoffice/webhook` with `X-Lxo-Signature` verification.

**Subscribe via API:**

```php
use HoheiselIT\Lexoffice\Facades\Lexoffice;

Lexoffice::webhooks->subscribe('contact.changed', 'https://your-app.com/lexoffice/webhook');
Lexoffice::webhooks->list();
Lexoffice::webhooks->unsubscribe($subscriptionId);
```

**Listen for webhook events:**

```php
use HoheiselIT\Lexoffice\Events\Webhooks\ContactChanged;
use HoheiselIT\Lexoffice\Events\Webhooks\InvoiceChanged;
use HoheiselIT\Lexoffice\Events\Webhooks\PaymentChanged;

// In EventServiceProvider:
ContactChanged::class  => [YourContactSyncListener::class],
InvoiceChanged::class  => [YourInvoiceSyncListener::class],
PaymentChanged::class  => [YourPaymentListener::class],
```

Available typed events: `ContactCreated`, `ContactChanged`, `ContactDeleted`, `InvoiceCreated`, `InvoiceChanged`, `InvoiceDeleted`, `PaymentChanged`, `VoucherChanged`.

Unknown event types fall back to `LexofficeWebhookReceived`.

### 7. Sync log

Every sync attempt (success or failure) is written to `lexoffice_sync_log`:

```php
use HoheiselIT\Lexoffice\Models\LexofficeSyncLog;

// all failures
LexofficeSyncLog::failed()->latest()->get();

// history for a specific model
LexofficeSyncLog::forModel(App\Models\Customer::class, $id)->get();

// last successful sync
LexofficeSyncLog::forModel(App\Models\Customer::class, $id)->successful()->latest()->first();
```

Failed jobs re-throw the exception so Laravel's queue retry/failed-jobs mechanism still fires.

## License

MIT
