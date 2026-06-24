<?php

namespace HoheiselIT\Lexoffice\Api;

use HoheiselIT\Lexoffice\LexofficeClient;

class ContactsApi
{
    public function __construct(private readonly LexofficeClient $client) {}

    public function list(int $page = 0, int $size = 25): array
    {
        return $this->client->get('contacts', compact('page', 'size'));
    }

    public function find(string $id): array
    {
        return $this->client->get("contacts/{$id}");
    }

    public function search(string $email = '', string $name = ''): array
    {
        $query = array_filter(compact('email', 'name'));

        return $this->client->get('contacts', $query);
    }

    public function create(array $data): array
    {
        return $this->client->post('contacts', $data);
    }

    public function update(string $id, array $data): array
    {
        return $this->client->put("contacts/{$id}", $data);
    }

    public function delete(string $id): void
    {
        $this->client->delete("contacts/{$id}");
    }
}
