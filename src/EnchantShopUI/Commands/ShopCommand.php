<?php

namespace EnchantShopUI\Commands;

use pocketmine\command\{
    Command,
    CommandSender
};
use pocketmine\plugin\PluginOwned;
use pocketmine\player\Player;
use EnchantShopUI\Main;

class ShopCommand extends Command implements PluginOwned{
	
	public $plugin;
    
    /**
    * ShopCommand constructor.
    * @param Main $plugin
    */
    public function __construct(Main $plugin){
        $this->plugin = $plugin;
        parent::__construct('enchantui');
        $this->setAliases(['eshop','es']);
        $this->setDescription('Main Enchant command');
        $this->setPermission("eshop.command");
    }
    
   /**
    * @param CommandSender $sender
    * @param string $commandLabel
    * @param array $args
    *
    * @return bool
    */
    public function execute(CommandSender $sender, string $commandLabel, array $args): bool{
        if(!$sender->hasPermission("eshop.command")){
            $sender->sendMessage($this->plugin->shop->getNested('messages.no-perm'));
            return true;
        }
        if(!$sender instanceof Player){
            $sender->sendMessage("Please use this in-game.");
            return true;
        }   
        $this->plugin->listForm($sender);
        return true;
	}
   
   public function getOwningPlugin(): Main{
    return $this->plugin;
   }
}
