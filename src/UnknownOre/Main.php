<?php

namespace UnknownOre;

use pocketmine\{Server ,Player};
use pocketmine\item\{Item , enchantment\Enchantment , enchantment\EnchantmentInstance};

use pocketmine\utils\Config;
use pocketmine\plugin\PluginBase as PB;

use onebone\economyapi\EconomyAPI;
use joe777777\FormAPI;

class Main extends PB {
    public function onEnable(): void{
        $this->getLogger()->info("EnchantShop has been enabled!");
        if(!file_exists($this->getDataFolder() . "Shop.yml")) {
            @mkdir($this->getDataFolder());
            file_put_contents($this->getDataFolder() . "Shop.yml",$this->getResource("Shop.yml"));
        }
        $this->saveDefaultConfig();
        $this->getServer()->getCommandMap()->register("enchantui", new ShopCommand($this));
        $this->shop = new Config($this->getDataFolder() . "Shop.yml", Config::YAML);
        $this->fapi = Server::getInstance()->getPluginManager()->getPlugin("FormAPI");
    }
    public function ListForm(Player $player): void{
        $form = $this->fapi->createSimpleForm(function (Player $player, $data = null){
            if ($data === null) {
                
            }else{
                $this->BuyForm($player, $data);
            }
        });
        $array = $this->shop->getAll();
		foreach ($array as $name => $content) {

			$form->addButton($content[0]." ".$content[2]."$");
		}
        $form->setTitle("§cEnchantment §aShop");
        $form->sendToPlayer($player);
    }
    public function BuyForm(Player $player, $id): void{
        $array = $this->shop->getAll();
      
        $price = $array[$id][2];
        $name = $array[$id][0];
        $ide = $array[$id][1];
        $level = $array[$id][3];
      
        $form = $this->fapi->createCustomForm(function (Player $player, $data = null) use ($price, $ide, $name){
            $money = EconomyAPI::getInstance()->myMoney($player);
            if($data === null){
                $this->ListForm($player);
            }elseif($money > $price * $data[0]){
                EconomyAPI::getInstance()->reduceMoney($player,  $price);
                $item = $player->getInventory()->getItemInHand();
                $ench = Enchantment::getEnchantment($ide);
                $item->addEnchantment(new EnchantmentInstance($ench, (int) $data[0]));
                $player->getInventory()->setItemInHand($item);
                $player->sendMessage('§ayou have bought '.$name.' level ' .$data[0]. 'for '.$price * $data[0]."$");
            }else{
                $player->sendMessage('§cyou dont have enough Money');
            }
        }
        );
        $form->setTitle("§eBuy enchantment");
        $form->addLabel("§aEnchantment:§c ".$name."\n§aYou will pay §e".$price."$§a per level");
        $form->addSlider("Level", 1, $level, 1, -1);
        $form->sendToPlayer($player);
    }
}
