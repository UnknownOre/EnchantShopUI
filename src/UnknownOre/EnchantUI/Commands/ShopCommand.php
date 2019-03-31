<?php
namespace UnknownOre\EnchantUI\Commands;
use pocketmine\command\{
    Command,
    PluginCommand,
    CommandSender
};
use pocketmine\Player;
use UnknownOre\EnchantUI\Main;

/**
 * Class ShopCommand
 * @package UnknownOre\EnchantUI\Commands
 */
class ShopCommand extends PluginCommand{
    
    /**
    * ShopCommand constructor.
    * @param Main $plugin
    */
    public function __construct(Main $plugin){
        parent::__construct('enchantui', $plugin);
        $this->setAliases(['eshop','es']);
        $this->setDescription('Main Enchant command');
        $this->setPermission("eshop.command");
        $this->plugin = $plugin;
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
   
}
