<?php

namespace UnknownOre\EnchantUI;

use pocketmine\{Server ,Player};
use pocketmine\item\{Item , enchantment\Enchantment , enchantment\EnchantmentInstance};

use pocketmine\utils\Config;
use pocketmine\plugin\PluginBase as PB;

use onebone\economyapi\EconomyAPI;
use jojoe77777\FormAPI\CustomForm;
use jojoe77777\FormAPI\ModalForm;
use jojoe77777\FormAPI\SimpleForm;
use pocketmine\utils\TextFormat as C;

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
    }
	
    public function ListForm(Player $player): void{
        $form = new SimpleForm(function (Player $player, $data = null){
            if ($data === null){
                $player->sendMessage(C::GREEN.'Thank You for using Enchant Shop');
            }else{
                $this->BuyForm($player, $data);
            }
        });
        $array = $this->shop->getAll();
		foreach ($array as $name => $content) {

			$form->addButton(C::DARK_AQUA.$content[0]." ".C::GREEN.$content[2]."$");
		}
        $form->setTitle(C::RED."Enchantment Shop");
        $player->sendForm($form);
    }
	
    public function BuyForm(Player $player, $id): void{
        $array = $this->shop->getAll();
        $price = $array[$id][2];
        $ide = $array[$id][1];
        $level = $array[$id][3];
        $name = $array[$id][0];
        $form = new CustomForm(function (Player $player, $data = null) use ($price, $ide, $name){
            $money = EconomyAPI::getInstance()->myMoney($player);
            $fprice = $price * $data[1];
            if ($data === null){
                $this->ListForm($player);
            }elseif($money > $fprice){
                EconomyAPI::getInstance()->reduceMoney($player,  $price * $data[1]);
                $item = $player->getInventory()->getItemInHand();
                $ench = Enchantment::getEnchantment($ide);
                $item->addEnchantment(new EnchantmentInstance($ench, (int) $data[1]));
                $player->getInventory()->setItemInHand($item);
                $message = C::GREEN."You have bought ". $name. " level ". $data[1]. " For ".C::YELLOW. $fprice.C::RED. "$";
                $player->sendMessage($message);
            }else{
                $player->sendMessage(C::RED.' You dont have enough Money!');
            }
        }
        );
        $form->addLabel(C::DARK_AQUA.'You will pay '.C::YELLOW. $price.'$'.C::DARK_AQUA.' per level');
        $form->setTitle(C::RED."Enchantment Shop");
        $form->addSlider("Level", 1, $level, 1, -1);
        $player->sendForm($form);
    }
	
}
