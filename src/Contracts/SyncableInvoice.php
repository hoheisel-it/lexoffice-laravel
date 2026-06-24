<?php

namespace HoheiselIT\Lexoffice\Contracts;

interface SyncableInvoice
{
    /** Returns data formatted for the Lexoffice invoices API. */
    public function toLexofficeInvoice(): array;

    /** Returns the stored Lexoffice invoice ID, or null if not yet synced. */
    public function getLexofficeId(): ?string;

    /** Persists the Lexoffice invoice ID after successful sync. */
    public function setLexofficeId(string $id): void;
}
