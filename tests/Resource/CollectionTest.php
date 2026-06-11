<?php

namespace Meritum\Serialization\Test\Resource;

use Meritum\Serialization\Resource\Collection;
use Meritum\Serialization\SerializerInterface;
use Meritum\Serialization\Pagination\CursorInterface;
use Meritum\Serialization\Pagination\PaginatorInterface;
use PHPUnit\Framework\TestCase;

final class CollectionTest extends TestCase
{
    private SerializerInterface $serializer;

    protected function setUp(): void
    {
        $this->serializer = new class implements SerializerInterface {
            public function serialize(mixed $data): array { return []; }
        };
    }

    public function test_get_data_returns_data(): void
    {
        $items = [(object) ['id' => 1]];
        $collection = new Collection($items, $this->serializer);

        $this->assertSame($items, $collection->getData());
    }

    public function test_get_serializer_returns_serializer(): void
    {
        $collection = new Collection([], $this->serializer);

        $this->assertSame($this->serializer, $collection->getSerializer());
    }

    public function test_get_pagination_returns_empty_array_with_no_paginator(): void
    {
        $collection = new Collection([], $this->serializer);

        $this->assertSame([], $collection->getPagination());
    }

    public function test_set_cursor_populates_pagination(): void
    {
        $cursor = new class implements CursorInterface {
            public function getPrevious(): ?string { return 'abc'; }
            public function getNext(): ?string { return 'def'; }
            public function getPerPage(): int { return 25; }
        };

        $collection = new Collection([], $this->serializer);
        $collection->setCursor($cursor);

        $pagination = $collection->getPagination();

        $this->assertSame(25, $pagination['limit']);
        $this->assertSame('abc', $pagination['previous']);
        $this->assertSame('def', $pagination['next']);
    }

    public function test_set_cursor_allows_null_previous_and_next(): void
    {
        $cursor = new class implements CursorInterface {
            public function getPrevious(): ?string { return null; }
            public function getNext(): ?string { return null; }
            public function getPerPage(): int { return 25; }
        };

        $collection = new Collection([], $this->serializer);
        $collection->setCursor($cursor);

        $pagination = $collection->getPagination();

        $this->assertNull($pagination['previous']);
        $this->assertNull($pagination['next']);
    }

    public function test_set_cursor_is_fluent(): void
    {
        $cursor = new class implements CursorInterface {
            public function getPrevious(): ?string { return null; }
            public function getNext(): ?string { return null; }
            public function getPerPage(): int { return 25; }
        };

        $collection = new Collection([], $this->serializer);

        $this->assertSame($collection, $collection->setCursor($cursor));
    }

    public function test_set_cursor_clears_paginator(): void
    {
        $paginator = new class implements PaginatorInterface {
            public function getCurrentPage(): int { return 1; }
            public function getLastPage(): int { return 5; }
            public function getTotal(): int { return 100; }
            public function getCount(): int { return 20; }
            public function getPerPage(): int { return 20; }
        };

        $cursor = new class implements CursorInterface {
            public function getPrevious(): ?string { return null; }
            public function getNext(): ?string { return 'abc'; }
            public function getPerPage(): int { return 25; }
        };

        $collection = new Collection([], $this->serializer);
        $collection->setPaginator($paginator)->setCursor($cursor);

        $pagination = $collection->getPagination();

        $this->assertArrayNotHasKey('total', $pagination);
        $this->assertArrayHasKey('next', $pagination);
    }

    public function test_set_paginator_populates_pagination(): void
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

        $pagination = $collection->getPagination();

        $this->assertSame(2, $pagination['current']);
        $this->assertSame(5, $pagination['last']);
        $this->assertSame(100, $pagination['total']);
        $this->assertSame(20, $pagination['count']);
        $this->assertSame(20, $pagination['limit']);
    }

    public function test_set_paginator_is_fluent(): void
    {
        $paginator = new class implements PaginatorInterface {
            public function getCurrentPage(): int { return 1; }
            public function getLastPage(): int { return 1; }
            public function getTotal(): int { return 0; }
            public function getCount(): int { return 0; }
            public function getPerPage(): int { return 20; }
        };

        $collection = new Collection([], $this->serializer);

        $this->assertSame($collection, $collection->setPaginator($paginator));
    }

    public function test_set_paginator_clears_cursor(): void
    {
        $cursor = new class implements CursorInterface {
            public function getPrevious(): ?string { return null; }
            public function getNext(): ?string { return 'abc'; }
            public function getPerPage(): int { return 25; }
        };

        $paginator = new class implements PaginatorInterface {
            public function getCurrentPage(): int { return 1; }
            public function getLastPage(): int { return 5; }
            public function getTotal(): int { return 100; }
            public function getCount(): int { return 20; }
            public function getPerPage(): int { return 20; }
        };

        $collection = new Collection([], $this->serializer);
        $collection->setCursor($cursor)->setPaginator($paginator);

        $pagination = $collection->getPagination();

        $this->assertArrayNotHasKey('next', $pagination);
        $this->assertArrayHasKey('total', $pagination);
    }

    public function test_has_meta_returns_false_when_empty(): void
    {
        $collection = new Collection([], $this->serializer);

        $this->assertFalse($collection->hasMeta());
    }

    public function test_has_meta_returns_true_after_add_meta(): void
    {
        $collection = new Collection([], $this->serializer);
        $collection->addMeta('foo', 'bar');

        $this->assertTrue($collection->hasMeta());
    }

    public function test_get_meta_returns_empty_array_initially(): void
    {
        $collection = new Collection([], $this->serializer);

        $this->assertSame([], $collection->getMeta());
    }

    public function test_get_meta_returns_accumulated_values(): void
    {
        $collection = new Collection([], $this->serializer);
        $collection->addMeta('foo', 'bar')->addMeta('baz', 42);

        $this->assertSame(['foo' => 'bar', 'baz' => 42], $collection->getMeta());
    }

    public function test_add_meta_overwrites_existing_key(): void
    {
        $collection = new Collection([], $this->serializer);
        $collection->addMeta('foo', 'bar')->addMeta('foo', 'baz');

        $this->assertSame(['foo' => 'baz'], $collection->getMeta());
    }

    public function test_add_meta_is_fluent(): void
    {
        $collection = new Collection([], $this->serializer);

        $this->assertSame($collection, $collection->addMeta('foo', 'bar'));
    }
}
