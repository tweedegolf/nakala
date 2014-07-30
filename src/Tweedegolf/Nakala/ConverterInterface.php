<?php

namespace Tweedegolf\Nakala;

use Tweedegolf\Nakala\Handler\HandlerInterface;
use Tweedegolf\Nakala\Reader\ReaderInterface;

interface ConverterInterface
{
    public function convert(ReaderInterface $reader);

    public function addHandler(HandlerInterface $handler);
} 
