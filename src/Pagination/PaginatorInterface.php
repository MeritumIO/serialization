<?php

namespace Meritum\Serialization\Pagination;

interface PaginatorInterface
{
    public function getCurrentPage(): int;

    public function getLastPage(): int;

    public function getTotal(): int;

    public function getCount(): int;

    public function getPerPage(): int;
}
