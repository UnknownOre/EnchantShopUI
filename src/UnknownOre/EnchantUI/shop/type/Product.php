<?php
declare(strict_types=1);

namespace UnknownOre\EnchantUI\shop\type;

use function gettype;

class Product{

	private const
		PRICE = "price", NAME = "name", MINIMUM = "minimum", MAXIMUM = "maximum", DESCRIPTION = "description", ECONOMY = "economy", SLOTS = "slots", INCOMPATIBLE = "incompatible";

	private string $name, $description, $economy;

	private int $minimum, $maximum;

	private float $price;

	private array $slots, $incompatible;

	public function __construct(array $data){
		$this->name = $data[self::NAME];
		$this->minimum = $data[self::MINIMUM];
		$this->maximum = $data[self::MAXIMUM];
		$this->description = $data[self::DESCRIPTION];
		$this->price = $data[self::PRICE];
		$this->economy = $data[self::ECONOMY];
		$this->slots = $data[self::SLOTS];
		$this->incompatible = $data[self::INCOMPATIBLE];
	}

	public function getName():string{
		return $this->name;
	}

	public function getDescription():string{
		return $this->description;
	}

	public function getMinimumLevel(): int{
		return $this->minimum;
	}

	public function getMaximumLevel(): int{
		return $this->maximum;
	}

	public function getPrice():float{
		return $this->price;
	}

	public function getEconomy():string{
		return $this->economy;
	}

	public function getCompatibleSlots():array{
		return $this->slots;
	}

	public function getInCompatibleEnchantments():array{
		return $this->incompatible;
	}

	public function asArray():array{
		return [
			self::PRICE => $this->price,
			self::NAME => $this->name,
			self::ECONOMY => $this->economy,
			self::SLOTS => $this->slots,
			self::INCOMPATIBLE => $this->incompatible];
	}

	public static function create(string $name, float $price):Product{
		return new Product([
			self::NAME => $name,
			self::PRICE => $price]);
	}

	public static function valid(array $data):bool{
		$default = [
			self::PRICE => 0.00,
			self::NAME => "",
			self::MINIMUM => 0,
			self::MAXIMUM => 0,
			self::ECONOMY => "",
			self::SLOTS => [],
			self::INCOMPATIBLE => []];

		foreach($default as $key => $value) {
			if(!isset($data[$key])) {
				return false;
			}

			if(gettype($data[$key]) !== gettype($value)) {
				return false;
			}
		}

		return true;
	}

}