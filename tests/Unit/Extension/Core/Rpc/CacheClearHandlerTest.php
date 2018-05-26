<?php

namespace Phpactor\Tests\Unit\Extension\Core\Rpc;

use Phpactor\Tests\Unit\Extension\Rpc\Handler\HandlerTestCase;
use Phpactor\Extension\Rpc\DefaultParameterHandler;
use Phpactor\Extension\Core\Rpc\CacheClearHandler;
use Phpactor\Extension\Core\Application\CacheClear;
use Prophecy\Prophecy\ObjectProphecy;

class CacheClearHandlerTest extends HandlerTestCase
{
    /**
     * @var CacheClear|ObjectProphecy
     */
    private $clearCache;

    public function setUp()
    {
        $this->clearCache = $this->prophesize(CacheClear::class);
    }

    public function createHandler(): DefaultParameterHandler
    {
        return new CacheClearHandler($this->clearCache->reveal());
    }

    public function testClearCache()
    {
        $this->clearCache->clearCache()->shouldBeCalled();
        $this->clearCache->cachePath()->willReturn('/path/to');
        $this->handle('cache_clear', []);
    }
}
