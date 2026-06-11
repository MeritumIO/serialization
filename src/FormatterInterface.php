<?php

namespace Meritum\Serialization;

use Meritum\Serialization\Resource\ResourceInterface;

interface FormatterInterface
{
    public function format(ResourceInterface $resource): EnvelopeInterface;
}
