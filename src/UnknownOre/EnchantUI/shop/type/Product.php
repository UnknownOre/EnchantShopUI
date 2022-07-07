<?php
declare(strict_types=1);

namespace UnknownOre\EnchantUI\shop\type;

use UnknownOre\EnchantUI\utils\Data;
use UnknownOre\EnchantUI\utils\EntryInfo;

class Product implements Data{

	private const INFO = "Info";
	private const ENCHANTMENT = "enchantment";
	private const PRICE = "price";
	private const ECONOMY = "economy";
	private const MINIMUM = "minimum";
	private const MAXIMUM = "maximum";
	private const ITEM_TYPE = "item type";
	private const INCOMPATIBLE = "incompatible";

	private EntryInfo $info;
	private string $enchantment, $economy, $itemType;
	private int $minimum, $maximum;
	private float $price;
	private array $incompatible;

	public function __construct(array $data){
		$this->load($data);
	}

	public function getInfo():EntryInfo{
		return $this->info;
	}

	public function getEnchantment():string{
		return $this->enchantment;
	}

	public function setEnchantment(string $enchantment):void{
		$this->enchantment = $enchantment;
	}

	public function getEconomy():string{
		return $this->economy;
	}

	public function setEconomy(string $economy):void{
		$this->economy = $economy;
	}

	public function getMinimumLevel():int{
		return $this->minimum;
	}

	public function setMinimumLevel(int $level):void{
		$this->minimum = $level;
	}

	public function getMaximumLevel():int{
		return $this->maximum;
	}

	public function setMaximumLevel(int $level):void{
		$this->maximum = $level;
	}

	public function getPrice():float{
		return $this->price;
	}

	public function setPrice(float $price):void{
		$this->price = $price;
	}

	public function getItemType():string{
		return $this->itemType;
	}

	public function setItemType(string $itemType):void{
		$this->itemType = $itemType;
	}

	public function getInCompatibleEnchantments():array{
		return $this->incompatible;
	}

	public function setInCompatibleEnchantments(array $enchantments):void{
		$this->incompatible = $enchantments;
	}

	private function load(array $data):void{
		$this->info = new EntryInfo($data[self::INFO] ?? []);
		$this->enchantment = $data[self::ENCHANTMENT] ?? "protection";
		$this->minimum = max($data[self::MINIMUM] ?? 1, 1);
		$this->maximum = max($data[self::MAXIMUM] ?? 1, $this->minimum);
		$this->price = $data[self::PRICE] ?? 1;
		$this->economy = $data[self::ECONOMY] ?? "";
		$this->itemType = $data[self::ITEM_TYPE] ?? "";
		$this->incompatible = $data[self::INCOMPATIBLE] ?? [];
	}

	public function __asArray():array{
		return [
			self::INFO => $this->info->__asArray(),
			self::PRICE => $this->price,
			self::ECONOMY => $this->economy,
			self::ITEM_TYPE => $this->itemType,
			self::INCOMPATIBLE => $this->incompatible];
	}

}