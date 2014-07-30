<?php

namespace Tweedegolf\Nakala\Handler;

use Tweedegolf\Nakala\Event\ElementEvent;

interface HandlerInterface
{
    /**
     * @return string
     */
    public function getName();

    /**
     * @param ElementEvent $event
     * @return void
     */
    public function onElement(ElementEvent $event);
} 
