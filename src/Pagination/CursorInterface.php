<?php

namespace Meritum\Serialization\Pagination;

interface CursorInterface
{
    public function getPrevious(): ?string;

    public function getNext(): ?string;

    public function getPerPage(): int;
}
