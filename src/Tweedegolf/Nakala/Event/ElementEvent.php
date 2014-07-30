<?php

namespace Tweedegolf\Nakala\Event;

use Symfony\Component\EventDispatcher\Event;

class ElementEvent extends Event implements \ArrayAccess
{
    /**
     * @var string
     */
    private $element;

    /**
     * @var string
     */
    private $content;

    /**
     * @var array
     */
    private $attributes;

    /**
     * @param string $element
     * @param string $content
     * @param array  $attributes
     */
    public function __construct($element, $content = '', array $attributes = [])
    {
        $this->element = $element;
        $this->content = $content;
        $this->attributes = $attributes;
    }

    /**
     * @return string
     */
    public function getElement()
    {
        return $this->element;
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @return array
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * @param string $key
     * @param mixed  $default
     * @return mixed
     */
    public function getAttribute($key, $default = null)
    {
        if (isset($this->attributes[$key])) {
            return $this->attributes[$key];
        }
        return $default;
    }

    /**
     * {@inheritdoc}
     */
    public function offsetExists($offset)
    {
        return isset($this->attributes[$offset]);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetGet($offset)
    {
        return $this->offsetExists($offset) ? $this->attributes[$offset] : null;
    }

    /**
     * {@inheritdoc}
     */
    public function offsetSet($offset, $value)
    {
        $this->attributes[$offset] = $value;
    }

    /**
     * {@inheritdoc}
     */
    public function offsetUnset($offset)
    {
        unset($this->attributes[$offset]);
    }

    /**
     * @param string $content
     * @return $this
     */
    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * @param string $element
     * @return $this
     */
    public function setElement($element)
    {
        $this->element = $element;

        return $this;
    }
}
