<?php

namespace Tweedegolf\Nakala\Handler;

use PhpOffice\PhpWord\Element\AbstractContainer;
use PhpOffice\PhpWord\Element\Row;
use PhpOffice\PhpWord\Element\Section;
use PhpOffice\PhpWord\Element\Table;
use PhpOffice\PhpWord\Element\ListItemRun;
use PhpOffice\PhpWord\Style\ListItem;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Tweedegolf\Nakala\ElementAttributes;
use Tweedegolf\Nakala\Event\DocumentConstructionEvent;
use Tweedegolf\Nakala\Event\DocumentEvent;
use Tweedegolf\Nakala\Event\DocumentEvents;
use Tweedegolf\Nakala\Event\ElementEvent;
use Tweedegolf\Nakala\Event\ElementEvents;
use Tweedegolf\Nakala\Util\AttributeList;

class Word implements OutputHandlerInterface
{
    /**
     * @var Section
     */
    private $section;

    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * @var AttributeList
     */
    private $context;

    private $defaultStyles = [
        'paragraph' => 'Normal',
        'list' => 'Normal',
        'table' => 'Normal',
        'image' => 'Normal',
    ];

    public function __construct(EventDispatcherInterface $dispatcher)
    {
        $this->dispatcher = $dispatcher;
        $this->context = new AttributeList();
    }

    public function setDocumentSection(Section $section)
    {
        $this->section = $section;
    }

    public function getDocumentSection()
    {
        return $this->section;
    }

    public function getName()
    {
        return 'word';
    }

    public function setDefaultStyle($element, $styleName)
    {
        $this->defaultStyles[$element] = $styleName;
    }

    public function onElement(ElementEvent $event)
    {
        if (null === $this->section) {
            throw new \RuntimeException("No document section provided");
        }

        /** @var AbstractContainer $current */
        $current = $this->context->get('current', $this->section);
        if ($current === $this->section) {
            $this->dispatcher->dispatch(
                DocumentEvents::BEFORE_ROOT_ELEMENT,
                new DocumentConstructionEvent($this->section)
            );
        }
        switch ($event->getElement()) {
            case ElementEvents::TITLE:
                /** @var Section $current */
                $title = $current->addTitle($event->getContent());
                $this->dispatcher->dispatch(
                    DocumentEvents::TITLE_CREATED,
                    new DocumentEvent($this->section, $title, $event)
                );
                break;
            case ElementEvents::PARAGRAPH_START:
                $this->context->setMany([
                    'current' => $current->addTextRun(),
                    'event' => $event,
                ]);
                break;
            case ElementEvents::TABLE_START:
                $this->context->setMany([
                    'current' => $current->addTable(['width' => 5000, 'unit' => 'pct']),
                    'event' => $event,
                ]);
                break;
            case ElementEvents::TABLE_ROW_START:
                /** @var Table $current */
                $this->context->setMany([
                    'current' => $current->addRow(),
                    'event' => $event,
                ]);
                break;
            case ElementEvents::TABLE_CELL_START:
                /** @var Row $current */
                $this->context->setMany([
                    'current' => $current->addCell(5000),
                    'event' => $event,
                ]);
                break;
            case ElementEvents::LIST_END:
                // close the last item in the list
                if ($current instanceof ListItemRun) {
                    $this->dispatcher->dispatch(
                        DocumentEvents::LIST_ITEM_CREATED,
                        new DocumentEvent($this->section, $current, $this->context['event'])
                    );
                    $this->context->undo();
                }
                break;
            case ElementEvents::LIST_ITEM_END:
            case ElementEvents::LIST_START:
                break;
            case ElementEvents::LIST_ITEM_START:
                // close the previous list item
                if ($current instanceof ListItemRun) {
                    $this->dispatcher->dispatch(
                        DocumentEvents::LIST_ITEM_CREATED,
                        new DocumentEvent($this->section, $current, $this->context['event'])
                    );
                    $this->context->undo();
                    $current = $this->context->get('current', $this->section);
                }
                $is_numbered = $event->getAttribute(ElementAttributes::NUMBERED, false);
                $this->context->setMany([
                    'current' => $current->addListItemRun($event->getAttribute(ElementAttributes::DEPTH), [
                        'listType' => $is_numbered ? ListItem::TYPE_NUMBER : ListItem::TYPE_BULLET_FILLED,
                    ]),
                    'event' => $event,
                ]);
                break;
            case ElementEvents::TEXT:
                $current->addText($event->getContent(), $event->getAttributes());
                break;
            case ElementEvents::IMAGE:
                $image = $current->addImage($event->getContent());
                $this->dispatcher->dispatch(
                    DocumentEvents::IMAGE_CREATED,
                    new DocumentEvent($this->section, $image, $event)
                );
                break;
            case ElementEvents::LINK:
                $link = $current->addLink($event->getAttribute(ElementAttributes::LINK), $event->getContent());
                $this->dispatcher->dispatch(
                    DocumentEvents::LINK_CREATED,
                    new DocumentEvent($this->section, $link, $event)
                );
                break;
            case ElementEvents::TABLE_END:
                $this->dispatcher->dispatch(
                    DocumentEvents::TABLE_CREATED,
                    new DocumentEvent($this->section, $current, $this->context['event'])
                );
                $this->context->undo();
                break;
            case ElementEvents::PARAGRAPH_END:
                $this->dispatcher->dispatch(
                    DocumentEvents::PARAGRAPH_CREATED,
                    new DocumentEvent($this->section, $current, $this->context['event'])
                );
                $this->context->undo();
                break;
            case ElementEvents::TABLE_ROW_END:
                $this->dispatcher->dispatch(
                    DocumentEvents::TABLE_ROW_CREATED,
                    new DocumentEvent($this->section, $current, $this->context['event'])
                );
                $this->context->undo();
                break;
            case ElementEvents::TABLE_CELL_END:
                $this->dispatcher->dispatch(
                    DocumentEvents::TABLE_CELL_CREATED,
                    new DocumentEvent($this->section, $current, $this->context['event'])
                );
                $this->context->undo();
                break;
            default:
                throw new \RuntimeException("Cannot handle element of type '{$event->getElement()}'");
        }

        $current = $this->context->get('current', $this->section);

        if ($current === $this->section) {
            $this->dispatcher->dispatch(
                DocumentEvents::AFTER_ROOT_ELEMENT,
                new DocumentConstructionEvent($this->section)
            );
        }
    }

    public function getOutput()
    {
        return '';
    }
}
