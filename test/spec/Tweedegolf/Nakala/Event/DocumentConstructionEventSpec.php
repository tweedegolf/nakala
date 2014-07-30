<?php

namespace spec\Tweedegolf\Nakala\Event;

use PhpOffice\PhpWord\Element\Section;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class DocumentConstructionEventSpec extends ObjectBehavior
{
    function it_should_be_constructed_with_a_section(Section $section)
    {
        $this->beConstructedWith($section);
        $this->getSection()->shouldReturn($section);
    }
}
