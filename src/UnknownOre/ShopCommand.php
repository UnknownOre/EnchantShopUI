<?php
namespace UnknownOre;

use pocketmine\command\Command;
use pocketmine\command\PluginCommand;
use pocketmine\command\CommandSender;
use pocketmine\Player;
class ShopCommand extends PluginCommand {
    
    public function __construct(Main $plugin) {
        parent::__construct('enchantui', $plugin);
        $this->setAliases(['eshop','es']);
        $this->setDescription('Main Enchant command');
        $this->plugin = $plugin;
    }
    public function execute(CommandSender $sender, string $commandLabel, array $args): bool {
        $this->plugin->ListForm($sender);
		return true;
	}
    
}
