<?php
declare(strict_types=1);

namespace UnknownOre\EnchantUI\shop\type;

class Product{

	const PRICE = "price";
	const ECONOMY = "economy";
	const MINIMUM = "minimum";
	const MAXIMUM = "maximum";
	const SLOTS = "slots";
	const INCOMPATIBLE = "incompatible";

	private string $economy;

	private int $minimum, $maximum;

	private float $price;

	private array $slots, $incompatible;

	public function __construct(array $data){
		$this->load($data);
	}

	public function getMinimumLevel(): int{
		return $this->minimum;
	}

	public function setMinimumLevel(int $level): void{
		$this->minimum = $level;
	}

	public function getMaximumLevel(): int{
		return $this->maximum;
	}

	public function setMaximumLevel(int $level): void{
		$this->maximum = $level;
	}

	public function getPrice():float{
		return $this->price;
	}

	public function setPrice(float $price):void{
		$this->price = $price;
	}

	public function getEconomy():string{
		return $this->economy;
	}

	public function getCompatibleSlots():array{
		return $this->slots;
	}

	public function setCompatibleSlots(array $slots): void{
		$this->slots = $slots;
	}

	public function getInCompatibleEnchantments():array{
		return $this->incompatible;
	}

	public function setInCompatibleEnchantments(array $enchantments): void{
		$this->incompatible = $enchantments;
	}

	private function load(array $data): void{
		$this->minimum = $data[self::MINIMUM] ?? 0;
		$this->maximum = $data[self::MAXIMUM] ?? 1;
		$this->price = $data[self::PRICE] ?? 0;
		$this->economy = $data[self::ECONOMY] ?? "";
		$this->slots = $data[self::SLOTS] ?? [];
		$this->incompatible = $data[self::INCOMPATIBLE] ?? [];
	}

	public function __asArray():array{
		return [
			self::PRICE => $this->price,
			self::ECONOMY => $this->economy,
			self::SLOTS => $this->slots,
			self::INCOMPATIBLE => $this->incompatible];
	}

	public static function create(float $price):Product{
		return new Product([
			self::PRICE => $price
		]);
	}

}