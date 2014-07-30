<?php

namespace spec\Tweedegolf\Nakala\Event;

use PhpOffice\PhpWord\Element\AbstractElement;
use PhpOffice\PhpWord\Element\Section;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Tweedegolf\Nakala\Event\ElementEvent;

class DocumentEventSpec extends ObjectBehavior
{
    function it_should_be_constructed_with_the_correct_parameters(
        Section $section,
        AbstractElement $element,
        ElementEvent $event
    ) {
        $this->beConstructedWith($section, $element, $event);
        $this->getElement()->shouldReturn($element);
        $this->getSection()->shouldReturn($section);
        $this->getEvent()->shouldReturn($event);
    }
}
