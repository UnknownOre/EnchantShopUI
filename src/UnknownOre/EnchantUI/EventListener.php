<?php
namespace UnknownOre\EnchantUI;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\Listener;
use pocketmine\Item;
use pocketmine\block\EnchantingTable;

/**
 * Class EventListener
 * @package UnknownOre\EnchantUI
 */
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
        if($ev->getAction() !== PlayerInteractEvent::RIGHT_CLICK_BLOCK) return;
        $table = $this->plugin->shop->getNested('enchanting-table');
        if($table and $ev->getBlock() instanceof EnchantingTable){
            $ev->setCancelled();
            $this->plugin->ListForm($ev->getPlayer());
        }
    }
}
