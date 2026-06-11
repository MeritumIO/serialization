<?php

namespace Meritum\Serialization;

use Meritum\Serialization\Resource\ItemInterface;
use Meritum\Serialization\Resource\ResourceInterface;
use Meritum\Serialization\Strategy\StrategyInterface;
use Meritum\Serialization\Resource\CollectionInterface;

final class Formatter implements FormatterInterface
{
    public function __construct(private readonly StrategyInterface $strategy) {}

    public function format(ResourceInterface $resource): EnvelopeInterface
    {
        return match (true) {
            $resource instanceof ItemInterface       => $this->strategy->item($resource),
            $resource instanceof CollectionInterface => $this->strategy->collection($resource),
            default => throw new \InvalidArgumentException('Unknown resource type')
        };
    }
}
