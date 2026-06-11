<?php

namespace Meritum\Serialization\Test\Resource;

use Meritum\Serialization\Resource\Item;
use Meritum\Serialization\SerializerInterface;
use PHPUnit\Framework\TestCase;

final class ItemTest extends TestCase
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
        $model = (object) ['id' => 1];
        $item = new Item($model, $this->serializer);

        $this->assertSame($model, $item->getData());
    }

    public function test_get_serializer_returns_serializer(): void
    {
        $item = new Item((object) [], $this->serializer);

        $this->assertSame($this->serializer, $item->getSerializer());
    }

    public function test_has_meta_returns_false_when_empty(): void
    {
        $item = new Item((object) [], $this->serializer);

        $this->assertFalse($item->hasMeta());
    }

    public function test_has_meta_returns_true_after_add_meta(): void
    {
        $item = new Item((object) [], $this->serializer);
        $item->addMeta('foo', 'bar');

        $this->assertTrue($item->hasMeta());
    }

    public function test_get_meta_returns_empty_array_initially(): void
    {
        $item = new Item((object) [], $this->serializer);

        $this->assertSame([], $item->getMeta());
    }

    public function test_get_meta_returns_accumulated_values(): void
    {
        $item = new Item((object) [], $this->serializer);
        $item->addMeta('foo', 'bar')->addMeta('baz', 42);

        $this->assertSame(['foo' => 'bar', 'baz' => 42], $item->getMeta());
    }

    public function test_add_meta_overwrites_existing_key(): void
    {
        $item = new Item((object) [], $this->serializer);
        $item->addMeta('foo', 'bar')->addMeta('foo', 'baz');

        $this->assertSame(['foo' => 'baz'], $item->getMeta());
    }

    public function test_add_meta_is_fluent(): void
    {
        $item = new Item((object) [], $this->serializer);

        $this->assertSame($item, $item->addMeta('foo', 'bar'));
    }
}
