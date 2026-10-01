<?php

namespace Meritum\Serialization;

use Georgeff\Kernel\KernelInterface;
use Psr\Container\ContainerInterface;
use Georgeff\Kernel\Contract\ModuleInterface;
use Meritum\Serialization\Strategy\StrategyInterface;
use Meritum\Serialization\Strategy\DataArrayStrategy;

final class SerializationModule implements ModuleInterface
{
    public function register(KernelInterface $kernel): void
    {
        $kernel->defineFallback(StrategyInterface::class, fn() => new DataArrayStrategy());

        $kernel->define(
            FormatterInterface::class,
            fn(ContainerInterface $c) => new Formatter($c->get(StrategyInterface::class))
        );
    }
}
