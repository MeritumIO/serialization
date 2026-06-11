<?php

namespace Meritum\Serialization\Resource;

interface ItemInterface extends ResourceInterface
{
    /**
     * Get the unserialized data
     */
    public function getData(): mixed;
}
