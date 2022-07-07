<?php
declare(strict_types=1);

namespace UnknownOre\EnchantUI;

use pocketmine\plugin\PluginBase;
use UnknownOre\EnchantUI\commands\EnchantShopCommand;
use UnknownOre\EnchantUI\economy\EconomyManager;
use UnknownOre\EnchantUI\language\LanguagesManager;
use UnknownOre\EnchantUI\shop\EnchantsShop;

class EnchantUI extends PluginBase{

	private EnchantsShop $shop;
	private EconomyManager $economyManager;
	private LanguagesManager $languagesManager;

	public function onEnable():void{
		$this->getServer()->getCommandMap()->register("eshop",new EnchantShopCommand($this));

		$this->shop = new EnchantsShop($this);
		$this->economyManager = new EconomyManager();
		$this->languagesManager = new LanguagesManager($this);
	}

	public function getShop():EnchantsShop{
		return $this->shop;
	}

	public function getEconomyManager(): EconomyManager{
		return $this->economyManager;
	}

	public function getLanguageManager(): LanguagesManager{
		return $this->languagesManager;
	}

}