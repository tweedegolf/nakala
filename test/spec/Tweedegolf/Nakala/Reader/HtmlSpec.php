<?php

namespace spec\Tweedegolf\Nakala\Reader;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Tweedegolf\Nakala\Event\ElementEvents;

class HtmlSpec extends ObjectBehavior
{
    const ELEMENT_EVENT_CLASS = 'Tweedegolf\\Nakala\\Event\\ElementEvent';

    function it_should_load_a_correct_html_fragment()
    {
        $this->setContent(
            '<!DOCTYPE html><html><head><title>Testing title</title></head><body><h1>Testing</h1></body></html>'
        )->shouldReturn($this);
    }

    function it_should_not_load_invalid_html()
    {
        $this->shouldThrow('RuntimeException')->duringSetContent('<html<<=');
    }

    function it_should_return_false_if_there_are_no_more_elements()
    {
        $this->setContent($this->htmlBulk(''));
        $this->next()->shouldReturn(false);
    }

    function it_should_handle_a_paragraph()
    {
        $this->setContent($this->htmlBulk('<p>Some content</p>'));
        $open = $this->next();
        $open->shouldBeAnInstanceOf(self::ELEMENT_EVENT_CLASS);
        $open->getElement()->shouldReturn(ElementEvents::PARAGRAPH_START);

        $content = $this->next();
        $content->shouldBeAnInstanceOf(self::ELEMENT_EVENT_CLASS);
        $content->getElement()->shouldReturn(ElementEvents::TEXT);
        $content->getContent()->shouldReturn('Some content');

        $end = $this->next();
        $end->shouldBeAnInstanceOf(self::ELEMENT_EVENT_CLASS);
        $end->getElement()->shouldReturn(ElementEvents::PARAGRAPH_END);
    }

    function it_should_handle_an_unordered_list()
    {
        $this->setContent($this->htmlBulk('<ul><li>Test</li><li>Test 2</li></ul>'));

        $elem = $this->next();
        $elem->getElement()->shouldReturn(ElementEvents::LIST_START);
        $elem->getAttribute('numbered')->shouldReturn(false);

        $elem = $this->next();
        $elem->getElement()->shouldReturn(ElementEvents::LIST_ITEM_START);
        $elem->getAttribute('numbered')->shouldReturn(false);
        $elem->getAttribute('depth')->shouldReturn(0);

        $elem = $this->next();
        $elem->getElement()->shouldReturn(ElementEvents::TEXT);
        $elem->getContent()->shouldReturn('Test');

        $elem = $this->next();
        $elem->getElement()->shouldReturn(ElementEvents::LIST_ITEM_END);
        $elem->getAttribute('numbered')->shouldReturn(false);
        $elem->getAttribute('depth')->shouldReturn(0);

        $elem = $this->next();
        $elem->getElement()->shouldReturn(ElementEvents::LIST_ITEM_START);
        $elem->getAttribute('numbered')->shouldReturn(false);
        $elem->getAttribute('depth')->shouldReturn(0);

        $elem = $this->next();
        $elem->getElement()->shouldReturn(ElementEvents::TEXT);
        $elem->getContent()->shouldReturn('Test 2');

        $elem = $this->next();
        $elem->getElement()->shouldReturn(ElementEvents::LIST_ITEM_END);
        $elem->getAttribute('numbered')->shouldReturn(false);
        $elem->getAttribute('depth')->shouldReturn(0);

        $elem = $this->next();
        $elem->getElement()->shouldReturn(ElementEvents::LIST_END);
        $elem->getAttribute('numbered')->shouldReturn(false);
    }

    function it_should_handle_an_ordered_list()
    {
        $this->setContent($this->htmlBulk('<ol><li>Test</li><li>Test 2<ol><li>Test 3</li></ol></li></ol>'));

        // main list
        $elem = $this->next();
        $elem->getElement()->shouldReturn(ElementEvents::LIST_START);
        $elem->getAttribute('numbered')->shouldReturn(true);

        // first item
        $elem = $this->next();
        $elem->getElement()->shouldReturn(ElementEvents::LIST_ITEM_START);
        $elem->getAttribute('numbered')->shouldReturn(true);
        $elem->getAttribute('depth')->shouldReturn(0);

        $elem = $this->next();
        $elem->getElement()->shouldReturn(ElementEvents::TEXT);
        $elem->getContent()->shouldReturn('Test');

        $elem = $this->next();
        $elem->getElement()->shouldReturn(ElementEvents::LIST_ITEM_END);
        $elem->getAttribute('numbered')->shouldReturn(true);
        $elem->getAttribute('depth')->shouldReturn(0);

        // second item
        $elem = $this->next();
        $elem->getElement()->shouldReturn(ElementEvents::LIST_ITEM_START);
        $elem->getAttribute('numbered')->shouldReturn(true);
        $elem->getAttribute('depth')->shouldReturn(0);

        $elem = $this->next();
        $elem->getElement()->shouldReturn(ElementEvents::TEXT);
        $elem->getContent()->shouldReturn('Test 2');

        // sublist
        $elem = $this->next();
        $elem->getElement()->shouldReturn(ElementEvents::LIST_START);
        $elem->getAttribute('numbered')->shouldReturn(true);

        // sublist first item
        $elem = $this->next();
        $elem->getElement()->shouldReturn(ElementEvents::LIST_ITEM_START);
        $elem->getAttribute('numbered')->shouldReturn(true);
        $elem->getAttribute('depth')->shouldReturn(1);

        $elem = $this->next();
        $elem->getElement()->shouldReturn(ElementEvents::TEXT);
        $elem->getContent()->shouldReturn('Test 3');

        $elem = $this->next();
        $elem->getElement()->shouldReturn(ElementEvents::LIST_ITEM_END);
        $elem->getAttribute('numbered')->shouldReturn(true);
        $elem->getAttribute('depth')->shouldReturn(1);

        $elem = $this->next();
        $elem->getElement()->shouldReturn(ElementEvents::LIST_END);
        $elem->getAttribute('numbered')->shouldReturn(true);


        // main list remainder
        $elem = $this->next();
        $elem->getElement()->shouldReturn(ElementEvents::LIST_ITEM_END);
        $elem->getAttribute('numbered')->shouldReturn(true);
        $elem->getAttribute('depth')->shouldReturn(0);

        $elem = $this->next();
        $elem->getElement()->shouldReturn(ElementEvents::LIST_END);
        $elem->getAttribute('numbered')->shouldReturn(true);
    }

    function it_should_correctly_handle_a_table()
    {
        $this->setContent($this->htmlBulk(
            '<table><thead><tr><th>Header 1</th><th>Header 2</th></tr></thead>' .
            '<tbody><tr><td>Cell 1:1</td><td>Cell 1:2</td></tr></tbody></table>'
        ));

        $elem = $this->next();
        $elem->getElement()->shouldReturn(ElementEvents::TABLE_START);

        // header row
        $elem = $this->next();
        $elem->getElement()->shouldReturn(ElementEvents::TABLE_ROW_START);

        $elem = $this->next();
        $elem->getElement()->shouldReturn(ElementEvents::TABLE_CELL_START);
        $elem->getAttribute('header')->shouldReturn(true);

        $elem = $this->next();
        $elem->getElement()->shouldReturn(ElementEvents::TEXT);
        $elem->getContent()->shouldReturn('Header 1');

        $elem = $this->next();
        $elem->getElement()->shouldReturn(ElementEvents::TABLE_CELL_END);
        $elem->getAttribute('header')->shouldReturn(true);

        $elem = $this->next();
        $elem->getElement()->shouldReturn(ElementEvents::TABLE_CELL_START);
        $elem->getAttribute('header')->shouldReturn(true);

        $elem = $this->next();
        $elem->getElement()->shouldReturn(ElementEvents::TEXT);
        $elem->getContent()->shouldReturn('Header 2');

        $elem = $this->next();
        $elem->getElement()->shouldReturn(ElementEvents::TABLE_CELL_END);
        $elem->getAttribute('header')->shouldReturn(true);

        $elem = $this->next();
        $elem->getElement()->shouldReturn(ElementEvents::TABLE_ROW_END);

        // content row
        $elem = $this->next();
        $elem->getElement()->shouldReturn(ElementEvents::TABLE_ROW_START);

        $elem = $this->next();
        $elem->getElement()->shouldReturn(ElementEvents::TABLE_CELL_START);
        $elem->getAttribute('header')->shouldReturn(false);

        $elem = $this->next();
        $elem->getElement()->shouldReturn(ElementEvents::TEXT);
        $elem->getContent()->shouldReturn('Cell 1:1');

        $elem = $this->next();
        $elem->getElement()->shouldReturn(ElementEvents::TABLE_CELL_END);
        $elem->getAttribute('header')->shouldReturn(false);

        $elem = $this->next();
        $elem->getElement()->shouldReturn(ElementEvents::TABLE_CELL_START);
        $elem->getAttribute('header')->shouldReturn(false);

        $elem = $this->next();
        $elem->getElement()->shouldReturn(ElementEvents::TEXT);
        $elem->getContent()->shouldReturn('Cell 1:2');

        $elem = $this->next();
        $elem->getElement()->shouldReturn(ElementEvents::TABLE_CELL_END);
        $elem->getAttribute('header')->shouldReturn(false);

        $elem = $this->next();
        $elem->getElement()->shouldReturn(ElementEvents::TABLE_ROW_END);

        $elem = $this->next();
        $elem->getElement()->shouldReturn(ElementEvents::TABLE_END);
    }

    function it_should_correctly_handle_spaces_in_a_paragraph()
    {
        $this->setContent($this->htmlBulk('<p> </p>'));
        $this->next();
        $content = $this->next();
        $content->getElement()->shouldReturn(ElementEvents::TEXT);
        $content->getContent()->shouldReturn('');

        $this->setContent($this->htmlBulk('<p> <strong>test </strong> thing    y</p>'));
        $this->next();
        $content = $this->next();
        $content->getElement()->shouldReturn(ElementEvents::TEXT);
        $content->getContent()->shouldReturn('');

        $bold = $this->next();
        $bold->getElement()->shouldReturn(ElementEvents::TEXT);
        $bold->getContent()->shouldReturn('test ');

        $end = $this->next();
        $end->getElement()->shouldReturn(ElementEvents::TEXT);
        $end->getContent()->shouldReturn(' thing y');
    }

    function it_should_handle_h1_elements()
    {
        $this->setContent($this->htmlBulk('<h1>Title</h1>'));
        $title = $this->next();
        $title->shouldBeAnInstanceOf(self::ELEMENT_EVENT_CLASS);
        $title->getElement()->shouldReturn(ElementEvents::TITLE);
        $title->getContent()->shouldReturn('Title');
        $title->getAttribute('depth')->shouldReturn(0);
    }

    function it_should_handle_h2_elements()
    {
        $this->setContent($this->htmlBulk('<h2>Title</h2>'));
        $title = $this->next();
        $title->shouldBeAnInstanceOf(self::ELEMENT_EVENT_CLASS);
        $title->getElement()->shouldReturn(ElementEvents::TITLE);
        $title->getContent()->shouldReturn('Title');
        $title->getAttribute('depth')->shouldReturn(1);
    }

    function it_should_handle_h3_elements()
    {
        $this->setContent($this->htmlBulk('<h3>Title</h3>'));
        $title = $this->next();
        $title->shouldBeAnInstanceOf(self::ELEMENT_EVENT_CLASS);
        $title->getElement()->shouldReturn(ElementEvents::TITLE);
        $title->getContent()->shouldReturn('Title');
        $title->getAttribute('depth')->shouldReturn(2);
    }

    function it_should_handle_h4_elements()
    {
        $this->setContent($this->htmlBulk('<h4>Title</h4>'));
        $title = $this->next();
        $title->shouldBeAnInstanceOf(self::ELEMENT_EVENT_CLASS);
        $title->getElement()->shouldReturn(ElementEvents::TITLE);
        $title->getContent()->shouldReturn('Title');
        $title->getAttribute('depth')->shouldReturn(3);
    }

    function it_should_handle_h5_elements()
    {
        $this->setContent($this->htmlBulk('<h5>Title</h5>'));
        $title = $this->next();
        $title->shouldBeAnInstanceOf(self::ELEMENT_EVENT_CLASS);
        $title->getElement()->shouldReturn(ElementEvents::TITLE);
        $title->getContent()->shouldReturn('Title');
        $title->getAttribute('depth')->shouldReturn(4);
    }

    function it_should_handle_h6_elements()
    {
        $this->setContent($this->htmlBulk('<h6>Title</h6>'));
        $title = $this->next();
        $title->shouldBeAnInstanceOf(self::ELEMENT_EVENT_CLASS);
        $title->getElement()->shouldReturn(ElementEvents::TITLE);
        $title->getContent()->shouldReturn('Title');
        $title->getAttribute('depth')->shouldReturn(5);
    }

    function it_should_handle_strong_elements()
    {
        $this->setContent($this->htmlBulk('<p><strong>Test</strong></p>'));

        $this->next();
        $content = $this->next();
        $content->getElement()->shouldReturn(ElementEvents::TEXT);
        $content->getAttribute('bold')->shouldReturn(true);
        $content->getContent()->shouldReturn('Test');
    }

    function it_should_handle_b_elements()
    {
        $this->setContent($this->htmlBulk('<p><b>Test</b></p>'));

        $this->next();
        $content = $this->next();
        $content->getElement()->shouldReturn(ElementEvents::TEXT);
        $content->getAttribute('bold')->shouldReturn(true);
        $content->getContent()->shouldReturn('Test');
    }

    function it_should_handle_em_elements()
    {
        $this->setContent($this->htmlBulk('<p><em>Test</em></p>'));

        $this->next();
        $content = $this->next();
        $content->getElement()->shouldReturn(ElementEvents::TEXT);
        $content->getAttribute('italic')->shouldReturn(true);
        $content->getContent()->shouldReturn('Test');
    }

    function it_should_handle_i_elements()
    {
        $this->setContent($this->htmlBulk('<p><i>Test</i></p>'));

        $this->next();
        $content = $this->next();
        $content->getElement()->shouldReturn(ElementEvents::TEXT);
        $content->getAttribute('italic')->shouldReturn(true);
        $content->getContent()->shouldReturn('Test');
    }

    function it_should_handle_u_elements()
    {
        $this->setContent($this->htmlBulk('<p><u>Test</u></p>'));

        $this->next();
        $content = $this->next();
        $content->getElement()->shouldReturn(ElementEvents::TEXT);
        $content->getAttribute('underline')->shouldReturn('single');
        $content->getContent()->shouldReturn('Test');
    }

    function it_should_handle_del_elements()
    {
        $this->setContent($this->htmlBulk('<p><del>Test</del></p>'));

        $this->next();
        $content = $this->next();
        $content->getElement()->shouldReturn(ElementEvents::TEXT);
        $content->getAttribute('strikethrough')->shouldReturn(true);
        $content->getContent()->shouldReturn('Test');
    }

    function it_should_handle_sup_elements()
    {
        $this->setContent($this->htmlBulk('<p><sup>Test</sup></p>'));

        $this->next();
        $content = $this->next();
        $content->getElement()->shouldReturn(ElementEvents::TEXT);
        $content->getAttribute('superScript')->shouldReturn(true);
        $content->getContent()->shouldReturn('Test');
    }

    function it_should_handle_sub_elements()
    {
        $this->setContent($this->htmlBulk('<p><sub>Test</sub></p>'));

        $this->next();
        $content = $this->next();
        $content->getElement()->shouldReturn(ElementEvents::TEXT);
        $content->getAttribute('subScript')->shouldReturn(true);
        $content->getContent()->shouldReturn('Test');
    }

    function it_should_handle_a_elements()
    {
        $this->setContent($this->htmlBulk('<p><a href="test.html">Test</a></p>'));

        $this->next();
        $link = $this->next();
        $link->getElement()->shouldReturn(ElementEvents::LINK);
        $link->getAttribute('link')->shouldReturn('test.html');
        $link->getContent()->shouldReturn('Test');
    }

    function it_should_handle_img_elements()
    {
        $this->setContent($this->htmlBulk('<img src="test.png">'));

        $image = $this->next();
        $image->getElement()->shouldReturn(ElementEvents::IMAGE);
        $image->getContent()->shouldReturn('test.png');
    }

    function it_should_ignore_span_elements()
    {
        $this->setContent($this->htmlBulk('<p><span><a href="test.html">Test</a></span></p>'));

        $this->next();
        $link = $this->next();
        $link->getElement()->shouldReturn(ElementEvents::LINK);
        $link->getAttribute('link')->shouldReturn('test.html');
        $link->getContent()->shouldReturn('Test');
    }

    function it_should_ignore_div_elements()
    {
        $this->setContent($this->htmlBulk('<div><h1>Title</h1></div>'));
        $title = $this->next();
        $title->shouldBeAnInstanceOf(self::ELEMENT_EVENT_CLASS);
        $title->getElement()->shouldReturn(ElementEvents::TITLE);
        $title->getContent()->shouldReturn('Title');
        $title->getAttribute('depth')->shouldReturn(0);
    }

    function it_should_not_handle_other_elements()
    {
        $this->shouldThrow('RuntimeException')->duringSetContent(
            $this->htmlBulk('<dl><dt>Title</dt><dd>Descr</dd></dl>')
        );
    }

    private function htmlBulk($fragment)
    {
        return '<!DOCTYPE html><html><head></head><body>' . $fragment . '</body></html>';
    }
}
