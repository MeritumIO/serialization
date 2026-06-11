<?php

namespace Meritum\Serialization\Resource;

trait MetaTrait
{
    /**
     * @var array<string, mixed>
     */
    private array $meta = [];

    public function hasMeta(): bool
    {
        return [] !== $this->meta;
    }

    /**
     * @return array<string, mixed>
     */
    public function getMeta(): array
    {
        return $this->meta;
    }

    public function addMeta(string $key, mixed $value): static
    {
        $this->meta[$key] = $value;

        return $this;
    }
}
