<?php
declare(strict_types=1);

namespace UnknownOre\EnchantUI\shop;

use pocketmine\utils\Config;
use UnknownOre\EnchantUI\EnchantUI;
use UnknownOre\EnchantUI\shop\type\Category;
use function is_array;

class EnchantsShop{

	private Category $root;
	private Config $config;

	public function __construct(private EnchantUI $plugin){
		$this->config = $config = new Config($this->plugin->getDataFolder() . "shop.yml");

		$root = $config->get("shop");

		$this->root = new Category(is_array($root) ? $root : []);
	}




}