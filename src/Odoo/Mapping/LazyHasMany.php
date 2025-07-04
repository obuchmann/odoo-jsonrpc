<?php

namespace Obuchmann\OdooJsonRpc\Odoo\Mapping;

use ArrayAccess;
use Countable;
use Iterator;

class LazyHasMany implements ArrayAccess, Iterator, Countable
{
    private $object;
    private $method;
    private $args;
    private $data = null;
    private $loaded = false;
    private $keys = null;
    private $position = 0;

    public function __construct($object, string $method, array $args = [])
    {
        $this->object = $object;
        $this->method = $method;
        $this->args = $args;
    }

    private function load()
    {
        if (!$this->loaded) {
            $this->data = call_user_func_array(
                [$this->object, $this->method],
                $this->args
            );
            $this->loaded = true;
            // Reset keys cache when data is loaded
            $this->keys = null;
        }
        return $this->data;
    }

    private function getKeys()
    {
        if ($this->keys === null) {
            $data = $this->load();
            $this->keys = is_array($data) ? array_keys($data) : [];
        }
        return $this->keys;
    }

    public function offsetExists($offset): bool
    {
        return array_key_exists($offset, $this->load());
    }

    public function offsetGet($offset): mixed
    {
        return $this->load()[$offset] ?? null;
    }

    public function offsetSet($offset, $value): void
    {
        $data = $this->load();
        if ($offset === null) {
            $data[] = $value;
        } else {
            $data[$offset] = $value;
        }
        $this->data = $data;
        // Reset keys cache when data is modified
        $this->keys = null;
    }

    public function offsetUnset($offset): void
    {
        $data = $this->load();
        unset($data[$offset]);
        $this->data = $data;
        // Reset keys cache when data is modified
        $this->keys = null;
    }

    // Method to check if data is loaded without triggering load
    public function isLoaded(): bool
    {
        return $this->loaded;
    }

    // Method to force reload
    public function reload()
    {
        $this->loaded = false;
        $this->data = null;
        $this->keys = null;
        $this->position = 0;
        return $this;
    }

    // Countable interface implementation
    public function count(): int
    {
        $data = $this->load();
        return is_array($data) || $data instanceof Countable ? count($data) : 0;
    }

    // Iterator interface implementation
    public function current(): mixed
    {
        $keys = $this->getKeys();
        if (!isset($keys[$this->position])) {
            return null;
        }
        $key = $keys[$this->position];
        return $this->load()[$key] ?? null;
    }

    public function next(): void
    {
        $this->position++;
    }

    public function key(): mixed
    {
        $keys = $this->getKeys();
        return $keys[$this->position] ?? null;
    }

    public function valid(): bool
    {
        $keys = $this->getKeys();
        return isset($keys[$this->position]);
    }

    public function rewind(): void
    {
        $this->position = 0;
    }
}