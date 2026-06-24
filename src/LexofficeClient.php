<?php

namespace HoheiselIT\Lexoffice;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use HoheiselIT\Lexoffice\Api\ContactsApi;
use HoheiselIT\Lexoffice\Api\InvoicesApi;
use HoheiselIT\Lexoffice\Api\VouchersApi;
use HoheiselIT\Lexoffice\Api\WebhooksApi;
use HoheiselIT\Lexoffice\Exceptions\LexofficeApiException;
use HoheiselIT\Lexoffice\Exceptions\LexofficeAuthException;
use HoheiselIT\Lexoffice\Exceptions\LexofficeRateLimitException;

class LexofficeClient
{
    private Client $http;

    public readonly ContactsApi $contacts;
    public readonly InvoicesApi $invoices;
    public readonly VouchersApi $vouchers;
    public readonly WebhooksApi $webhooks;

    public function __construct(private readonly string $apiKey, private readonly string $baseUrl)
    {
        $this->http = new Client([
            'base_uri' => rtrim($baseUrl, '/') . '/',
            'headers' => [
                'Authorization' => 'Bearer ' . $apiKey,
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ],
            'timeout' => 30,
        ]);

        $this->contacts = new ContactsApi($this);
        $this->invoices = new InvoicesApi($this);
        $this->vouchers = new VouchersApi($this);
        $this->webhooks = new WebhooksApi($this);
    }

    public function get(string $endpoint, array $query = []): array
    {
        return $this->request('GET', $endpoint, ['query' => $query]);
    }

    public function post(string $endpoint, array $data = []): array
    {
        return $this->request('POST', $endpoint, ['json' => $data]);
    }

    public function put(string $endpoint, array $data = []): array
    {
        return $this->request('PUT', $endpoint, ['json' => $data]);
    }

    public function delete(string $endpoint): void
    {
        $this->request('DELETE', $endpoint);
    }

    private function request(string $method, string $endpoint, array $options = []): array
    {
        try {
            $response = $this->http->request($method, ltrim($endpoint, '/'), $options);
            $body = (string) $response->getBody();

            return $body !== '' ? json_decode($body, true, 512, JSON_THROW_ON_ERROR) : [];
        } catch (ClientException $e) {
            $status = $e->getResponse()->getStatusCode();

            if ($status === 401) {
                throw new LexofficeAuthException('Invalid Lexoffice API key.', 401, $e);
            }

            if ($status === 429) {
                throw new LexofficeRateLimitException('Lexoffice API rate limit reached.', 429, $e);
            }

            throw new LexofficeApiException(
                "Lexoffice API error [{$status}]: " . $e->getResponse()->getBody(),
                $status,
                $e
            );
        } catch (ServerException $e) {
            throw new LexofficeApiException('Lexoffice server error.', 500, $e);
        }
    }
}
