<?php
declare(strict_types=1);

namespace UnknownOre\EnchantUI\utils;

use function spl_object_hash;

class EntriesHolder{

	private array $entries = [];

	public function getEntries():array{
		return $this->entries;
	}

	public function addEntry(Object $object): void{
		$this->entries[spl_object_hash($object)] = $object;
	}

	public function removeEntry(Object $object): void{
		unset($this->entries[spl_object_hash($object)]);
	}

	public function entryExists(Object $object): bool{
		return isset($this->entries[spl_object_hash($object)]);
	}

}