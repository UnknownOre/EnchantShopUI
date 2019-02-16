<?php
namespace UnknownOre\EnchantUI;

use pocketmine\command\{ Command, PluginCommand, CommandSender};
use pocketmine\Player;

class ShopCommand extends PluginCommand {
    
    public function __construct(Main $plugin) {
        parent::__construct('enchantui', $plugin);
        $this->setAliases(['eshop','es']);
        $this->setDescription('Main Enchant command');
        $this->plugin = $plugin;
    }
	
    public function execute(CommandSender $sender, string $commandLabel, array $args): bool {
        if ($sender instanceof Player) {
            $this->plugin->ListForm($sender);
        }else{
            $sender->sendMessage("Please use this in-game.");
        }
		return true;
	}
    
}
