<?php

namespace Meritum\Serialization\Resource;

interface CollectionInterface extends ResourceInterface
{
    /**
     * Get the unserialized data
     *
     * @return iterable<mixed>
     */
    public function getData(): iterable;

    /**
     * @return array<string, null|int|string>
     */
    public function getPagination(): array;
}
