<?php
declare(strict_types=1);

namespace UnknownOre\EnchantUI\shop;

use Exception;
use pocketmine\form\Form;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\enchantment\StringToEnchantmentParser;
use pocketmine\player\Player;
use pocketmine\utils\Config;
use UnknownOre\EnchantUI\economy\EconomyManager;
use UnknownOre\EnchantUI\EnchantUI;
use UnknownOre\EnchantUI\libs\dktapps\pmforms\CustomForm;
use UnknownOre\EnchantUI\libs\dktapps\pmforms\CustomFormResponse;
use UnknownOre\EnchantUI\libs\dktapps\pmforms\element\Dropdown;
use UnknownOre\EnchantUI\libs\dktapps\pmforms\element\Input;
use UnknownOre\EnchantUI\libs\dktapps\pmforms\element\Label;
use UnknownOre\EnchantUI\libs\dktapps\pmforms\element\Slider;
use UnknownOre\EnchantUI\libs\dktapps\pmforms\FormIcon;
use UnknownOre\EnchantUI\libs\dktapps\pmforms\MenuForm;
use UnknownOre\EnchantUI\libs\dktapps\pmforms\MenuOption;
use UnknownOre\EnchantUI\shop\type\Category;
use UnknownOre\EnchantUI\shop\type\Product;
use UnknownOre\EnchantUI\shop\type\SubCategory;
use UnknownOre\EnchantUI\utils\EntryInfo;
use UnknownOre\EnchantUI\utils\ItemUtils;
use function array_keys;
use function array_search;
use function count;
use function filter_var;
use function is_int;
use function strtolower;
use const FILTER_VALIDATE_URL;

class EnchantsShop{

	//TODO: Messages

	private Category $root;
	private Config $config;

	public function __construct(private EnchantUI $plugin){
		$this->config = $config = new Config($this->plugin->getDataFolder() . "shop.yml");
		$this->root = new Category($config->getAll());
	}

	public function send(Player $player):void{
		$player->sendForm($this->getCategoryForm($player, $this->root));
	}

	private function getCategoryForm(Player $player, Category $category):MenuForm{
		$options = [];

		$options[] = $category instanceof SubCategory ? new MenuOption("Back") : new MenuOption("Close");
		$player->hasPermission("eshop.admin") && $options[] = new MenuOption("Edit");

		/** @var SubCategory[] $subCategories */
		$subCategories = $category->getCategories()->getEntries();
		foreach($subCategories as $subCategory) {
			$options[] = new MenuOption($subCategory->getInfo()->getName(), self::getFormIcon($subCategory->getInfo()->getIcon()));
		}

		$economyManager = EconomyManager::getInstance();

		/** @var Product[] $products */
		$products = $category->getProducts()->getEntries();
		foreach($products as $key => $product) {
			if($economyManager->getProviderByName($product->getEconomy()) === null) {
				unset($products[$key]);
				continue;
			}

			$options[] = new MenuOption($product->getInfo()->getName(), self::getFormIcon($product->getInfo()->getIcon()));
		}

		return new MenuForm($category->getInfo()->getName(), $category->getInfo()->getDescription(), $options, function(Player $player, int $data) use ($category, $subCategories, $products):void{
			if($data === 0) {
				$category instanceof SubCategory && $player->sendForm($this->getCategoryForm($player, $category->getParent()));
				return;
			}

			$data--;

			if($player->hasPermission("eshop.admin")) {
				if($data === 0) {
					$player->sendForm($this->editCategoryMenu($category));
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

				if(!ItemUtils::isItemCompatible($item,$product->getItemType())){
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

	private function editCategoryMenu(Category $category): MenuForm{
		$options = [
			new MenuOption("Back"),
			new MenuOption("Edit Info"),
			new MenuOption("Add Category"),
			new MenuOption("Edit Products"),
		];

		if($category instanceof SubCategory) {
			$options[] = new MenuOption("Delete");
		}

		return new MenuForm("Edit ".$category->getInfo()->getName(),"",$options,function(Player $player, int $data) use ($category): void{
			switch($data){
				case 0:
					$player->sendForm($this->getCategoryForm($player, $category));
					break;
				case 1:
					$player->sendForm($this->editInfoForm($category->getInfo(), $this->editCategoryMenu($category)));
					break;
				case 2:
					$subCategory = new SubCategory([], $category);

					$category->getCategories()->addEntry($subCategory);
					$player->sendForm($this->editCategoryMenu($subCategory));
					$this->save();
					break;
				case 3:
					$player->sendForm($this->editProducts($category));
					break;
				case 4:
					/** @var SubCategory $category */
					$category->clear();

					$category->getParent()->getCategories()->removeEntry($category);
					$this->save();
					break;
			}
		});
	}

	private function editInfoForm(EntryInfo $info, Form $back): CustomForm{
		return new CustomForm($info->getName(), [
			new Input("name", "Name", $info->getName(), $info->getName()),
			new Input("description", "Description", $info->getDescription(), $info->getDescription()),
			new Input("icon","Icon",$info->getIcon(), $info->getIcon())], function(Player $player, CustomFormResponse $response) use ($info, $back):void{
			$name = $response->getString("name");
			$description = $response->getString("description");
			$icon = $response->getString("icon");

			$info->setName($name);
			$info->setDescription($description);
			$info->setIcon($icon);

			$this->save();
			$player->sendForm($back);
		});
	}

	private function editProducts(Category $category): MenuForm{
		$options = [
			new MenuOption("Back"),
			new MenuOption("Add Product")
		];

		/** @var Product[] $products */
		$products = $category->getProducts()->getEntries();
		foreach($products as $product){
			$options[] = new MenuOption($product->getInfo()->getName(), self::getFormIcon($product->getInfo()->getIcon()));
		}

		return new MenuForm("Edit Products","",$options,function(Player $player, int $data) use ($category, $products): void{
			if($data === 0){
				$player->sendForm($this->editCategoryMenu($category));
				return;
			}
			$data--;
			if($data === 0){
				$product = new Product([]);
				$category->getProducts()->addEntry($product);
				$this->save();
				$player->sendForm($this->editProductForm($category, $product));
				return;
			}

			$data--;

			$product = $products[array_keys($products)[$data]];
			if($category->getProducts()->entryExists($product)){
				$player->sendForm($this->editProductForm($category, $product));
				return;
			}

			$player->sendForm($this->editProducts($category));
		});
	}

	private function editProductForm(Category $category, Product $product): MenuForm{
		return new MenuForm("Edit Product", "", [
			new MenuOption("Back"),
			new MenuOption("Edit Info"),
			new MenuOption("Edit MetaData"),
			new MenuOption("Delete")], function(Player $player, int $data) use ($category, $product):void{
			switch($data){
				case 0:
					$player->sendForm($this->editProducts($category));
					break;
				case 1:
					$player->sendForm($this->editInfoForm($product->getInfo(),$this->editProductForm($category,$product)));
					break;
				case 2:
					$player->sendForm($this->editProductMetaData($category,$product));
					break;
				case 3:
					$category->getProducts()->removeEntry($product);
					$player->sendForm($this->editProductForm($category, $product));
					break;
			}
		});
	}

	private function editProductMetaData(Category $category, Product $product): CustomForm{
		$states = StringToEnchantmentParser::getInstance()->getKnownAliases();

		if($product->getEnchantment() !== "") {
			$enchantment = array_search($product->getEnchantment(), $states, true);

			if(!is_int($enchantment)) {
				$enchantment = 0;
			}
		}else{
			$enchantment = 0;
		}

		$providers = array_keys(EconomyManager::getInstance()->getProviders());
		if($product->getEconomy() !== "") {
			$provider = array_search(strtolower($product->getEconomy()), $providers, true);
		}else{
			$provider = 0;
		}

		$types = ItemUtils::TYPES;

		if($product->getItemType() !== ""){
			$type = array_search(strtolower($product->getEconomy()), $types, true);
		}else{
			$type = 0;
		}

		return new CustomForm("Edit Product MetaData", [
			new Dropdown("enchantment", "Enchantment", $states, $enchantment),
			new Input("price", "Price", (string) $product->getPrice()),
			new Dropdown("economy", "Economy", $providers, $provider),
			new Input("minimum", "Minimum Level", (string) $product->getMinimumLevel()),
			new Input("maximum", "Maximum Level", (string) $product->getMaximumLevel()),
			new Dropdown("type", "Item Type", $types, $type)], function(Player $player, CustomFormResponse $response) use ($category, $product, $states, $providers):void{
			$enchantment = $states[$response->getInt("enchantment")];
			$price = (float) $response->getString("price");
			$economy = $providers[$response->getInt("economy")];
			$minimum = (int) $response->getString("minimum");
			$maximum = (int) $response->getString("maximum");
			$type = array_keys(ItemUtils::TYPES)[$response->getInt("type")];

			$product->setEnchantment($enchantment);
			$product->setPrice($price);
			$product->setEconomy($economy);
			$product->setMinimumLevel($minimum);
			$product->setMaximumLevel($maximum);
			$product->setItemType($type);
			$this->save();

			$player->sendForm($this->editProductForm($category, $product));
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

	private static function getFormIcon(string $iconLink): ?FormIcon{
		$icon = null;
		if($iconLink !== ""){
			if(filter_var($iconLink, FILTER_VALIDATE_URL)){
				$icon = new FormIcon($iconLink);
			}else{
				$icon = new FormIcon($iconLink,FormIcon::IMAGE_TYPE_PATH);
			}
		}

		return $icon;
	}
}