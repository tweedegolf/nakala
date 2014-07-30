<?php

namespace Tweedegolf\Nakala\Event;

use PhpOffice\PhpWord\Element\AbstractElement;
use PhpOffice\PhpWord\Element\Section;

class DocumentEvent extends DocumentConstructionEvent
{
    /**
     * @var AbstractElement
     */
    private $element;

    /**
     * @var ElementEvent
     */
    private $event;

    public function __construct(Section $section, AbstractElement $element, ElementEvent $event)
    {
        parent::__construct($section);
        $this->element = $element;
        $this->event = $event;
    }

    /**
     * @return AbstractElement
     */
    public function getElement()
    {
        return $this->element;
    }

    /**
     * @return ElementEvent
     */
    public function getEvent()
    {
        return $this->event;
    }
}
