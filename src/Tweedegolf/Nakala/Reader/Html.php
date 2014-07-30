<?php

namespace Tweedegolf\Nakala\Reader;

use Tweedegolf\Nakala\ElementAttributes;
use Tweedegolf\Nakala\Event\ElementEvent;
use Tweedegolf\Nakala\Event\ElementEvents;
use Tweedegolf\Nakala\Util\AttributeList;

class Html implements ReaderInterface
{
    /**
     * @var \DOMDocument
     */
    private $content;

    /**
     * @var ElementEvent[]
     */
    private $elements;

    /**
     * @var AttributeList
     */
    private $attributes;

    public function setContent($content)
    {
        $document = new \DOMDocument();
        libxml_use_internal_errors(true);
        $document->loadHTML($content);
        if (count(libxml_get_errors()) > 0) {
            libxml_clear_errors();
            throw new \RuntimeException("No valid HTML document provided");
        }

        $this->elements = [];
        $this->content = $document;
        $this->attributes = new AttributeList();
        $this->extractElements();

        return $this;
    }

    protected function extractElements()
    {
        $head = $this->xpathFirst($this->content, '//head');
        if ($head) {
            $title = $this->xpathFirst($head, './title');
            if ($title) {
                $this->elements[] = new ElementEvent('document_title', $this->normalizeSpace($title->nodeValue));
            }
        }

        $body = $this->xpathFirst($this->content, '//body');
        if ($body) {
            $this->extractChildElements($body, true);
        }
    }

    protected function extractChildElements(\DOMNode $elem, $skipWhitespace = false, $parentIsBlock = false)
    {
        /** @var \DOMNode $child */
        $first = true;
        foreach ($elem->childNodes as $child) {
            if ($skipWhitespace && $child->nodeName === '#text' && ctype_space($child->nodeValue)) {
                // do nothing
            } elseif ($child->nodeName === '#text') {
                $this->elements[] = new ElementEvent(
                    ElementEvents::TEXT,
                    $this->normalizeSpace($child->nodeValue, $parentIsBlock && $first),
                    $this->attributes->values()
                );
            } else {
                switch ($child->nodeName) {
                    case 'p':
                        $this->elements[] = new ElementEvent(ElementEvents::PARAGRAPH_START);
                        $this->extractChildElements($child, false, true);
                        $this->elements[] = new ElementEvent(ElementEvents::PARAGRAPH_END);
                        break;
                    case 'ul':
                        $this->attributes->setMany([
                            'list_depth' => $this->attributes->get('list_depth', 0) + 1,
                            'list_numbered' => false,
                        ]);
                        $this->elements[] = new ElementEvent(ElementEvents::LIST_START, '', [
                            ElementAttributes::NUMBERED => false
                        ]);
                        $this->extractChildElements($child, true);
                        $this->elements[] = new ElementEvent(ElementEvents::LIST_END, '', [
                            ElementAttributes::NUMBERED => false
                        ]);
                        $this->attributes->undo();
                        break;
                    case 'ol':
                        $this->attributes->setMany([
                            'list_depth' => $this->attributes->get('list_depth', 0) + 1,
                            'list_numbered' => true,
                        ]);
                        $this->elements[] = new ElementEvent(ElementEvents::LIST_START, '', [
                            ElementAttributes::NUMBERED => true
                        ]);
                        $this->extractChildElements($child, true);
                        $this->elements[] = new ElementEvent(ElementEvents::LIST_END, '', [
                            ElementAttributes::NUMBERED => true
                        ]);
                        $this->attributes->undo();
                        break;
                    case 'li':
                        $this->elements[] = new ElementEvent(ElementEvents::LIST_ITEM_START, '', [
                            ElementAttributes::DEPTH => $this->attributes['list_depth'] - 1,
                            ElementAttributes::NUMBERED => $this->attributes['list_numbered'],
                        ]);
                        $this->extractChildElements($child, false, true);
                        $this->elements[] = new ElementEvent(ElementEvents::LIST_ITEM_END, '', [
                            ElementAttributes::DEPTH => $this->attributes['list_depth'] - 1,
                            ElementAttributes::NUMBERED => $this->attributes['list_numbered'],
                        ]);
                        break;
                    case 'table':
                        $this->elements[] = new ElementEvent(ElementEvents::TABLE_START);
                        $this->extractChildElements($child, true);
                        $this->elements[] = new ElementEvent(ElementEvents::TABLE_END);
                        break;
                    case 'tr':
                        $this->elements[] = new ElementEvent(ElementEvents::TABLE_ROW_START);
                        $this->extractChildElements($child, true);
                        $this->elements[] = new ElementEvent(ElementEvents::TABLE_ROW_END);
                        break;
                    case 'th':
                    case 'td':
                        $this->elements[] = new ElementEvent(ElementEvents::TABLE_CELL_START, '', [
                            ElementAttributes::HEADER => $child->nodeName === 'th'
                        ]);
                        $this->extractChildElements($child);
                        $this->elements[] = new ElementEvent(ElementEvents::TABLE_CELL_END, '', [
                            ElementAttributes::HEADER => $child->nodeName === 'th'
                        ]);
                        break;
                    case 'tbody':
                    case 'thead':
                        $this->extractChildElements($child, true);
                        break;
                    case 'h1':
                        $this->elements[] = new ElementEvent(
                            ElementEvents::TITLE,
                            $this->normalizeSpace($child->nodeValue),
                            [
                                ElementAttributes::DEPTH => 0
                            ]
                        );
                        break;
                    case 'h2':
                        $this->elements[] = new ElementEvent(
                            ElementEvents::TITLE,
                            $this->normalizeSpace($child->nodeValue),
                            [
                                ElementAttributes::DEPTH => 1
                            ]
                        );
                        break;
                    case 'h3':
                        $this->elements[] = new ElementEvent(
                            ElementEvents::TITLE,
                            $this->normalizeSpace($child->nodeValue),
                            [
                                ElementAttributes::DEPTH => 2
                            ]
                        );
                        break;
                    case 'h4':
                        $this->elements[] = new ElementEvent(
                            ElementEvents::TITLE,
                            $this->normalizeSpace($child->nodeValue),
                            [
                                ElementAttributes::DEPTH => 3
                            ]
                        );
                        break;
                    case 'h5':
                        $this->elements[] = new ElementEvent(
                            ElementEvents::TITLE,
                            $this->normalizeSpace($child->nodeValue),
                            [
                                ElementAttributes::DEPTH => 4
                            ]
                        );
                        break;
                    case 'h6':
                        $this->elements[] = new ElementEvent(
                            ElementEvents::TITLE,
                            $this->normalizeSpace($child->nodeValue),
                            [
                                ElementAttributes::DEPTH => 5
                            ]
                        );
                        break;
                    case 'div':
                        $this->extractChildElements($child, true);
                        break;
                    case 'span':
                        $this->extractChildElements($child);
                        break;
                    case 'strong':
                    case 'b':
                        $this->attributes[ElementAttributes::BOLD] = true;
                        $this->extractChildElements($child);
                        $this->attributes->undo();
                        break;
                    case 'em':
                    case 'i':
                        $this->attributes[ElementAttributes::ITALIC] = true;
                        $this->extractChildElements($child);
                        $this->attributes->undo();
                        break;
                    case 'u':
                        $this->attributes[ElementAttributes::UNDERLINE] = 'single';
                        $this->extractChildElements($child);
                        $this->attributes->undo();
                        break;
                    case 'del':
                        $this->attributes[ElementAttributes::STRIKETHROUGH] = true;
                        $this->extractChildElements($child);
                        $this->attributes->undo();
                        break;
                    case 'sup':
                        $this->attributes[ElementAttributes::SUPERSCRIPT] = true;
                        $this->extractChildElements($child);
                        $this->attributes->undo();
                        break;
                    case 'sub':
                        $this->attributes[ElementAttributes::SUBSCRIPT] = true;
                        $this->extractChildElements($child);
                        $this->attributes->undo();
                        break;
                    case 'a':
                        $href = $child->attributes->getNamedItem('href')->nodeValue;
                        $this->attributes[ElementAttributes::LINK] = $href;
                        $this->elements[] = new ElementEvent(
                            ElementEvents::LINK,
                            $this->normalizeSpace($child->nodeValue),
                            $this->attributes->values()
                        );
                        $this->attributes->undo();
                        break;
                    case 'img':
                        $this->elements[] = new ElementEvent(
                            ElementEvents::IMAGE,
                            $child->attributes->getNamedItem('src')->nodeValue
                        );
                        break;
                    default:
                        throw new \RuntimeException("Element of type {$child->nodeName} not supported");
                        break;
                }
            }
            $first = false;
        }
    }

    /**
     * @param \DOMNode $element
     * @param string   $xpathExpr
     * @return \DOMNode
     */
    protected function xpathFirst(\DOMNode $element, $xpathExpr)
    {
        if ($element instanceof \DOMDocument) {
            $xpath = new \DOMXPath($element);
            $result = $xpath->query($xpathExpr);
        } else {
            $xpath = new \DOMXPath($element->ownerDocument);
            $result = $xpath->query($xpathExpr, $element);
        }

        if ($result && $result->length > 0) {
            return $result->item(0);
        }
        return null;
    }

    /**
     * @param string $string
     * @return string
     */
    protected function normalizeSpace($string, $cleanStart = false)
    {
        if (ctype_space($string)) {
            $str = ' ';
        } else {
            $str = preg_replace('/\s+/', ' ', $string);
        }

        if ($cleanStart) {
            $str = ltrim($str);
        }
        return $str;
    }

    public function next()
    {
        $current = array_shift($this->elements);
        if (null === $current) {
            return false;
        }
        return $current;
    }
} 
