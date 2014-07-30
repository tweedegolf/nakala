<?php

namespace spec\Tweedegolf\Nakala;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Tweedegolf\Nakala\Event\ElementEvent;
use Tweedegolf\Nakala\Event\NakalaEvents;
use Tweedegolf\Nakala\Handler\HandlerInterface;
use Tweedegolf\Nakala\Reader\ReaderInterface;

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

    function it_should_handle_reader_events_when_converting(
        EventDispatcherInterface $dispatcher,
        ReaderInterface $reader,
        ElementEvent $first,
        ElementEvent $second
    ) {
        $reader->next()->will(function ($args) use ($first, $second) {
            $this->next()->will(function ($args) use ($second) {
                $this->next()->will(function ($args) {
                    return false;
                });
                return $second;
            });
            return $first;
        });
        $this->convert($reader);
        $reader->next()->shouldHaveBeenCalledTimes(3);
        $dispatcher->dispatch(NakalaEvents::ELEMENT, $first)->shouldHaveBeenCalled();
        $dispatcher->dispatch(NakalaEvents::ELEMENT, $second)->shouldHaveBeenCalled();
    }
}
