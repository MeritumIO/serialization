<?php

namespace Meritum\Serialization;

final class Envelope implements EnvelopeInterface
{
    /**
     * @param array<mixed> $data
     */
    public function __construct(private readonly array $data) {}

    public function toArray(): array
    {
        return $this->data;
    }

    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }
}
