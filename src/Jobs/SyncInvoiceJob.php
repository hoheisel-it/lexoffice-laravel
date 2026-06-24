<?php

namespace HoheiselIT\Lexoffice\Jobs;

use HoheiselIT\Lexoffice\Contracts\SyncableInvoice;
use HoheiselIT\Lexoffice\LexofficeClient;

class SyncInvoiceJob extends BaseSyncJob
{
    private array $payload;

    public function __construct(private readonly SyncableInvoice $model)
    {
        parent::__construct();
        $this->payload = $model->toLexofficeInvoice();
    }

    protected function sync(LexofficeClient $client): array
    {
        $id = $this->model->getLexofficeId();

        if ($id) {
            return $client->invoices->update($id, $this->payload);
        }

        $result = $client->invoices->create($this->payload);
        $this->model->setLexofficeId($result['id']);

        return $result;
    }

    protected function getModel(): object  { return $this->model; }
    protected function getPayload(): array { return $this->payload; }
    protected function getSyncType(): string { return 'invoice'; }
}
