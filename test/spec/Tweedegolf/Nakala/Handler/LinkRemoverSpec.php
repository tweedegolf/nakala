<?php

namespace spec\Tweedegolf\Nakala\Handler;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Tweedegolf\Nakala\Event\ElementEvent;
use Tweedegolf\Nakala\Event\ElementEvents;

class LinkRemoverSpec extends ObjectBehavior
{
    function it_should_not_touch_an_element_event_that_is_not_a_link(ElementEvent $event)
    {
        $event->getElement()->willReturn(ElementEvents::IMAGE);
        $this->onElement($event);
        $event->setElement(Argument::any())->shouldNotHaveBeenCalled();
    }

    function it_should_change_a_link_to_text(ElementEvent $event)
    {
        $event->getElement()->willReturn(ElementEvents::LINK);
        $event->setElement(ElementEvents::TEXT)->shouldBeCalled();
        $this->onElement($event);
    }

    function it_should_return_its_name()
    {
        $this->getName()->shouldReturn('link_remover');
    }
}
