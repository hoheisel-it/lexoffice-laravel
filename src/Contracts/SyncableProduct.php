<?php

namespace HoheiselIT\Lexoffice\Contracts;

interface SyncableProduct
{
    /** Returns data formatted for the Lexoffice article/product API. */
    public function toLexofficeProduct(): array;

    /** Returns the stored Lexoffice product ID, or null if not yet synced. */
    public function getLexofficeId(): ?string;

    /** Persists the Lexoffice product ID after successful sync. */
    public function setLexofficeId(string $id): void;
}
