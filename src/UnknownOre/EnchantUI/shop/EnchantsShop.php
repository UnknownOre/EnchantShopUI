<?php
declare(strict_types=1);

namespace UnknownOre\EnchantUI\shop;

use Exception;
use pocketmine\form\Form;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\enchantment\StringToEnchantmentParser;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\utils\Config;
use UnknownOre\EnchantUI\economy\EconomyManager;
use UnknownOre\EnchantUI\EnchantUI;
use UnknownOre\EnchantUI\language\ShopTranslations;
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

		$language = $player->getLocale();

		$options[] = $category instanceof SubCategory ? ShopTranslations::form_button_previous($language) : new MenuOption(ShopTranslations::form_button_exit($player->getLocale()));
		$player->hasPermission("eshop.admin") && $options[] = new MenuOption(ShopTranslations::form_button_edit($language));

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
					$player->sendForm($this->editCategoryMenu($player, $category));
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
				$player->sendForm($this->getProductForm($player, $product, $category));
			}
		});
	}

	private function getProductForm(Player $player, Product $product, Category $parent):CustomForm{
		$options = [];

		$info = $product->getInfo();

		$name = $info->getName();
		$description = $info->getDescription();

		if($description !== "") {
			$options[] = new Label("description", $description);
		}

		$options[] = new Slider("level", ShopTranslations::form_element_level($player->getLocale()), $product->getMinimumLevel(), $product->getMaximumLevel());

		return new CustomForm($name, $options, function(Player $player, CustomFormResponse $response) use ($product, $parent):void{
			$level = (int) $response->getFloat("level");

			$economy = EconomyManager::getInstance()->getProviderByName($product->getEconomy());

			$economy->getBalance($player, function(float $amount) use ($product, $player, $level):void{
				if(!$player->isOnline()) {
					//there's a chance the player would leave before the provider receives the player balance (async)
					return;
				}

				if($amount < $product->getPrice() * $level) {
					return;
				}

				$item = $player->getInventory()->getItemInHand();
				if($item->isNull()) {
					return;
				}

				if(!ItemUtils::isItemCompatible($item, $product->getItemType())) {
					return;
				}

				$incompatible = $product->getInCompatibleEnchantments();

				$restricted = [];

				foreach($incompatible as $id) {
					if($item->hasEnchantment($id)) {
						$restricted[] = $id;
					}
				}

				if($restricted !== []) {
					return;
				}

				$item->addEnchantment(new EnchantmentInstance(StringToEnchantmentParser::getInstance()->parse($product->getEnchantment()), $level));
				$player->getInventory()->setItemInHand($item);
			});

		}, function(Player $player) use ($parent):void{
			$player->sendForm($this->getCategoryForm($player, $parent));
		});
	}

	private function editCategoryMenu(Player $player, Category $category):MenuForm{
		$language = $player->getLocale();

		$options = [
			new MenuOption(ShopTranslations::form_button_previous($language)),
			new MenuOption(ShopTranslations::form_button_edit_info($language)),
			new MenuOption(ShopTranslations::form_button_edit($language)),
			new MenuOption(ShopTranslations::form_button_delete($language)),];

		if($category instanceof SubCategory) {
			$options[] = new MenuOption("Delete");
		}

		return new MenuForm(ShopTranslations::form_title_edit_category($language), "", $options, function(Player $player, int $data) use ($category):void{
			switch($data){
				case 0:
					$player->sendForm($this->getCategoryForm($player, $category));
					break;
				case 1:
					$player->sendForm($this->editInfoForm($player,$category->getInfo(), $this->editCategoryMenu($player, $category)));
					break;
				case 2:
					$subCategory = new SubCategory([], $category);

					$category->getCategories()->addEntry($subCategory);
					$player->sendForm($this->editCategoryMenu($player, $subCategory));
					$this->save();
					break;
				case 3:
					$player->sendForm($this->editProducts($player, $category));
					break;
				case 4:
					/** @var SubCategory $category */ $category->clear();

					$category->getParent()->getCategories()->removeEntry($category);
					$this->save();
					break;
			}
		});
	}

	private function editInfoForm(Player $player,EntryInfo $info, Form $back):CustomForm{
		$language = $player->getLocale();

		return new CustomForm($info->getName(), [
			new Input("name", ShopTranslations::form_element_name($language), $info->getName(), $info->getName()),
			new Input("description", ShopTranslations::form_element_description($language), $info->getDescription(), $info->getDescription()),
			new Input("icon", ShopTranslations::form_element_icon($language), $info->getIcon(), $info->getIcon())], function(Player $player, CustomFormResponse $response) use ($info, $back):void{
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

	private function editProducts(Player $player, Category $category):MenuForm{
		$language = $player->getLocale();

		$options = [
			new MenuOption(ShopTranslations::form_button_previous($language)),
			new MenuOption(ShopTranslations::form_button_add_product($language))];

		/** @var Product[] $products */
		$products = $category->getProducts()->getEntries();
		foreach($products as $product) {
			$options[] = new MenuOption($product->getInfo()->getName(), self::getFormIcon($product->getInfo()->getIcon()));
		}

		return new MenuForm(ShopTranslations::form_title_edit_products($language), "", $options, function(Player $player, int $data) use ($category, $products):void{
			if($data === 0) {
				$player->sendForm($this->editCategoryMenu($player, $category));
				return;
			}
			$data--;
			if($data === 0) {
				$product = new Product([]);
				$category->getProducts()->addEntry($product);
				$this->save();
				$player->sendForm($this->editProductForm($player, $category, $product));
				return;
			}

			$data--;

			$product = $products[array_keys($products)[$data]];
			if($category->getProducts()->entryExists($product)) {
				$player->sendForm($this->editProductForm($player, $category, $product));
				return;
			}


			$player->sendForm($this->editProducts($player, $category));
		});
	}

	private function editProductForm(Player $player, Category $category, Product $product):MenuForm{
		$language = $player->getLocale();

		return new MenuForm(ShopTranslations::form_title_edit_product($language), "", [
			new MenuOption(ShopTranslations::form_button_previous($language)),
			new MenuOption(ShopTranslations::form_button_edit_info($language)),
			new MenuOption(ShopTranslations::form_button_edit_metadata($language)),
			new MenuOption(ShopTranslations::form_button_delete($language))], function(Player $player, int $data) use ($category, $product):void{
			switch($data){
				case 0:
					$player->sendForm($this->editProducts($player, $category));
					break;
				case 1:
					$player->sendForm($this->editInfoForm($player, $product->getInfo(), $this->editProductForm($player, $category, $product)));
					break;
				case 2:
					$player->sendForm($this->editProductMetaData($player, $category, $product));
					break;
				case 3:
					$category->getProducts()->removeEntry($product);
					$player->sendForm($this->editProducts($player, $category));
					break;
			}
		});
	}

	private function editProductMetaData(Player $player, Category $category, Product $product):CustomForm{
		$language = $player->getLocale();

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

		if($product->getItemType() !== "") {
			$type = array_search(strtolower($product->getEconomy()), $types, true);
		}else{
			$type = 0;
		}

		return new CustomForm(ShopTranslations::form_button_edit_metadata($language), [
			new Dropdown("enchantment", ShopTranslations::form_element_enchantment($language), $states, $enchantment),
			new Input("price", ShopTranslations::form_element_price($language), (string) $product->getPrice()),
			new Dropdown("economy", ShopTranslations::form_element_economy($language), $providers, $provider),
			new Input("minimum", ShopTranslations::form_element_level_min($language), (string) $product->getMinimumLevel()),
			new Input("maximum", ShopTranslations::form_element_level_max($language), (string) $product->getMaximumLevel()),
			new Dropdown("type", ShopTranslations::form_element_item_type($language), $types, $type)], function(Player $player, CustomFormResponse $response) use ($category, $product, $states, $providers):void{
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

			$player->sendForm($this->editProductForm($player, $category, $product));
		});
	}


	private function save():void{
		$this->config->setAll($this->root->__asArray());
		try{
			$this->config->save();
		}catch(Exception $exception){
			$lang = Server::getInstance()->getLanguage()->getLang();

			$this->plugin->getLogger()->error(ShopTranslations::message_error_save_failed($lang, $exception->getMessage()));
		}
	}

	private static function getFormIcon(string $iconLink):?FormIcon{
		$icon = null;
		if($iconLink !== "") {
			if(filter_var($iconLink, FILTER_VALIDATE_URL)) {
				$icon = new FormIcon($iconLink);
			}else{
				$icon = new FormIcon($iconLink, FormIcon::IMAGE_TYPE_PATH);
			}
		}

		return $icon;
	}
}