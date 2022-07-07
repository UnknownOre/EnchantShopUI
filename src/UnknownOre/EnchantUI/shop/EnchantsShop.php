<?php
declare(strict_types=1);

namespace UnknownOre\EnchantUI\shop;

use Exception;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\enchantment\StringToEnchantmentParser;
use pocketmine\player\Player;
use pocketmine\utils\Config;
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
use function array_keys;
use function count;
use function is_array;

class EnchantsShop{

	//TODO: Messages

	private Category $root;
	private Config $config;

	public function __construct(private EnchantUI $plugin){
		$this->config = $config = new Config($this->plugin->getDataFolder() . "shop.yml");

		$root = $config->get("shop");

		$this->root = new Category(is_array($root) ? $root : []);
	}

	public function send(Player $player):void{
		$player->sendForm($this->getCategoryForm($player, $this->root));
	}

	private function getCategoryForm(Player $player, Category $category):MenuForm{
		$options = [];

		$options[] = $category instanceof SubCategory ? new MenuOption("Go Back") : new MenuOption("Close");
		$player->hasPermission("eshop.admin") && $options[] = new MenuOption("Edit");

		/** @var SubCategory[] $subCategories */
		$subCategories = $category->getCategories()->getEntries();
		foreach($subCategories as $subCategory) {
			$options[] = new MenuOption($subCategory->getInfo()->getName());
		}

		$economyManager = EconomyManager::getInstance();

		/** @var Product[] $products */
		$products = $category->getProducts()->getEntries();
		foreach($products as $key => $product) {
			if($economyManager->getProviderByName($key) === null) {
				unset($products[$key]);
				continue;
			}

			$options[] = new MenuOption($product->getInfo()->getName());
		}

		return new MenuForm($category->getInfo()->getDescription(), $category->getInfo()->getDescription(), $options, function(Player $player, int $data) use ($category, $subCategories, $products):void{
			if($data === 0) {
				$category instanceof SubCategory && $player->sendForm($this->getCategoryForm($player, $category->getParent()));
				return;
			}

			$data--;

			if($player->hasPermission("eshop.admin")) {
				if($data === 0) {
					//todo: admin
					return;
				}

				$data--;
			}

			$subCategoriesCount = count($subCategories);

			if($subCategoriesCount > $data) {
				$subCategory = $subCategories[array_keys($subCategories)[$data]];
				if($category->getCategories()->entryExists($subCategory)) {
					$player->sendForm($this->getCategoryForm($player, $subCategory));
					return;
				}
			}

			$subCategoriesCount > 0 && $data -= $subCategoriesCount;

			$product = $products[array_keys($products)[$data]];

			if($category->getProducts()->entryExists($product)) {
				$player->sendForm($this->getProductForm($product, $category));
			}
		});
	}

	private function getProductForm(Product $product, Category $parent):CustomForm{
		$options = [];

		$info = $product->getInfo();

		$name = $info->getName();
		$description = $info->getDescription();

		if($description !== "") {
			$options[] = new Label("description", $description);
		}

		$options[] = new Slider("level", "Level", $product->getMinimumLevel(), $product->getMaximumLevel());

		return new CustomForm($name, $options, function(Player $player, CustomFormResponse $response) use ($product, $parent):void{
			$level = (int) $response->getFloat("level");

			$economy = EconomyManager::getInstance()->getProviderByName($product->getEconomy());

			$economy->getBalance($player, function(float $amount ) use ($product, $player, $level): void{
				if(!$player->isOnline()){
					//there's a chance the player would leave before the provider receives the player balance (async)
					return;
				}

				if($amount < $product->getPrice() * $level){
					return;
				}

				$item = $player->getInventory()->getItemInHand();
				if($item->isNull()) {
					return;
				}

				$incompatible = $product->getInCompatibleEnchantments();

				$restricted = [];

				foreach($incompatible as $id){
					if($item->hasEnchantment($id)){
						$restricted[] = $id;
					}
				}

				if($restricted !== []){
					return;
				}

				//todo: slots compatibility

				$item->addEnchantment(new EnchantmentInstance(StringToEnchantmentParser::getInstance()->parse($product->getEnchantment()), $level));
			});

		}, function(Player $player) use ($parent):void{
			$player->sendForm($this->getCategoryForm($player, $parent));
		});
	}


	private function save():void{
		$this->config->setAll($this->root->__asArray());
		try{
			$this->config->save();
		}catch(Exception $exception){
			$this->plugin->getLogger()->error("Couldn't save shop data: " . $exception->getMessage());
		}
	}
}