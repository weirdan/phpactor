<?php

namespace Phpactor\Extension\CodeTransform\Rpc;

use Phpactor\Extension\Rpc\DefaultParameterHandler;
use Phpactor\Extension\CodeTransform\Application\Transformer;
use Phpactor\CodeTransform\CodeTransform;
use Phpactor\Extension\Rpc\Response\Input\ChoiceInput;
use Phpactor\Extension\Rpc\Response\InputCallbackResponse;
use Phpactor\Extension\Rpc\Request;
use Phpactor\CodeTransform\Domain\SourceCode;
use Phpactor\Extension\Rpc\Response\ReplaceFileSourceResponse;

class TransformHandler implements DefaultParameterHandler
{
    /**
     * @var Transformer
     */
    private $codeTransform;

    public function __construct(CodeTransform $codeTransform)
    {
        $this->codeTransform = $codeTransform;
    }

    public function name(): string
    {
        return 'transform';
    }

    public function defaultParameters(): array
    {
        return [
            'path' => null,
            'transform' => null,
            'source' => null,
        ];
    }

    public function handle(array $arguments)
    {
        if (null === $arguments['transform']) {
            return $this->transformerChoiceAction($arguments['path'], $arguments['source']);
        }

        $code = SourceCode::fromString($arguments['source']);

        $transformedCode = $this->codeTransform->transform($code, [
            $arguments['transform']
        ]);

        return ReplaceFileSourceResponse::fromPathAndSource($arguments['path'], (string) $transformedCode);
    }

    private function transformerChoiceAction(string $path, string $source)
    {
        $transformers= $this->codeTransform->transformers()->names();

        // get destination path
        return InputCallbackResponse::fromCallbackAndInputs(
            Request::fromNameAndParameters(
                $this->name(),
                [
                    'transform' => null,
                    'path' => $path,
                    'source' => $source,
                ]
            ),
            [
                ChoiceInput::fromNameLabelChoicesAndDefault(
                    'transform',
                    'Transform: ',
                    array_combine($transformers, $transformers)
                )
            ]
        );
    }
}
