<?php

namespace HoheiselIT\Lexoffice\Api;

use HoheiselIT\Lexoffice\LexofficeClient;

class InvoicesApi
{
    public function __construct(private readonly LexofficeClient $client) {}

    public function list(int $page = 0, int $size = 25): array
    {
        return $this->client->get('invoices', compact('page', 'size'));
    }

    public function find(string $id): array
    {
        return $this->client->get("invoices/{$id}");
    }

    public function create(array $data): array
    {
        return $this->client->post('invoices', $data);
    }

    public function update(string $id, array $data): array
    {
        return $this->client->put("invoices/{$id}", $data);
    }

    /** Finalize invoice so it gets a voucher number. */
    public function finalize(string $id): array
    {
        return $this->client->post("invoices/{$id}/document");
    }

    public function renderDocument(string $id): array
    {
        return $this->client->get("invoices/{$id}/document");
    }
}
