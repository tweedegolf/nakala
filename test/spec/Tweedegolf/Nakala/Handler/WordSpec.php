<?php

namespace spec\Tweedegolf\Nakala\Handler;

use PhpOffice\PhpWord\Element\Section;
use PhpOffice\PhpWord\Element\Title;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Tweedegolf\Nakala\Event\ElementEvent;
use Tweedegolf\Nakala\Event\ElementEvents;

class WordSpec extends ObjectBehavior
{
    function let(EventDispatcherInterface $dispatcher)
    {
        $this->beConstructedWith($dispatcher);
    }

    function its_section_should_be_null_by_default()
    {
        $this->getDocumentSection()->shouldReturn(null);
    }

    function its_section_should_be_modifiable(Section $section)
    {
        $this->setDocumentSection($section);
        $this->getDocumentSection()->shouldReturn($section);
    }

    function it_should_return_its_name()
    {
        $this->getName()->shouldReturn('word');
    }

    function it_should_be_possible_to_set_a_default_style()
    {
        $this->setDefaultStyle('paragraph', 'Test');
        // TODO: test if the style is actually set with a call to onElement
    }

    function it_should_fail_when_no_section_is_provided(ElementEvent $event)
    {
        $this->shouldThrow('RuntimeException')->duringOnElement($event);
    }

    function it_should_handle_a_title_event(Section $section, ElementEvent $event, Title $title)
    {
        $this->setDocumentSection($section);
        $section->addTitle('Title')->willReturn($title)->shouldBeCalled();


        $event->getElement()->willReturn(ElementEvents::TITLE);
        $event->getContent()->willReturn('Title');
        $event->getAttribute('depth')->willReturn(0);
        $this->onElement($event);
    }
}
