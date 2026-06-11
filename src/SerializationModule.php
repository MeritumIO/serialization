<?php

namespace Meritum\Serialization;

use Georgeff\Kernel\KernelInterface;
use Psr\Container\ContainerInterface;
use Georgeff\Kernel\Module\ModuleInterface;
use Meritum\Serialization\Strategy\StrategyInterface;
use Meritum\Serialization\Strategy\DataArrayStrategy;

final class SerializationModule implements ModuleInterface
{
    public function register(KernelInterface $kernel): void
    {
        $factory = function (ContainerInterface $c) {
            $strategy = $c->has(StrategyInterface::class)
                ? $c->get(StrategyInterface::class)
                : new DataArrayStrategy();

            return new Formatter($strategy);
        };

        $kernel->define(FormatterInterface::class, $factory);
    }
}
