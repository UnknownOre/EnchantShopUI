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

	private bool $deleted = false;

	public function __construct(array $data){
		$this->load($data);
	}

	public function getName():string{
		return $this->name;
	}

	public function setName(string $name): void{
		$this->name = $name;
	}

	public function getDescription():string{
		return $this->description;
	}

	public function setDescription(string $description): void{
		$this->description = $description;
	}

	public function getCategories():array{
		return $this->categories;
	}

	public function addCategory(Category $category): void{
		$this->categories[$this->lastCategoryId] = $category;
		$this->lastCategoryId++;
	}

	public function removeCategory(Category $category): void{
		foreach($this->categories as $key => $target){
			if($target === $category){
				unset($this->categories[$key]);
			}
		}
	}

	public function getProducts():array{
		return $this->products;
	}

	public function addProduct(Product $product): void{
		$this->products[$this->lastProductId] = $product;
		$this->lastProductId++;
	}

	public function removeProduct(Product $product): void{
		foreach($this->products as $key => $target){
			if($target === $product){
				unset($this->products[$key]);
			}
		}
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

	public function exists(): bool{
		return !$this->deleted;
	}

	public function delete(): void{
		foreach($this->categories as $category){
			$category->delete();
		}

		$this->categories = [];
		$this->products = [];
		$this->deleted = true;
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