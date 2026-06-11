<?php

namespace Meritum\Serialization\Strategy;

use Meritum\Serialization\Envelope;
use Meritum\Serialization\EnvelopeInterface;
use Meritum\Serialization\Resource\ItemInterface;
use Meritum\Serialization\Resource\CollectionInterface;

final class DataArrayStrategy implements StrategyInterface
{
    public function item(ItemInterface $item): EnvelopeInterface
    {
        $data = [
            'data' => $item->getSerializer()->serialize($item->getData()),
        ];

        $meta = $item->getMeta();

        if ([] !== $meta) {
            $data['meta'] = $meta;
        }

        return new Envelope($data);
    }

    public function collection(CollectionInterface $collection): EnvelopeInterface
    {
        $data = $collection->getPagination();

        $data['data'] = [];

        foreach ($collection->getData() as $item) {
            $data['data'][] = $collection->getSerializer()->serialize($item);
        }

        $meta = $collection->getMeta();

        if ([] !== $meta) {
            $data['meta'] = $meta;
        }

        return new Envelope($data);
    }
}
