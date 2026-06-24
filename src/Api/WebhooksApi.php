<?php

namespace HoheiselIT\Lexoffice\Api;

use HoheiselIT\Lexoffice\LexofficeClient;

class WebhooksApi
{
    public function __construct(private readonly LexofficeClient $client) {}

    public function list(): array
    {
        return $this->client->get('event-subscriptions');
    }

    public function find(string $id): array
    {
        return $this->client->get("event-subscriptions/{$id}");
    }

    /**
     * @param string $eventType  e.g. 'contact.changed'
     * @param string $callbackUrl  publicly reachable URL
     */
    public function subscribe(string $eventType, string $callbackUrl): array
    {
        return $this->client->post('event-subscriptions', [
            'eventType'   => $eventType,
            'callbackUrl' => $callbackUrl,
        ]);
    }

    public function unsubscribe(string $id): void
    {
        $this->client->delete("event-subscriptions/{$id}");
    }
}
