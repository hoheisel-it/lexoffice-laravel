<?php

namespace HoheiselIT\Lexoffice\Jobs;

use HoheiselIT\Lexoffice\Contracts\SyncableContact;
use HoheiselIT\Lexoffice\LexofficeClient;

class SyncContactJob extends BaseSyncJob
{
    private array $payload;

    public function __construct(private readonly SyncableContact $model)
    {
        parent::__construct();
        $this->payload = $model->toLexofficeContact();
    }

    protected function sync(LexofficeClient $client): array
    {
        $id = $this->model->getLexofficeId();

        if ($id) {
            return $client->contacts->update($id, $this->payload);
        }

        $result = $client->contacts->create($this->payload);
        $this->model->setLexofficeId($result['id']);

        return $result;
    }

    protected function getModel(): object  { return $this->model; }
    protected function getPayload(): array { return $this->payload; }
    protected function getSyncType(): string { return 'contact'; }
}
