<?php

namespace HoheiselIT\Lexoffice\Api;

use HoheiselIT\Lexoffice\LexofficeClient;

class VouchersApi
{
    public function __construct(private readonly LexofficeClient $client) {}

    public function list(int $page = 0, int $size = 25): array
    {
        return $this->client->get('voucherlist', [
            'voucherType' => 'invoice,creditnote',
            'voucherStatus' => 'open,overdue,paid,paidoff,voided,transferred',
            'page' => $page,
            'size' => $size,
            'sort' => 'voucherDate,DESC',
        ]);
    }

    public function find(string $id): array
    {
        return $this->client->get("vouchers/{$id}");
    }
}
