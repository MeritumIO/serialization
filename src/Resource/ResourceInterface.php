<?php

namespace Meritum\Serialization\Resource;

use Meritum\Serialization\SerializerInterface;

interface ResourceInterface
{
    public function getSerializer(): SerializerInterface;

    /**
     * @return array<string, mixed>
     */
    public function getMeta(): array;
}
