<?php

namespace Meritum\Serialization\Test;

use Meritum\Serialization\Envelope;
use PHPUnit\Framework\TestCase;

final class EnvelopeTest extends TestCase
{
    public function test_to_array_returns_data(): void
    {
        $envelope = new Envelope(['foo' => 'bar', 'baz' => 42]);

        $this->assertSame(['foo' => 'bar', 'baz' => 42], $envelope->toArray());
    }

    public function test_json_serialize_returns_to_array(): void
    {
        $envelope = new Envelope(['foo' => 'bar']);

        $this->assertSame($envelope->toArray(), $envelope->jsonSerialize());
    }
}
