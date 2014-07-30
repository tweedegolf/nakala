<?php

namespace Tweedegolf\Nakala\Event;

use PhpOffice\PhpWord\Element\Section;
use Symfony\Component\EventDispatcher\Event;

class DocumentConstructionEvent extends Event
{
    private $section;

    public function __construct(Section $section)
    {
        $this->section = $section;
    }

    public function getSection()
    {
        return $this->section;
    }
}
