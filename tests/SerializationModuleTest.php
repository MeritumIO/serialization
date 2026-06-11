<?php

namespace Meritum\Serialization\Test;

use Georgeff\Kernel\Kernel;
use Georgeff\Kernel\Environment;
use Meritum\Serialization\Envelope;
use Meritum\Serialization\EnvelopeInterface;
use Meritum\Serialization\FormatterInterface;
use Meritum\Serialization\SerializationModule;
use Meritum\Serialization\SerializerInterface;
use Meritum\Serialization\Resource\Item;
use Meritum\Serialization\Resource\ItemInterface;
use Meritum\Serialization\Resource\CollectionInterface;
use Meritum\Serialization\Strategy\StrategyInterface;
use PHPUnit\Framework\TestCase;

final class SerializationModuleTest extends TestCase
{
    public function test_registers_formatter_in_container(): void
    {
        $kernel = new Kernel(Environment::Testing);
        $kernel->addModule(new SerializationModule());
        $kernel->boot();

        $this->assertInstanceOf(
            FormatterInterface::class,
            $kernel->getContainer()->get(FormatterInterface::class)
        );
    }

    public function test_formatter_uses_data_array_strategy_by_default(): void
    {
        $kernel = new Kernel(Environment::Testing);
        $kernel->addModule(new SerializationModule());
        $kernel->boot();

        $serializer = new class implements SerializerInterface {
            public function serialize(mixed $data): array { return ['id' => $data->id]; }
        };

        $formatter = $kernel->getContainer()->get(FormatterInterface::class);
        $result = $formatter->format(new Item((object) ['id' => 1], $serializer))->toArray();

        $this->assertArrayHasKey('data', $result);
    }

    public function test_formatter_uses_registered_strategy(): void
    {
        $customStrategy = new class implements StrategyInterface {
            public bool $called = false;

            public function item(ItemInterface $item): EnvelopeInterface
            {
                $this->called = true;

                return new Envelope([]);
            }

            public function collection(CollectionInterface $collection): EnvelopeInterface
            {
                return new Envelope([]);
            }
        };

        $kernel = new Kernel(Environment::Testing);
        $kernel->define(StrategyInterface::class, fn () => $customStrategy)->share();
        $kernel->addModule(new SerializationModule());
        $kernel->boot();

        $serializer = new class implements SerializerInterface {
            public function serialize(mixed $data): array { return []; }
        };

        $formatter = $kernel->getContainer()->get(FormatterInterface::class);
        $formatter->format(new Item((object) [], $serializer));

        $this->assertTrue($customStrategy->called);
    }
}
