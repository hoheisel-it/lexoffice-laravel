<?php

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use HoheiselIT\Lexoffice\LexofficeClient;

test('contacts api resolves on client', function () {
    $client = app(LexofficeClient::class);
    expect($client->contacts)->toBeInstanceOf(\HoheiselIT\Lexoffice\Api\ContactsApi::class);
    expect($client->invoices)->toBeInstanceOf(\HoheiselIT\Lexoffice\Api\InvoicesApi::class);
    expect($client->vouchers)->toBeInstanceOf(\HoheiselIT\Lexoffice\Api\VouchersApi::class);
});

test('facade resolves lexoffice client', function () {
    $facade = app(\HoheiselIT\Lexoffice\LexofficeClient::class);
    expect($facade)->toBeInstanceOf(LexofficeClient::class);
});
