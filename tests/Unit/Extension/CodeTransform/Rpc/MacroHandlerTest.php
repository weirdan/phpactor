<?php

namespace Phpactor\Tests\Unit\Extension\CodeTransform\Rpc;

use PHPUnit\Framework\TestCase;
use Phpactor\CodeTransform\Domain\Macro\Macro;
use Phpactor\CodeTransform\Domain\Macro\MacroDefinitionFactory;
use Phpactor\CodeTransform\Domain\Macro\MacroRegistry;
use Phpactor\CodeTransform\Domain\Macro\MacroRunner;
use Phpactor\CodeTransform\Domain\SourceCode;
use Phpactor\Extension\CodeTransform\Rpc\MacroHandler;
use Phpactor\Extension\Rpc\Response\InputCallbackResponse;
use Phpactor\Extension\Rpc\Response\Input\TextInput;
use Phpactor\Extension\Rpc\Response\ReplaceFileSourceResponse;

class MacroHandlerTest extends TestCase
{
    /**
     * @var MacroHandler
     */
    private $handler;

    public function setUp()
    {
        $macro = new class implements Macro {
            public function name() { return 'test_macro'; }
            public function __invoke(SourceCode $sourceCode, int $offset, string $name): SourceCode { return $sourceCode; }
        };
        $registry = new MacroRegistry([
            $macro
        ]);
        $definitionFactory = new MacroDefinitionFactory();
        $runner = new MacroRunner($registry, $definitionFactory);
        $this->handler = new MacroHandler($definitionFactory, $runner, $macro);
    }

    public function testAsksForMissingArguments()
    {
        $response = $this->handler->handle([
            'path' => '/fo'
        ]);

        $this->assertInstanceOf(InputCallbackResponse::class, $response);
        assert($response instanceof InputCallbackResponse);
        $this->assertCount(3, $response->inputs());
        $this->assertEquals(TextInput::fromNameLabelAndDefault('offset', 'offset', null), $response->inputs()[1]);
    }

    public function testProcessesMacro()
    {
        $response = $this->handler->handle([
            'sourceCode' => SourceCode::fromStringAndPath('<?php', '/path'),
            'path' => 'path',
            'offset' => 1234,
            'name' => 'hello',
        ]);

        $this->assertInstanceOf(ReplaceFileSourceResponse::class, $response);
        assert($response instanceof ReplaceFileSourceResponse);
    }
}
