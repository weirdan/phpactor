<?php

namespace Phpactor\Tests\Unit\Extension\CoreTransform\Rpc;

use Phpactor\Extension\Rpc\DefaultParameterHandler;
use Phpactor\CodeTransform\Domain\SourceCode;
use Phpactor\Extension\Rpc\Response\ReplaceFileSourceResponse;
use Phpactor\CodeTransform\Domain\Refactor\GenerateAccessor;
use Phpactor\Extension\CodeTransform\Rpc\GenerateAccessorHandler;
use Phpactor\Tests\Unit\Extension\Rpc\Handler\HandlerTestCase;

class GenerateAccessorHandlerTest extends HandlerTestCase
{
    const SOURCE = '<?php echo "foo";';
    const PATH = '/path/to';
    const OFFSET = 1234;
    const CONSTANT_NAME = 'FOOBAR';

    /**
     * @var GenerateAccessor
     */
    private $generateAccessor;

    public function setUp()
    {
        $this->generateAccessor = $this->prophesize(GenerateAccessor::class);
    }

    public function createHandler(): DefaultParameterHandler
    {
        return new GenerateAccessorHandler($this->generateAccessor->reveal());
    }

    public function testGenerateAccessor()
    {
        $this->generateAccessor->generateAccessor(
            self::SOURCE,
            self::OFFSET
        )->willReturn(SourceCode::fromStringAndPath('asd', '/path'));

        $action = $this->handle(GenerateAccessorHandler::NAME, [
            'source' => self::SOURCE,
            'path' => self::PATH,
            'offset' => self::OFFSET
        ]);

        $this->assertInstanceof(ReplaceFileSourceResponse::class, $action);
    }
}
