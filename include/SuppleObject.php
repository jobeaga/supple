<?php

require_once('include/SuppleApplication.php');

abstract class SuppleObject implements IteratorAggregate {

    public $db;
    private array $_properties = [];

    function __construct() {
        $this->db = SuppleApplication::getdb();
    }

    public function __set(string $name, mixed $value): void {
        $this->_properties[$name] = $value;
    }

    public function __get(string $name): mixed {
        return $this->_properties[$name] ?? '';
    }

    public function __isset(string $name): bool {
        return isset($this->_properties[$name]);
    }

    public function __unset(string $name): void {
        unset($this->_properties[$name]);
    }

    public function getIterator(): Traversable {
        return new ArrayIterator($this->_properties);
    }

}