<?php

namespace spec\Tweedegolf\Nakala\Util;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class AttributeListSpec extends ObjectBehavior
{
    function it_should_be_able_to_set_a_property()
    {
        $this->set('test', 42);
        $this->get('test')->shouldReturn(42);
        $this->count()->shouldReturn(1);
    }

    function it_should_be_able_to_undo_a_set_step()
    {
        $this->set('test', 42);
        $this->undo();
        $this->get('test')->shouldReturn(null);
        $this->count()->shouldReturn(0);
    }

    function its_count_should_be_correct()
    {
        $this->set('test', 42);
        $this->set('another_test', 41);
        $this->set('test', 41);
        $this->count()->shouldReturn(2);
    }

    function its_values_should_return_a_list_of_values()
    {
        $this->set('example', 'example');
        $this->set('test', 42);
        $this->values()->shouldReturn([
            'example' => 'example',
            'test' => 42,
        ]);
    }

    function it_should_be_possible_to_iterate_over_the_values()
    {
        $this->set('example', 'example');
        $iterator = $this->getIterator();
        $iterator->shouldBeAnInstanceOf('Iterator');
    }

    function it_should_be_possible_to_remove_a_key()
    {
        $this->set('test', 42);
        $this->remove('test');
        $this->count()->shouldReturn(0);
        $this->get('test')->shouldReturn(null);
    }

    function it_should_be_possible_to_undo_a_remove_step()
    {
        $this->set('test', 42);
        $this->remove('test');
        $this->undo();
        $this->count()->shouldReturn(1);
        $this->get('test')->shouldReturn(42);
    }

    function it_should_have_the_correct_array_after_undoing_multiple_steps()
    {
        $this->set('test', 42);
        $this->set('example', 'example');
        $this->remove('test');
        $this->set('testing', 'example');
        $this->remove('testing');
        $this->undo();
        $this->undo();
        $this()->shouldReturn([
            'example' => 'example',
        ]);
    }

    function it_should_work_as_an_array()
    {
        $this['test'] = 'test';
        $this['example'] = 'example';
        unset($this['example']);

        $this['test']->shouldBe('test');
        $this['example']->shouldBe(null);
    }
}
