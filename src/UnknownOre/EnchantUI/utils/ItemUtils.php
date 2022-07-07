<?php
declare(strict_types=1);

namespace UnknownOre\EnchantUI\utils;

use pocketmine\inventory\ArmorInventory;
use pocketmine\item\Armor;
use pocketmine\item\Axe;
use pocketmine\item\Hoe;
use pocketmine\item\Item;
use pocketmine\item\Pickaxe;
use pocketmine\item\Sword;
use pocketmine\item\Tool;

class ItemUtils{

	const TYPE_ANY = "any";

	const TYPE_TOOL = "tool";
	const TYPE_SWORD = "sword";
	const TYPE_HOE = "hoe";
	const TYPE_PICKAXE = "pickaxe";
	const TYPE_AXE = "axe";

	const TYPE_ARMOR = "armor";
	const TYPE_HELMET = "helmet";
	const TYPE_CHESTPLATE = "chestplate";
	const TYPE_LEGGINGS = "leggings";
	const TYPE_BOOTS = "boots";

	const TYPES = [
		self::TYPE_ANY,
		self::TYPE_TOOL,
		self::TYPE_SWORD ,
		self::TYPE_HOE ,
		self::TYPE_PICKAXE,
		self::TYPE_AXE ,
		self::TYPE_ARMOR ,
		self::TYPE_HELMET ,
		self::TYPE_CHESTPLATE ,
		self::TYPE_LEGGINGS ,
		self::TYPE_BOOTS
	];

	public static function isItemCompatible(Item $item, string $type): bool{
		return match ($type){
			default => true,
			self::TYPE_TOOL => $item instanceof Tool,
			self::TYPE_SWORD => $item instanceof Sword,
			self::TYPE_HOE => $item instanceof Hoe,
			self::TYPE_PICKAXE => $item instanceof Pickaxe,
			self::TYPE_AXE => $item instanceof Axe,
			self::TYPE_ARMOR => $item instanceof Armor,
			self::TYPE_HELMET => $item instanceof Armor && $item->getArmorSlot() === ArmorInventory::SLOT_HEAD,
			self::TYPE_CHESTPLATE => $item instanceof Armor && $item->getArmorSlot() === ArmorInventory::SLOT_CHEST,
			self::TYPE_LEGGINGS => $item instanceof Armor && $item->getArmorSlot() === ArmorInventory::SLOT_LEGS,
			self::TYPE_BOOTS => $item instanceof Armor && $item->getArmorSlot() === ArmorInventory::SLOT_FEET,
		};
	}

}