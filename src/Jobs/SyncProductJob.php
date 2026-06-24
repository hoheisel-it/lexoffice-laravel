<?php

namespace HoheiselIT\Lexoffice\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use HoheiselIT\Lexoffice\Contracts\SyncableProduct;
use HoheiselIT\Lexoffice\Events\LexofficeSynced;
use HoheiselIT\Lexoffice\LexofficeClient;
use HoheiselIT\Lexoffice\SyncLogger;

class SyncProductJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries;
    public int $backoff;

    public function __construct(private readonly SyncableProduct $model)
    {
        $this->tries = config('lexoffice.retry.times', 3);
        $this->backoff = config('lexoffice.retry.sleep', 5);
        $this->onConnection(config('lexoffice.queue.connection'));
    }

    public function handle(LexofficeClient $client): void
    {
        $lexofficeId = $this->model->getLexofficeId();
        $payload = $this->model->toLexofficeProduct();

        try {
            if ($lexofficeId) {
                $result = $client->put("articles/{$lexofficeId}", $payload);
            } else {
                $result = $client->post('articles', $payload);
                $this->model->setLexofficeId($result['id']);
            }

            SyncLogger::success($this->model, 'product', $payload, $result);
            event(new LexofficeSynced($this->model, 'product', $result));
        } catch (\Throwable $e) {
            SyncLogger::failure($this->model, 'product', $payload, $e);
            throw $e;
        }
    }
}
