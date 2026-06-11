<?php

namespace Meritum\Serialization;

interface SerializerInterface
{
    /**
     * @return array<string, mixed>
     */
    public function serialize(mixed $data): array;
}
