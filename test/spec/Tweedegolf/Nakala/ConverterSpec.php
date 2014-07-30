<?php

namespace spec\Tweedegolf\Nakala;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Tweedegolf\Nakala\Event\NakalaEvents;
use Tweedegolf\Nakala\Handler\HandlerInterface;

class ConverterSpec extends ObjectBehavior
{
    function let(EventDispatcherInterface $dispatcher)
    {
        $this->beConstructedWith($dispatcher);
    }

    function it_should_add_handlers_to_the_dispatcher(EventDispatcherInterface $dispatcher, HandlerInterface $handler)
    {
        $dispatcher->addListener(NakalaEvents::ELEMENT, [$handler, 'onElement'])->shouldBeCalled();
        $this->addHandler($handler);
    }
}
