<?php

namespace Tweedegolf\Nakala\Util;

class AttributeList implements \ArrayAccess, \IteratorAggregate, \Countable
{
    private $changes = [];

    private $current = [];

    /**
     * @return array
     */
    public function __invoke()
    {
        return $this->values();
    }

    /**
     * @return array
     */
    public function values()
    {
        return $this->current;
    }

    /**
     * Undo the given number of steps
     * @param int $count
     */
    public function undo($count = 1)
    {
        while ($count > 0) {
            array_pop($this->changes);
            $count -= 1;
        }
        $this->rebuild();
    }

    /**
     * Rebuild the cached current array
     */
    private function rebuild()
    {
        $this->current = [];
        foreach ($this->changes as $change) {
            switch ($change['type']) {
                case 'set':
                    foreach ($change['values'] as $key => $value) {
                        $this->current[$key] = $value;
                    }
                    break;
                case 'unset':
                    foreach ($change['values'] as $key) {
                        unset($this->current[$key]);
                    }
                    break;
            }
        }
    }

    /**
     * Retrieve the value of some index in the array
     * @param mixed $key
     * @param mixed $default
     * @return mixed
     */
    public function get($key, $default = null)
    {
        return $this->offsetExists($key) ? $this->current[$key] : $default;
    }

    /**
     * @param mixed $key
     * @param mixed $value
     */
    public function set($key, $value)
    {
        $this->setMany([$key => $value]);
    }

    /**
     * @param array $values
     */
    public function setMany(array $values)
    {
        $this->changes[] = [
            'type' => 'set',
            'values' => $values,
        ];

        foreach ($values as $key => $value) {
            $this->current[$key] = $value;
        }
    }

    /**
     * @param mixed $key
     */
    public function remove($key)
    {
        $this->removeMany([$key]);
    }

    /**
     * @param array $keys
     */
    public function removeMany(array $keys)
    {
        $this->changes[] = [
            'type' => 'unset',
            'values' => $keys,
        ];

        foreach ($keys as $key) {
            unset($this->current[$key]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function offsetExists($offset)
    {
        return isset($this->current[$offset]);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetSet($offset, $value)
    {
        $this->set($offset, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetUnset($offset)
    {
        $this->remove($offset);
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->current);
    }

    /**
     * {@inheritdoc}
     */
    public function count()
    {
        return count($this->current);
    }
}
