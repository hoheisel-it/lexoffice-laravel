<?php

namespace HoheiselIT\Lexoffice\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use HoheiselIT\Lexoffice\Contracts\SyncableContact;
use HoheiselIT\Lexoffice\Events\LexofficeSynced;
use HoheiselIT\Lexoffice\LexofficeClient;
use HoheiselIT\Lexoffice\SyncLogger;

class SyncContactJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries;
    public int $backoff;

    public function __construct(private readonly SyncableContact $model)
    {
        $this->tries = config('lexoffice.retry.times', 3);
        $this->backoff = config('lexoffice.retry.sleep', 5);
        $this->onConnection(config('lexoffice.queue.connection'));
    }

    public function handle(LexofficeClient $client): void
    {
        $lexofficeId = $this->model->getLexofficeId();
        $payload = $this->model->toLexofficeContact();

        try {
            if ($lexofficeId) {
                $result = $client->contacts->update($lexofficeId, $payload);
            } else {
                $result = $client->contacts->create($payload);
                $this->model->setLexofficeId($result['id']);
            }

            SyncLogger::success($this->model, 'contact', $payload, $result);
            event(new LexofficeSynced($this->model, 'contact', $result));
        } catch (\Throwable $e) {
            SyncLogger::failure($this->model, 'contact', $payload, $e);
            throw $e;
        }
    }
}
