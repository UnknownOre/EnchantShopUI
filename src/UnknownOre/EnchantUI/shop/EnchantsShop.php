<?php
declare(strict_types=1);

namespace UnknownOre\EnchantUI\shop;

use pocketmine\player\Player;
use UnknownOre\EnchantUI\EnchantUI;
use UnknownOre\EnchantUI\shop\type\Category;

class EnchantsShop{

	private Category $root;

	public function __construct(EnchantUI $plugin){
		$this->root = new Category([]);
	}

	public function send(Player $player):void{
		$player->sendForm($this->root->getForm());
	}

}