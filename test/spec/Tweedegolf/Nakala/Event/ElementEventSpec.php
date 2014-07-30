<?php

namespace spec\Tweedegolf\Nakala\Event;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ElementEventSpec extends ObjectBehavior
{
    private $element = 'example';

    private $content = 'test content';

    private $attributes = [
        'attribute' => 'value',
        'test' => 'example',
    ];

    function let()
    {
        $this->beConstructedWith($this->element, $this->content, $this->attributes);
    }

    function it_should_have_the_element_name_with_which_it_was_constructed()
    {
        $this->getElement()->shouldReturn($this->element);
    }

    function it_should_have_the_content_with_which_it_was_constructed()
    {
        $this->getContent()->shouldReturn($this->content);
    }

    function it_should_have_the_attributes_with_which_it_was_constructed()
    {
        $this->getAttributes()->shouldReturn($this->attributes);
    }

    function it_should_be_able_to_retrieve_individual_attributes()
    {
        $this->getAttribute('attribute')->shouldReturn('value');
    }

    function it_should_return_a_default_if_an_attribute_does_not_exist()
    {
        $this->getAttribute('nonexistant', 'test')->shouldReturn('test');
    }

    function it_should_be_able_to_add_attributes()
    {
        $this['extra'] = 'extra';
        $this['extra']->shouldReturn('extra');
        $this->getAttribute('extra')->shouldReturn('extra');
    }

    function it_should_be_possible_to_remove_attributes()
    {
        unset($this['attribute']);
        $this->getAttribute('attribute')->shouldReturn(null);
    }

    function its_content_should_be_modifiable()
    {
        $this->setContent('testing');
        $this->getContent()->shouldReturn('testing');
    }

    function its_element_should_be_modifiable()
    {
        $this->setElement('element_test');
        $this->getElement()->shouldReturn('element_test');
    }
}
