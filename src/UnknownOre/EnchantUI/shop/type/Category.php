<?php
declare(strict_types=1);

namespace UnknownOre\EnchantUI\shop\type;

use UnknownOre\EnchantUI\utils\Data;
use UnknownOre\EnchantUI\utils\EntriesHolder;
use UnknownOre\EnchantUI\utils\EntryInfo;

class Category implements Data{
	private const INFO = "Info";
	private const PRODUCTS = "Products";
	private const CATEGORIES = "Categories";

	private EntryInfo $info;
	private EntriesHolder $products, $categories;

	public function __construct(array $data){
		$this->load($data);
	}

	public function getInfo():EntryInfo{
		return $this->info;
	}

	public function getProducts():EntriesHolder{
		return $this->products;
	}

	public function getCategories():EntriesHolder{
		return $this->categories;
	}

	public function clear(): void{
		/** @var SubCategory[] $categories */
		$categories = $this->getCategories()->getEntries();

		foreach($categories as $category){
			$category->clear();
		}

		$this->categories->clear();
		$this->products->clear();
	}

	private function load(array $data): void{
		$this->info = new EntryInfo($data[self::INFO] ?? []);

		$this->products = new EntriesHolder();
		$this->categories = new EntriesHolder();

		$products = $data[self::PRODUCTS] ?? [];
		$categories = $data[self::CATEGORIES] ?? [];

		foreach($products as $product) {
			$this->products->addEntry(new Product($product));
		}

		foreach($categories as $category) {
			$this->categories->addEntry(new SubCategory($category, $this));
		}
	}

	public function __asArray(): array{
		return [
			self::INFO => $this->info->__asArray(),
			self::PRODUCTS => $this->products->__asArray(),
			self::CATEGORIES => $this->categories->__asArray()
		];
	}

}