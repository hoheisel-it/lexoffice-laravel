<?php

namespace HoheiselIT\Lexoffice\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LexofficeSynced
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly mixed $model,
        public readonly string $type,
        public readonly array $response,
    ) {}
}
