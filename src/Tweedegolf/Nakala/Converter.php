<?php

namespace Tweedegolf\Nakala;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Tweedegolf\Nakala\Event\NakalaEvents;
use Tweedegolf\Nakala\Handler\HandlerInterface;
use Tweedegolf\Nakala\Reader\ReaderInterface;

class Converter implements ConverterInterface
{
    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    public function __construct(EventDispatcherInterface $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    public function convert(ReaderInterface $reader)
    {
        while ($current = $reader->next()) {
            $this->dispatcher->dispatch(NakalaEvents::ELEMENT, $current);
        }
    }

    public function addHandler(HandlerInterface $handler)
    {
        $this->dispatcher->addListener(NakalaEvents::ELEMENT, [$handler, 'onElement']);
    }
}
