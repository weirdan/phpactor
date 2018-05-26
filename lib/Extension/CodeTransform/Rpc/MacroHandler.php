<?php

namespace Phpactor\Extension\CodeTransform\Rpc;

use InvalidArgumentException;
use Phpactor\CodeTransform\Domain\Macro\Macro;
use Phpactor\CodeTransform\Domain\Macro\MacroDefinition;
use Phpactor\CodeTransform\Domain\Macro\MacroDefinitionFactory;
use Phpactor\CodeTransform\Domain\Macro\MacroRunner;
use Phpactor\Extension\Rpc\Handler;
use Phpactor\Extension\Rpc\Handler\AbstractHandler;
use Phpactor\Extension\Rpc\Request;
use Phpactor\Extension\Rpc\Response\InputCallbackResponse;
use Phpactor\Extension\Rpc\Response\Input\TextInput;
use Phpactor\Extension\Rpc\Response\ReplaceFileSourceResponse;

class MacroHandler implements Handler
{
    const PARAM_PATH = 'path';

    /**
     * @var Macro
     */
    private $macro;

    /**
     * @var MacroRunner
     */
    private $runner;

    /**
     * @var MacroDefinitionFactory
     */
    private $definitionFactory;

    public function __construct(MacroDefinitionFactory $definitionFactory, MacroRunner $runner, Macro $macro)
    {
        $this->macro = $macro;
        $this->runner = $runner;
        $this->definitionFactory = $definitionFactory;
    }

    public function name(): string
    {
        return $this->macro->name();
    }

    public function handle(array $arguments)
    {
        $path = $this->extractPath($arguments);

        $definition = $this->definitionFactory->definitionFor(get_class($this->macro));
        $inputCallbacks = $this->collectInputCallbacks($definition, $arguments);

        if (count($inputCallbacks)) {
            return InputCallbackResponse::fromCallbackAndInputs(
                Request::fromNameAndParameters(
                    $this->name(),
                    $arguments
                ),
                $inputCallbacks
            );
        }

        $source = $this->runner->run($this->name(), $arguments);

        return ReplaceFileSourceResponse::fromPathAndSource(
            $source->path(),
            $source->__toString()
        );
    }

    private function extractPath(array &$arguments)
    {
        if (!isset($arguments[self::PARAM_PATH])) {
            throw new InvalidArgumentException(sprintf(
                'Missing path argument for macro "%s"',
                $this->name()
            ));
        }
        
        $path = $arguments[self::PARAM_PATH];
        unset($arguments[self::PARAM_PATH]);
        return $path;
    }

    private function collectInputCallbacks(MacroDefinition $definition, array $arguments)
    {
        $inputCallbacks = [];
        
        foreach ($definition->parameterDefinitions() as $parameterDefinition) {
            if (isset($arguments[$parameterDefinition->name()])) {
                continue;
            }
        
            $inputCallbacks[] = TextInput::fromNameLabelAndDefault(
                $parameterDefinition->name(),
                $parameterDefinition->name(),
                $parameterDefinition->default()
            );
        }
        return $inputCallbacks;
    }
}
