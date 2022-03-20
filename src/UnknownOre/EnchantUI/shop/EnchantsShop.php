<?php
declare(strict_types=1);

namespace UnknownOre\EnchantUI\shop;

use pocketmine\player\Player;
use pocketmine\utils\TextFormat as C;
use UnknownOre\EnchantUI\economy\EconomyManager;
use UnknownOre\EnchantUI\EnchantUI;
use UnknownOre\EnchantUI\libs\dktapps\pmforms\MenuForm;
use UnknownOre\EnchantUI\libs\dktapps\pmforms\MenuOption;
use UnknownOre\EnchantUI\shop\type\Category;
use UnknownOre\EnchantUI\shop\type\SubCategory;
use function count;
use function yaml_emit_file;
use function yaml_parse_file;

class EnchantsShop{

	private Category $root;

	public function __construct(private EnchantUI $plugin){
		$data = yaml_parse_file($plugin->getDataFolder() . "shop.yml",);
		$this->root = new Category($data);
	}

	public function send(Player $player):void{
		$player->sendForm($this->getForm($this->root));
	}

	private function getForm(Category $category): MenuForm{
		$options = [];

		if($this instanceof SubCategory) {
			$options[] = new MenuOption(C::RED . "Back");
		}else{
			$options[] = new MenuOption(C::RED . "Close");
		}

		$categories = $category->getCategories();
		foreach($categories as $subCategory) {
			$options[] = new MenuOption($subCategory->getName());
		}

		$economyManager = EconomyManager::getInstance();
		$products = $category->getProducts();

		foreach($products as $product) {
			$economy = $economyManager->getProviderByName($product->getEconomy());
			if($economy !== null) { //Send an error in console?
				$options[] = new MenuOption($product->getName() . " " . $economy->format($product->getPrice()));
			}
		}

		return new MenuForm($this->getName(), $this->getDescription(), $options, function(Player $player, int $button) use ($categories, $products):void{
			if($button === 0) {
				if($this instanceof SubCategory) {
					$player->sendForm($this->getParent()->getForm());
				}
				return;
			}
			$button--;

			if(isset($categories[$button])) {
				$category = $categories[$button];
				$player->sendForm($this->getForm($category));
				return;
			}

			$button -= (count($categories) - 1);

			$product = $products[$button];
		});
	}

	public function save(): void{
		yaml_emit_file($this->plugin->getDataFolder() . "shop.yml", $this->root->asArray());
	}

}