<?php

namespace Meritum\Serialization\Strategy;

use Meritum\Serialization\EnvelopeInterface;
use Meritum\Serialization\Resource\ItemInterface;
use Meritum\Serialization\Resource\CollectionInterface;

interface StrategyInterface
{
    public function item(ItemInterface $item): EnvelopeInterface;

    public function collection(CollectionInterface $collection): EnvelopeInterface;
}
