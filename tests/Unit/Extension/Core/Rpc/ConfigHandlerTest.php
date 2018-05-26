<?php

namespace Phpactor\Tests\Unit\Extension\Core\Rpc;

use Phpactor\Tests\Unit\Extension\Rpc\Handler\HandlerTestCase;
use Phpactor\Extension\Rpc\DefaultParameterHandler;
use Phpactor\Extension\Core\Rpc\ConfigHandler;
use Phpactor\Extension\Rpc\Response\InformationResponse;

class ConfigHandlerTest extends HandlerTestCase
{
    public function createHandler(): DefaultParameterHandler
    {
        return new ConfigHandler([
            'key1' => 'value1',
        ]);
    }

    public function testStatus()
    {
        $response = $this->handle('config', []);
        $this->assertInstanceOf(InformationResponse::class, $response);
    }
}
