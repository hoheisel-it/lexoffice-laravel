<?php

namespace HoheiselIT\Lexoffice\Jobs;

use HoheiselIT\Lexoffice\Contracts\SyncableProduct;
use HoheiselIT\Lexoffice\LexofficeClient;

class SyncProductJob extends BaseSyncJob
{
    private array $payload;

    public function __construct(private readonly SyncableProduct $model)
    {
        parent::__construct();
        $this->payload = $model->toLexofficeProduct();
    }

    protected function sync(LexofficeClient $client): array
    {
        $id = $this->model->getLexofficeId();

        if ($id) {
            return $client->put("articles/{$id}", $this->payload);
        }

        $result = $client->post('articles', $this->payload);
        $this->model->setLexofficeId($result['id']);

        return $result;
    }

    protected function getModel(): object  { return $this->model; }
    protected function getPayload(): array { return $this->payload; }
    protected function getSyncType(): string { return 'product'; }
}
