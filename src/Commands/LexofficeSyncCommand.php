<?php

namespace HoheiselIT\Lexoffice\Commands;

use Illuminate\Console\Command;
use HoheiselIT\Lexoffice\Contracts\SyncableContact;
use HoheiselIT\Lexoffice\Contracts\SyncableInvoice;
use HoheiselIT\Lexoffice\Contracts\SyncableProduct;
use HoheiselIT\Lexoffice\Jobs\SyncContactJob;
use HoheiselIT\Lexoffice\Jobs\SyncInvoiceJob;
use HoheiselIT\Lexoffice\Jobs\SyncProductJob;

class LexofficeSyncCommand extends Command
{
    protected $signature = 'lexoffice:sync
        {model : Fully-qualified model class to sync}
        {--ids=* : Specific model IDs to sync (syncs all if omitted)}
        {--queue : Force async dispatch even if sync is configured}';

    protected $description = 'Sync Laravel models to Lexoffice.';

    public function handle(): int
    {
        $modelClass = $this->argument('model');

        if (! class_exists($modelClass)) {
            $this->error("Class [{$modelClass}] not found.");
            return self::FAILURE;
        }

        $ids = $this->option('ids');
        $query = $modelClass::query();

        if (! empty($ids)) {
            $query->whereIn('id', $ids);
        }

        $count = 0;

        $query->each(function ($model) use (&$count) {
            if ($model instanceof SyncableContact) {
                SyncContactJob::dispatch($model)->onQueue(config('lexoffice.queue.name'));
                $count++;
            } elseif ($model instanceof SyncableInvoice) {
                SyncInvoiceJob::dispatch($model)->onQueue(config('lexoffice.queue.name'));
                $count++;
            } elseif ($model instanceof SyncableProduct) {
                SyncProductJob::dispatch($model)->onQueue(config('lexoffice.queue.name'));
                $count++;
            }
        });

        $this->info("Dispatched {$count} sync jobs.");

        return self::SUCCESS;
    }
}
