<?php

namespace HoheiselIT\Lexoffice\Traits;

use HoheiselIT\Lexoffice\Jobs\SyncContactJob;
use HoheiselIT\Lexoffice\Jobs\SyncInvoiceJob;
use HoheiselIT\Lexoffice\Jobs\SyncProductJob;
use HoheiselIT\Lexoffice\Contracts\SyncableContact;
use HoheiselIT\Lexoffice\Contracts\SyncableInvoice;
use HoheiselIT\Lexoffice\Contracts\SyncableProduct;

trait HasLexofficeSync
{
    public static function bootHasLexofficeSync(): void
    {
        static::saved(function (self $model) {
            $model->dispatchLexofficeSync();
        });

        static::deleted(function (self $model) {
            $model->dispatchLexofficeDelete();
        });
    }

    public function dispatchLexofficeSync(): void
    {
        if ($this instanceof SyncableContact && config('lexoffice.sync.contacts')) {
            SyncContactJob::dispatch($this)->onQueue(config('lexoffice.queue.name'));
        }

        if ($this instanceof SyncableInvoice && config('lexoffice.sync.invoices')) {
            SyncInvoiceJob::dispatch($this)->onQueue(config('lexoffice.queue.name'));
        }

        if ($this instanceof SyncableProduct && config('lexoffice.sync.products')) {
            SyncProductJob::dispatch($this)->onQueue(config('lexoffice.queue.name'));
        }
    }

    protected function dispatchLexofficeDelete(): void
    {
        // Override in model to handle deletion sync if needed.
    }

    /** Sync immediately without queuing. */
    public function syncToLexoffice(): void
    {
        if ($this instanceof SyncableContact) {
            (new SyncContactJob($this))->handle(app(\HoheiselIT\Lexoffice\LexofficeClient::class));
        }

        if ($this instanceof SyncableInvoice) {
            (new SyncInvoiceJob($this))->handle(app(\HoheiselIT\Lexoffice\LexofficeClient::class));
        }

        if ($this instanceof SyncableProduct) {
            (new SyncProductJob($this))->handle(app(\HoheiselIT\Lexoffice\LexofficeClient::class));
        }
    }
}
