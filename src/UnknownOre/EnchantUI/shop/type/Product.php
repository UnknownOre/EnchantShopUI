<?php
declare(strict_types=1);

namespace UnknownOre\EnchantUI\shop\type;

use function gettype;

class Product{

	private const
		PRICE = "price", NAME = "name", ECONOMY = "economy";

	private string $name, $economy;
	private float $price;

	public function __construct(array $data){
		$this->name = $data[self::NAME];
		$this->price = $data[self::PRICE];
		$this->economy = $data[self::ECONOMY];
	}

	public function getName():string{
		return $this->name;
	}

	public function getPrice():float{
		return $this->price;
	}

	public function getEconomy():string{
		return $this->economy;
	}

	public function asArray():array{
		return [
			self::PRICE => $this->price,
			self::NAME => $this->name,
			self::ECONOMY => $this->economy];
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
			self::ECONOMY => ""];

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