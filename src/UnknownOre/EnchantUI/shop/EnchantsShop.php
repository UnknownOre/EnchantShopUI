<?php
declare(strict_types=1);

namespace UnknownOre\EnchantUI\shop;

use pocketmine\player\Player;
use pocketmine\utils\TextFormat as C;
use UnknownOre\EnchantUI\economy\EconomyManager;
use UnknownOre\EnchantUI\EnchantUI;
use UnknownOre\EnchantUI\libs\dktapps\pmforms\CustomForm;
use UnknownOre\EnchantUI\libs\dktapps\pmforms\CustomFormResponse;
use UnknownOre\EnchantUI\libs\dktapps\pmforms\element\Label;
use UnknownOre\EnchantUI\libs\dktapps\pmforms\element\Slider;
use UnknownOre\EnchantUI\libs\dktapps\pmforms\MenuForm;
use UnknownOre\EnchantUI\libs\dktapps\pmforms\MenuOption;
use UnknownOre\EnchantUI\shop\type\Category;
use UnknownOre\EnchantUI\shop\type\Product;
use UnknownOre\EnchantUI\shop\type\SubCategory;
use function count;
use function yaml_emit_file;
use function yaml_parse_file;

class EnchantsShop{

	private Category $root;

	public function __construct(private EnchantUI $plugin){
		$data = yaml_parse_file($plugin->getDataFolder() . "shop.yml");
		$this->root = new Category($data);
	}

	public function send(Player $player):void{
		$player->sendForm($this->getForm($player, $this->root));
	}

	private function getForm(Player $player, Category $category):MenuForm{
		$options = [];

		if($category instanceof SubCategory) {
			$options[] = new MenuOption(C::RED . "Back");
		}else{
			$options[] = new MenuOption(C::RED . "Close");
		}

		if($player->hasPermission("eshop.admin")) {
			$options[] = new MenuOption(C::DARK_AQUA . "Add Category");
			$options[] = new MenuOption(C::DARK_AQUA . "Add Product");
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

		return new MenuForm($category->getName(), $category->getDescription(), $options, function(Player $player, int $button) use ($category, $categories, $products):void{
			if($button === 0) {
				if($category instanceof SubCategory) {
					$player->sendForm($category->getParent()->getForm());
				}
				return;
			}
			$button--;
			if($player->hasPermission("eshop.admin")) {

			}

			if(isset($categories[$button])) {
				$player->sendForm($this->getForm($player, $categories[$button]));
				return;
			}

			$button -= (count($categories) - 1);

			$product = $products[$button];
			$player->sendForm($this->getProductInfoForm($category, $product, $button));
		});
	}

	private function getProductInfoForm(Category $category, Product $product, int $id):CustomForm{
		$options = [];

		if($product->getDescription() !== "") {
			$options[] = new Label("description", $product->getDescription());
		}

		$options[] = new Slider("level", "Level", $product->getMinimumLevel(), $product->getMaximumLevel());

		return new CustomForm($product->getName(), $options, function(Player $player, CustomFormResponse $response) use ($category, $product, $id):void{
			if($category->exists()) {
				$player->sendMessage(C::RED . "The category has been deleted.");
				return;
			}
			$economy = EconomyManager::getInstance()->getProviderByName($product->getEconomy());
			$level = (int) $response->getFloat("level");
			$economy->getBalance($player, function(float $value) use ($player, $category, $product, $id, $level, $economy):void{
				if(!$player->isConnected()) {
					return;
				}

				if(!$category->exists()) {
					$player->sendMessage(C::RED . "The category has been deleted.");
					return;
				}

				$products = $category->getProducts();

				if(!isset($products[$id]) || $products[$id] !== $product) {
					$player->sendMessage(C::RED . "The product has been deleted.");
					return;
				}

				if($value < ($product->getPrice() * $level)) {
					$player->sendMessage(C::RED . "You don't have enough money.");
					return;
				}

				$economy->reduceBalance($player, $product->getPrice() * $level);
			});
		}, function(Player $player) use ($category):void{
			$player->sendForm($this->getForm($player, $category));
		});

	}

	public function save():void{
		yaml_emit_file($this->plugin->getDataFolder() . "shop.yml", $this->root->asArray());
	}

}