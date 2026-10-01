<?php

namespace Meritum\Serialization\Test;

use Georgeff\Kernel\Kernel;
use Georgeff\Kernel\KernelInterface;
use Georgeff\Kernel\Environment\Testing;
use Georgeff\Kernel\Contract\ModuleInterface;
use Meritum\Serialization\Envelope;
use Meritum\Serialization\EnvelopeInterface;
use Meritum\Serialization\FormatterInterface;
use Meritum\Serialization\SerializationModule;
use Meritum\Serialization\SerializerInterface;
use Meritum\Serialization\Resource\Item;
use Meritum\Serialization\Resource\ItemInterface;
use Meritum\Serialization\Resource\CollectionInterface;
use Meritum\Serialization\Strategy\StrategyInterface;
use Meritum\Serialization\Strategy\DataArrayStrategy;
use PHPUnit\Framework\TestCase;

final class SerializationModuleTest extends TestCase
{
    public function test_registers_formatter_in_container(): void
    {
        $kernel = new Kernel(new Testing());
        $kernel->addModule(new SerializationModule());
        $kernel->boot();

        $this->assertInstanceOf(
            FormatterInterface::class,
            $kernel->getContainer()->get(FormatterInterface::class)
        );
    }

    public function test_formatter_uses_data_array_strategy_by_default(): void
    {
        $kernel = new Kernel(new Testing());
        $kernel->addModule(new SerializationModule());
        $kernel->boot();

        $serializer = new class implements SerializerInterface {
            public function serialize(mixed $data): array { return ['id' => $data->id]; }
        };

        $formatter = $kernel->getContainer()->get(FormatterInterface::class);
        $result = $formatter->format(new Item((object) ['id' => 1], $serializer))->toArray();

        $this->assertArrayHasKey('data', $result);
    }

    public function test_strategy_falls_back_to_data_array_strategy(): void
    {
        $kernel = new Kernel(new Testing());
        $kernel->addModule(new SerializationModule());
        $kernel->boot();

        $this->assertInstanceOf(
            DataArrayStrategy::class,
            $kernel->getContainer()->get(StrategyInterface::class)
        );
    }

    public function test_strategy_registered_by_a_later_module_overrides_the_fallback(): void
    {
        $customStrategy = new class implements StrategyInterface {
            public function item(ItemInterface $item): EnvelopeInterface
            {
                return new Envelope([]);
            }

            public function collection(CollectionInterface $collection): EnvelopeInterface
            {
                return new Envelope([]);
            }
        };

        $kernel = new Kernel(new Testing());
        $kernel->addModule(new SerializationModule());
        $kernel->addModule(new class ($customStrategy) implements ModuleInterface {
            public function __construct(private StrategyInterface $strategy) {}

            public function register(KernelInterface $kernel): void
            {
                $kernel->define(StrategyInterface::class, fn () => $this->strategy);
            }
        });
        $kernel->boot();

        $this->assertSame($customStrategy, $kernel->getContainer()->get(StrategyInterface::class));
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

        $kernel = new Kernel(new Testing());
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
