<?php

require_once('include/SuppleApplication.php');

abstract class SuppleObject {

	public $db;
	private array $propiedades = [];

	function __construct(){

		$this->db = SuppleApplication::getdb();

	}	

    public function __set(string $nombre, mixed $valor): void
    {
        $this->propiedades[$nombre] = $valor;
    }

    public function __get(string $nombre): mixed
    {
        return $this->propiedades[$nombre] ?? '';
    }

    public function __isset(string $nombre): bool
    {
        return isset($this->propiedades[$nombre]);
    }

    public function __unset(string $nombre): void
    {
        unset($this->propiedades[$nombre]);
    }

}