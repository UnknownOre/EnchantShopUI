<?php
declare(strict_types=1);

namespace UnknownOre\EnchantUI\shop\type;

class SubCategory extends Category{

	public function __construct(array $data, private Category $parent){
		parent::__construct($data);
	}

	public function getParent():Category{
		return $this->parent;
	}
}