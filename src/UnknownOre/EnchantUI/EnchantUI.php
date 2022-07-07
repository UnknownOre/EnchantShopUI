<?php
declare(strict_types=1);

namespace UnknownOre\EnchantUI;

use pocketmine\plugin\PluginBase;
use UnknownOre\EnchantUI\economy\EconomyManager;
use UnknownOre\EnchantUI\shop\EnchantsShop;

class EnchantUI extends PluginBase{

	private EnchantsShop $shop;
	private EconomyManager $manager;

	public function onEnable():void{
		$this->shop = new EnchantsShop($this);
		$this->manager = new EconomyManager();
	}

	public function getShop():EnchantsShop{
		return $this->shop;
	}

	public function getEconomyManager(): EconomyManager{
		return $this->manager;
	}

}