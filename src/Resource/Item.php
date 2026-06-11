<?php

namespace Meritum\Serialization\Resource;

use Meritum\Serialization\SerializerInterface;

final class Item implements ItemInterface
{
    use MetaTrait;

    public function __construct(
        private readonly mixed $data,
        private readonly SerializerInterface $serializer
    ) {}

    public function getData(): mixed
    {
        return $this->data;
    }

    public function getSerializer(): SerializerInterface
    {
        return $this->serializer;
    }
}
