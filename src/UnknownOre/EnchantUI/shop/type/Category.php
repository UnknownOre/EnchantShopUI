<?php
declare(strict_types=1);

namespace UnknownOre\EnchantUI\shop\type;

class Category{

	private const
		PRODUCTS = "products", CATEGORIES = "categories";

	/** @var SubCategory[] */
	private array $categories = [];
	/** @var Product[] */
	private array $products = [];

	public function __construct(array $data){
		$this->load($data);
	}

	private function load(array $data):void{
		$products = $data[self::PRODUCTS] ?? [];
		foreach($products as $product) {
			if(Product::valid($product)) {
				$this->products[] = new Product($product);
			}
		}

		$categories = $data[self::CATEGORIES] ?? [];
		foreach($categories as $category) {
			$category = new SubCategory($category, $this);
			$this->categories[] = $category;
		}
	}

	public function getForm(){
		foreach($this->categories as $category){

		}

		foreach($this->products as $product){

		}


	}

}