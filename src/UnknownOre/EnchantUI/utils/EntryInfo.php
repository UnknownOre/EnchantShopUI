<?php
declare(strict_types=1);

namespace UnknownOre\EnchantUI\utils;

class EntryInfo implements Data{

	private const NAME = "name";
	private const DESCRIPTION = "description";

	private string $name, $description;

	public function __construct(array $data){
		$this->load($data);
	}

	public function getName(): string{
		return $this->name;
	}

	public function setName(string $name): void{
		$this->name = $name;
	}

	public function getDescription(): string{
		return $this->description;
	}

	public function setDescription(string $description): void{
		$this->description = $description;
	}

	private function load(array $data): void{
		$this->name = $data[self::NAME] ?? "";
		$this->description = $data[self::DESCRIPTION] ?? "";
	}

	public function __asArray():array{
		return [
			self::NAME => $this->name,
			self::DESCRIPTION => $this->description
		];
	}

}