<?php

namespace Tweedegolf\Nakala\Handler;

use Tweedegolf\Nakala\Event\ElementEvent;
use Tweedegolf\Nakala\Event\ElementEvents;

class LinkRemover implements HandlerInterface
{
    /**
     * @return string
     */
    public function getName()
    {
        return 'link_remover';
    }

    /**
     * @param ElementEvent $event
     * @return void
     */
    public function onElement(ElementEvent $event)
    {
        if ($event->getElement() === ElementEvents::LINK) {
            $event->setElement(ElementEvents::TEXT);
        }
    }
}
