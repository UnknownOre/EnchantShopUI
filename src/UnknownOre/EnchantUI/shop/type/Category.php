<?php
declare(strict_types=1);

namespace UnknownOre\EnchantUI\shop\type;

use pocketmine\player\Player;
use UnknownOre\EnchantUI\economy\EconomyManager;
use UnknownOre\EnchantUI\libs\dktapps\pmforms\MenuForm;
use UnknownOre\EnchantUI\libs\dktapps\pmforms\MenuOption;
use pocketmine\utils\TextFormat as C;
use function count;

class Category{

	private const
		NAME = "name", DESCRIPTION = "description", PRODUCTS = "products", CATEGORIES = "categories";

	private string $name, $description;

	/** @var SubCategory[] */
	private array $categories = [];
	/** @var Product[] */
	private array $products = [];

	private int $lastCategoryId = 0, $lastProductId = 0;


	public function __construct(array $data){
		$this->load($data);
	}

	public function getName():string{
		return $this->name;
	}

	public function getDescription():string{
		return $this->description;
	}

	public function getForm():MenuForm{
		$options = [];

		if($this instanceof SubCategory) {
			$options[] = new MenuOption(C::RED . "Back");
		}else{
			$options[] = new MenuOption(C::RED . "Close");
		}

		$categories = $this->categories;
		foreach($categories as $category) {
			$options[] = new MenuOption($category->getName());
		}

		$economyManager = EconomyManager::getInstance();
		$products = $this->products;

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
				$player->sendForm($category->getForm());
				return;
			}

			$button -= (count($categories) - 1);

			$product = $products[$button];
		});
	}

	private function load(array $data):void{
		$this->name = $data[self::NAME] ?? "";
		$this->description = $data[self::DESCRIPTION] ?? "";

		$products = $data[self::PRODUCTS] ?? [];
		foreach($products as $product) {
			if(Product::valid($product)) {
				$this->products[] = new Product($product);
			}
		}
		$this->lastProductId = count($this->categories);

		$categories = $data[self::CATEGORIES] ?? [];
		foreach($categories as $category) {
			$category = new SubCategory($category, $this);
			$this->categories[] = $category;
		}
		$this->lastCategoryId = count($this->categories);
	}

	public function asArray():array{
		$products = [];
		foreach($this->products as $product) {
			$products[] = $product->asArray();
		}

		$categories = [];
		foreach($this->categories as $category) {
			$categories[] = $category->asArray();
		}

		return [
			self::NAME => $this->name,
			self::DESCRIPTION => $this->description,
			self::CATEGORIES => $categories,
			self::PRODUCTS => $products];
	}

}