<?php
declare(strict_types=1);

namespace UnknownOre\EnchantUI\shop\type;

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

	public function getCategories():array{
		return $this->categories;
	}

	public function getProducts():array{
		return $this->products;
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