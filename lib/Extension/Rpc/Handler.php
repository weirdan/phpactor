<?php

namespace Phpactor\Extension\Rpc;

interface Handler
{
    public function name(): string;

    public function handle(array $arguments);
}
