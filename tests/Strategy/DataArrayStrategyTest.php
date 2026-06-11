<?php

namespace Meritum\Serialization\Test\Strategy;

use Meritum\Serialization\Resource\Item;
use Meritum\Serialization\Resource\Collection;
use Meritum\Serialization\SerializerInterface;
use Meritum\Serialization\Strategy\DataArrayStrategy;
use Meritum\Serialization\Pagination\CursorInterface;
use Meritum\Serialization\Pagination\PaginatorInterface;
use PHPUnit\Framework\TestCase;

final class DataArrayStrategyTest extends TestCase
{
    private DataArrayStrategy $strategy;
    private SerializerInterface $serializer;

    protected function setUp(): void
    {
        $this->strategy = new DataArrayStrategy();

        $this->serializer = new class implements SerializerInterface {
            public function serialize(mixed $data): array
            {
                return ['id' => $data->id, 'name' => $data->name];
            }
        };
    }

    public function test_item_wraps_serialized_data_under_data_key(): void
    {
        $item = new Item((object) ['id' => 1, 'name' => 'Mike'], $this->serializer);

        $result = $this->strategy->item($item)->toArray();

        $this->assertSame(['id' => 1, 'name' => 'Mike'], $result['data']);
    }

    public function test_item_omits_meta_when_empty(): void
    {
        $item = new Item((object) ['id' => 1, 'name' => 'Mike'], $this->serializer);

        $this->assertArrayNotHasKey('meta', $this->strategy->item($item)->toArray());
    }

    public function test_item_includes_meta_when_present(): void
    {
        $item = new Item((object) ['id' => 1, 'name' => 'Mike'], $this->serializer);
        $item->addMeta('version', 2);

        $result = $this->strategy->item($item)->toArray();

        $this->assertSame(['version' => 2], $result['meta']);
    }

    public function test_collection_serializes_items_under_data_key(): void
    {
        $items = [(object) ['id' => 1, 'name' => 'Mike'], (object) ['id' => 2, 'name' => 'Steve']];
        $collection = new Collection($items, $this->serializer);

        $result = $this->strategy->collection($collection)->toArray();

        $this->assertSame(
            [['id' => 1, 'name' => 'Mike'], ['id' => 2, 'name' => 'Steve']],
            $result['data']
        );
    }

    public function test_collection_data_key_present_for_empty_collection(): void
    {
        $collection = new Collection([], $this->serializer);

        $result = $this->strategy->collection($collection)->toArray();

        $this->assertArrayHasKey('data', $result);
        $this->assertSame([], $result['data']);
    }

    public function test_collection_omits_meta_when_empty(): void
    {
        $collection = new Collection([], $this->serializer);

        $this->assertArrayNotHasKey('meta', $this->strategy->collection($collection)->toArray());
    }

    public function test_collection_includes_meta_when_present(): void
    {
        $collection = new Collection([], $this->serializer);
        $collection->addMeta('version', 1);

        $result = $this->strategy->collection($collection)->toArray();

        $this->assertSame(['version' => 1], $result['meta']);
    }

    public function test_collection_includes_cursor_pagination(): void
    {
        $cursor = new class implements CursorInterface {
            public function getPrevious(): ?string { return 'abc'; }
            public function getNext(): ?string { return 'def'; }
            public function getPerPage(): int { return 25; }
        };

        $collection = new Collection([], $this->serializer);
        $collection->setCursor($cursor);

        $result = $this->strategy->collection($collection)->toArray();

        $this->assertSame(25, $result['limit']);
        $this->assertSame('abc', $result['previous']);
        $this->assertSame('def', $result['next']);
    }

    public function test_collection_includes_paginator_pagination(): void
    {
        $paginator = new class implements PaginatorInterface {
            public function getCurrentPage(): int { return 2; }
            public function getLastPage(): int { return 5; }
            public function getTotal(): int { return 100; }
            public function getCount(): int { return 20; }
            public function getPerPage(): int { return 20; }
        };

        $collection = new Collection([], $this->serializer);
        $collection->setPaginator($paginator);

        $result = $this->strategy->collection($collection)->toArray();

        $this->assertSame(100, $result['total']);
        $this->assertSame(20, $result['count']);
        $this->assertSame(20, $result['limit']);
        $this->assertSame(2, $result['current']);
        $this->assertSame(5, $result['last']);
    }

    public function test_collection_pagination_precedes_data_key(): void
    {
        $paginator = new class implements PaginatorInterface {
            public function getCurrentPage(): int { return 1; }
            public function getLastPage(): int { return 3; }
            public function getTotal(): int { return 50; }
            public function getCount(): int { return 20; }
            public function getPerPage(): int { return 20; }
        };

        $collection = new Collection([], $this->serializer);
        $collection->setPaginator($paginator);

        $keys = array_keys($this->strategy->collection($collection)->toArray());

        $this->assertLessThan(array_search('data', $keys), array_search('total', $keys));
    }

    public function test_collection_accepts_generator(): void
    {
        $generator = (function () {
            yield (object) ['id' => 1, 'name' => 'Mike'];
        })();

        $collection = new Collection($generator, $this->serializer);

        $this->assertCount(1, $this->strategy->collection($collection)->toArray()['data']);
    }
}
