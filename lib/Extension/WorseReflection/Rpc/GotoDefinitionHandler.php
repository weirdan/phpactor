<?php

namespace Phpactor\Extension\WorseReflection\Rpc;

use Phpactor\Extension\Rpc\DefaultParameterHandler;
use Phpactor\Extension\Rpc\Response\OpenFileResponse;
use Phpactor\Extension\WorseReflection\GotoDefinition\GotoDefinition;
use Phpactor\WorseReflection\Reflector;
use Phpactor\WorseReflection\Core\SourceCode;
use Phpactor\WorseReflection\Core\Offset;

class GotoDefinitionHandler implements DefaultParameterHandler
{
    const NAME = 'goto_definition';
    const PARAM_OFFSET = 'offset';
    const PARAM_SOURCE = 'source';
    const PARAM_PATH = 'path';

    /**
     * @var Reflector
     */
    private $reflector;

    /**
     * @var GotoDefinition
     */
    private $gotoDefinition;

    public function __construct(
        Reflector $reflector
    ) {
        $this->reflector = $reflector;
        $this->gotoDefinition = new GotoDefinition($reflector);
    }

    public function name(): string
    {
        return self::NAME;
    }

    public function defaultParameters(): array
    {
        return [
            self::PARAM_OFFSET => null,
            self::PARAM_SOURCE => null,
            self::PARAM_PATH => null,
        ];
    }

    public function handle(array $arguments)
    {
        $result = $this->reflector->reflectOffset(
            SourceCode::fromPathAndString(
                $arguments[self::PARAM_PATH],
                $arguments[self::PARAM_SOURCE]
            ),
            Offset::fromInt($arguments[self::PARAM_OFFSET])
        );

        $result = $this->gotoDefinition->gotoDefinition($result->symbolContext());

        return OpenFileResponse::fromPathAndOffset($result->path(), $result->offset());
    }
}
