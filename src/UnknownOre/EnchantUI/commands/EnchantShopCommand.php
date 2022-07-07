<?php
declare(strict_types=1);

namespace UnknownOre\EnchantUI\commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\Plugin;
use pocketmine\plugin\PluginOwned;
use UnknownOre\EnchantUI\EnchantUI;

class EnchantShopCommand extends Command implements PluginOwned{

	public function __construct(private EnchantUI $plugin){
		parent::__construct("eshop", "Opens Enchantment Shop menu.", "", [
			"enchantui",
			"enchantshop"]);
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args){
		if(!$sender instanceof Player){
			//todo:
			return;
		}

		$this->plugin->getShop()->send($sender);
	}

	public function getOwningPlugin():Plugin{
		return $this->plugin;
	}
}