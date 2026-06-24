<?php

namespace HoheiselIT\Lexoffice\Contracts;

interface SyncableContact
{
    /** Returns data formatted for the Lexoffice contacts API. */
    public function toLexofficeContact(): array;

    /** Returns the stored Lexoffice contact ID, or null if not yet synced. */
    public function getLexofficeId(): ?string;

    /** Persists the Lexoffice contact ID after successful sync. */
    public function setLexofficeId(string $id): void;
}
