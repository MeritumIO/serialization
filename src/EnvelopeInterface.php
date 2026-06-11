<?php

namespace Meritum\Serialization;

interface EnvelopeInterface extends \JsonSerializable
{
    /**
     * @return array<mixed>
     */
    public function toArray(): array;
}
