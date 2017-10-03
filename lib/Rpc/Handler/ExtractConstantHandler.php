<?php

namespace Phpactor\Rpc\Handler;

use Phpactor\Rpc\Handler;
use Phpactor\Rpc\Editor\EchoAction;
use Phpactor\CodeTransform\Domain\Refactor\ExtractConstant;
use Phpactor\Rpc\Editor\InputCallbackAction;
use Phpactor\Rpc\ActionRequest;
use Phpactor\Rpc\Editor\Input\TextInput;
use Phpactor\Rpc\Editor\ReplaceFileSourceAction;
use Phpactor\Rpc\Handler\AbstractHandler;

class ExtractConstantHandler extends AbstractHandler
{
    const NAME = 'extract_constant';

    const LABEL_CONSTANT_NAME = 'Constant name: ';

    const PARAM_CONSTANT_NAME = 'constant_name';
    const PARAM_CONSTANT_NAME_SUGGESTION = 'constant_name_suggestion';
    const PARAM_OFFSET = 'offset';
    const PARAM_PATH = 'path';
    const PARAM_SOURCE = 'source';

    /**
     * @var ExtractConstant
     */
    private $extractConstant;

    public function __construct(ExtractConstant $extractConstant)
    {
        $this->extractConstant = $extractConstant;
    }

    public function name(): string
    {
        return self::NAME;
    }

    public function defaultParameters(): array
    {
        return [
            self::PARAM_PATH => null,
            self::PARAM_SOURCE => null,
            self::PARAM_OFFSET => null,
            self::PARAM_CONSTANT_NAME => null,
            self::PARAM_CONSTANT_NAME_SUGGESTION => null,
        ];
    }

    public function handle(array $arguments)
    {
        $this->requireArgument('constant_name', TextInput::fromNameLabelAndDefault(
            self::PARAM_CONSTANT_NAME,
            self::LABEL_CONSTANT_NAME,
            $arguments[self::PARAM_CONSTANT_NAME_SUGGESTION] ?: ''
        ));

        if ($this->hasMissingArguments($arguments)) {
            return $this->createInputCallback($arguments);
        }

        $sourceCode = $this->extractConstant->extractConstant(
            $arguments[self::PARAM_SOURCE],
            $arguments[self::PARAM_OFFSET],
            $arguments[self::PARAM_CONSTANT_NAME]
        );

        return ReplaceFileSourceAction::fromPathAndSource(self::PARAM_PATH, (string) $sourceCode);
    }
}
