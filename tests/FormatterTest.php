<?php

namespace Meritum\Serialization\Test;

use Meritum\Serialization\Formatter;
use Meritum\Serialization\EnvelopeInterface;
use Meritum\Serialization\Resource\Item;
use Meritum\Serialization\Resource\Collection;
use Meritum\Serialization\Resource\ResourceInterface;
use Meritum\Serialization\SerializerInterface;
use Meritum\Serialization\Strategy\DataArrayStrategy;
use PHPUnit\Framework\TestCase;

final class FormatterTest extends TestCase
{
    private Formatter $formatter;
    private SerializerInterface $serializer;

    protected function setUp(): void
    {
        $this->formatter = new Formatter(new DataArrayStrategy());

        $this->serializer = new class implements SerializerInterface {
            public function serialize(mixed $data): array { return ['id' => $data->id]; }
        };
    }

    public function test_format_item_returns_envelope(): void
    {
        $item = new Item((object) ['id' => 1], $this->serializer);

        $this->assertInstanceOf(EnvelopeInterface::class, $this->formatter->format($item));
    }

    public function test_format_collection_returns_envelope(): void
    {
        $collection = new Collection([], $this->serializer);

        $this->assertInstanceOf(EnvelopeInterface::class, $this->formatter->format($collection));
    }

    public function test_format_delegates_item_to_strategy(): void
    {
        $item = new Item((object) ['id' => 42], $this->serializer);

        $result = $this->formatter->format($item)->toArray();

        $this->assertSame(['id' => 42], $result['data']);
    }

    public function test_format_delegates_collection_to_strategy(): void
    {
        $items = [(object) ['id' => 1], (object) ['id' => 2]];
        $collection = new Collection($items, $this->serializer);

        $result = $this->formatter->format($collection)->toArray();

        $this->assertSame([['id' => 1], ['id' => 2]], $result['data']);
    }

    public function test_format_throws_for_unknown_resource_type(): void
    {
        $unknown = new class implements ResourceInterface {
            public function getSerializer(): SerializerInterface
            {
                return new class implements SerializerInterface {
                    public function serialize(mixed $data): array { return []; }
                };
            }

            public function getMeta(): array { return []; }
        };

        $this->expectException(\InvalidArgumentException::class);

        $this->formatter->format($unknown);
    }
}
