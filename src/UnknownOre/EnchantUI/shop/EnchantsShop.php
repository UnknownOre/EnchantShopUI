<?php
declare(strict_types=1);

namespace UnknownOre\EnchantUI\shop;

use pocketmine\player\Player;
use UnknownOre\EnchantUI\EnchantUI;
use UnknownOre\EnchantUI\shop\type\Category;
use function yaml_emit_file;
use function yaml_parse_file;

class EnchantsShop{

	private Category $root;

	public function __construct(private EnchantUI $plugin){
		$data = yaml_parse_file($plugin->getDataFolder() . "shop.yml",);
		$this->root = new Category($data);
	}

	public function send(Player $player):void{
		$player->sendForm($this->root->getForm());
	}

	public function save(): void{
		yaml_emit_file($this->plugin->getDataFolder() . "shop.yml", $this->root->asArray());
	}

}