<?php

namespace Phpactor\Extension\Core\Rpc;

use Phpactor\Extension\Rpc\DefaultParameterHandler;
use Phpactor\Extension\Rpc\Response\InformationResponse;

class ConfigHandler implements DefaultParameterHandler
{
    const CONFIG = 'config';

    /**
     * @var array
     */
    private $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function name(): string
    {
        return self::CONFIG;
    }

    public function defaultParameters(): array
    {
        return [];
    }

    public function handle(array $arguments)
    {
        return InformationResponse::fromString(json_encode($this->config, JSON_PRETTY_PRINT));
    }
}
