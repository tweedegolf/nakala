<?php

namespace Tweedegolf\Nakala\Handler;

interface OutputHandlerInterface extends HandlerInterface
{
    /**
     * @return string
     */
    public function getOutput();
}
