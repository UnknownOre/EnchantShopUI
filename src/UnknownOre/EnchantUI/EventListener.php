<?php
namespace UnknownOre\EnchantUI;

use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\Listener;
use pocketmine\Item;
use pocketmine\block\EnchantingTable;
Class EventListener implements Listener{
    
    /** @var EnchantUI */
    private $plugin;
    
    /**
     * EventListener constructor.
     * @param EnchantUI $plugin
     */
	public function __construct(Main $plugin){
		$this->plugin = $plugin;
	}
    
    /**
	 * @param PlayerInteractEvent $ev
	 */
    public function onInteract(PlayerInteractEvent $ev){
        $block = $ev->getBlock();
        $table = $this->plugin->shop->getNested('enchantment-table');
        if($table and $block instanceof EnchantingTable){
            $ev->setCancelled();
            $this->plugin->ListForm($ev->getPlayer());
        }
    }
}
