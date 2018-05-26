<?php

namespace Phpactor\Extension\Rpc;

interface DefaultParameterHandler extends Handler
{
    public function defaultParameters(): array;
}
