<?php
declare(strict_types=1);

namespace UnknownOre\EnchantUI;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\plugin\PluginBase;
use UnknownOre\EnchantUI\economy\EconomyManager;
use UnknownOre\EnchantUI\shop\EnchantsShop;

class EnchantUI extends PluginBase implements Listener{

	private EnchantsShop $shop;
	private EconomyManager $manager;

	public function onEnable():void{
		$this->shop = new EnchantsShop($this);
		$this->manager = new EconomyManager();
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
	}

	public function getShop():EnchantsShop{
		return $this->shop;
	}

	public function getEconomyManager(): EconomyManager{
		return $this->manager;
	}

	public function onInteract(PlayerInteractEvent $event){
		$player = $event->getPlayer();

	}

}