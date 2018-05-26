<?php

namespace Phpactor\Extension\CodeTransform\Rpc\ParameterConverter;

class ParameterConverters
{
    /**
     * @var array
     */
    private $converters;

    public function __construct(array $converters)
    {
        $this->converters = $converters;
    }
}
