<?php

namespace HoheiselIT\Lexoffice\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use HoheiselIT\Lexoffice\Events\LexofficeSynced;
use HoheiselIT\Lexoffice\Exceptions\LexofficeRateLimitException;
use HoheiselIT\Lexoffice\LexofficeClient;
use HoheiselIT\Lexoffice\SyncLogger;

abstract class BaseSyncJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries;

    public function __construct()
    {
        $this->tries = config('lexoffice.retry.times', 3);
        $this->onConnection(config('lexoffice.queue.connection'));
    }

    /** Returns exponential delays in seconds: base, base×2, base×4, … */
    public function backoff(): array
    {
        $base = config('lexoffice.retry.backoff_base', 5);

        return array_map(
            fn (int $n) => $base * (2 ** $n),
            range(0, $this->tries - 2)
        );
    }

    public function handle(LexofficeClient $client): void
    {
        $model    = $this->getModel();
        $payload  = $this->getPayload();
        $syncType = $this->getSyncType();

        try {
            $result = $this->sync($client);
            SyncLogger::success($model, $syncType, $payload, $result);
            event(new LexofficeSynced($model, $syncType, $result));
        } catch (LexofficeRateLimitException $e) {
            // Exponential delay independent of normal backoff — API limit hit.
            $base  = config('lexoffice.retry.rate_limit_base', 30);
            $delay = $base * (2 ** ($this->attempts() - 1)); // 30s, 60s, 120s

            SyncLogger::failure($model, $syncType, $payload, $e);
            $this->release($delay);
        } catch (\Throwable $e) {
            SyncLogger::failure($model, $syncType, $payload, $e);
            throw $e;
        }
    }

    abstract protected function sync(LexofficeClient $client): array;

    abstract protected function getModel(): object;

    abstract protected function getPayload(): array;

    abstract protected function getSyncType(): string;
}
