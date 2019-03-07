<?php

namespace UnknownOre\EnchantUI;

use pocketmine\{
    Server,
    Player
};
use pocketmine\item\{
    Item,
    enchantment\Enchantment,
    enchantment\EnchantmentInstance
};
use pocketmine\utils\Config;
use UnknownOre\EnchantUI\libs\jojoe77777\FormAPI\{
    CustomForm,
    SimpleForm
};
use pocketmine\plugin\PluginBase;
use onebone\economyapi\EconomyAPI;

class Main extends PluginBase{
    
    public function onEnable(): void{
        @mkdir($this->getDataFolder());
        if(!file_exists($this->getDataFolder() . "Shop.yml")) {
            file_put_contents($this->getDataFolder() . "Shop.yml",$this->getResource("Shop.yml"));
        }
        $this->saveDefaultConfig();
        $this->getServer()->getCommandMap()->register("enchantui", new Commands\ShopCommand($this));
        $this->shop = new Config($this->getDataFolder() . "Shop.yml", Config::YAML);
    }
	
    /**
    * @param Player $player
    */
    public function listForm(Player $player): void{
        $form = new SimpleForm(function (Player $player, $data = null){
            if ($data === null){
                $player->sendMessage($this->shop->getNested('messages.thanks'));
                return;
            }
            $this->buyForm($player, $data);
        });
		foreach ($this->shop->getNested('shop') as $name){
            $var = array(
            "NAME" => $name['name'],
            "PRICE" => $name['price']
            );
			$form->addButton($this->replace($this->shop->getNested('Button'), $var));
		}
        $form->setTitle($this->shop->getNested('Title'));
        $player->sendForm($form);
    }
    
	/**
    * @param Player $player
    * @param int $id
    */
    public function buyForm(Player $player,int $id): void{
        $array = $this->shop->getNested('shop');
        $form = new CustomForm(function (Player $player, $data = null) use ($array, $id){
            $var = array(
            "NAME" => $array[$id]['name'],
            "PRICE" => $array[$id]['price'] * $data[1],
            "LEVEL" => $data[1],
            "MONEY" => EconomyAPI::getInstance()->myMoney($player)
            );
            if ($data === null){
                $this->listForm($player);
                return;
            }
            if(EconomyAPI::getInstance()->myMoney($player) > $c = $array[$id]['price'] * $data[1]){
                EconomyAPI::getInstance()->reduceMoney($player, $c);
                $item = $player->getInventory()->getItemInHand();
                $item->addEnchantment(new EnchantmentInstance(Enchantment::getEnchantment($array[$id]['id']), (int) $data[1]));
                $player->getInventory()->setItemInHand($item);
                $player->sendMessage($this->replace($this->shop->getNested('messages.paid-success'), $var));
            }else{
                $player->sendMessage($this->replace($this->shop->getNested('messages.not-enough-money'), $var));
            }
        }
        );
        $form->addLabel($this->replace($this->shop->getNested('messages.label'),["PRICE" => $array[$id]['price']]));
        $form->setTitle($this->shop->getNested('Title'));
        $form->addSlider($this->shop->getNested('slider-title'), 1, $array[$id]['max-level'], 1, -1);
        $player->sendForm($form);
    }
	
    /**
    * @param string $message
    * @param array $keys
    *
    * @return string
    */
    public function replace($message, array $keys){
        foreach($keys as $word => $value){
            $message = str_replace("{".$word."}", $value, $message);
        }
        return $message;
    }
}
