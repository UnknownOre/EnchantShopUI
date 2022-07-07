<?php
declare(strict_types=1);

namespace UnknownOre\EnchantUI\utils;

use function spl_object_hash;

class EntriesHolder implements Data{

	private array $entries = [];

	public function getEntries():array{
		return $this->entries;
	}

	public function addEntry(Data $object): void{
		$this->entries[spl_object_hash($object)] = $object;
	}

	public function removeEntry(Data $object): void{
		unset($this->entries[spl_object_hash($object)]);
	}

	public function entryExists(Data $object): bool{
		return isset($this->entries[spl_object_hash($object)]);
	}

	public function __asArray():array{
		$data = [];

		foreach($this->entries as $entry){
			$data[] = $entry->__asArray();
		}

		return $data;
	}

	public function clear(): void{
		$this->entries = [];
	}

}