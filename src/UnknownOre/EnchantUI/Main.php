<?php

namespace UnknownOre\EnchantUI;

use pocketmine\{
    Server,
    Player
};
use pocketmine\item\{
    Item,
    Tool,
    Armor,
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
use DaPigGuy\PiggyCustomEnchants\CustomEnchants\CustomEnchants;

class Main extends PluginBase{
    
    public function onEnable(): void{
        if (is_null($this->getServer()->getPluginManager()->getPlugin("EconomyAPI"))) {
            $this->getLogger()->error("in order to use EnchantUI you need to install EconomyAPI.");
            $this->getServer()->getPluginManager()->disablePlugin($this);
            return;
        }
        @mkdir($this->getDataFolder());
        $this->shop = new Config($this->getDataFolder() . "Shop.yml", Config::YAML);
        $this->UpdateConfig();
        $this->saveDefaultConfig();
        $this->getLogger()->notice("EnchantShopUI has been enabled.");
        $this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);
        $this->getServer()->getCommandMap()->register("enchantui", new Commands\ShopCommand($this));
        $this->piggyCE = $this->getServer()->getPluginManager()->getPlugin("PiggyCustomEnchants");
    }
    
    public function UpdateConfig(): void{
        if(is_null($this->shop->getNested('version'))){
            file_put_contents($this->getDataFolder() . "Shop.yml",$this->getResource("Shop.yml"));
            $this->getLogger()->notice("Updating plugin config.....");
            return;
        }
        if($this->shop->getNested('version') != '0.5'){
            $shop = $this->shop->getNested('shop');
            $this->getLogger()->notice("Updating plugin config to version 0.5");
            foreach($shop as $list => $data){
                $data["incompatible"] = [];
                $shop[$list] = $data;
            }
            $this->shop->set('shop', $shop);
            $this->shop->save();
            return;
        }
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
		foreach($this->shop->getNested('shop') as $name){
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
            "MONEY" => EconomyAPI::getInstance()->myMoney($player),
            "INCOMPATIBLE" => $incompatible = $this->isCompatible($player, $array[$id]['incompatible-enchantments'])
            );
            if ($data === null){
                $this->listForm($player);
                return;
            }
            if(!$player->getInventory()->getItemInHand() instanceof Tool and !$player->getInventory()->getItemInHand() instanceof Armor){
                $player->sendMessage($this->shop->getNested('messages.hold-item'));
                return;
            }
            if(!is_null($incompatible)){
                $player->sendMessage($this->replace($this->shop->getNested('messages.incompatible-enchantment'), $var));
                return;
            }
            if(EconomyAPI::getInstance()->myMoney($player) > $c = $array[$id]['price'] * $data[1]){
                EconomyAPI::getInstance()->reduceMoney($player, $c);
                $this->enchantItem($player, $data[1], $array[$id]['enchantment']); 
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
    * @param Player $Item
    * @param int $level
    * @param int|String $enchantment
    */
	public function enchantItem(Player $player, int $level, $enchantment): void{
        $item = $player->getInventory()->getItemInHand();
        if(is_string($enchantment)){
            $ench = Enchantment::getEnchantmentByName((string) $enchantment);
            if($this->piggyCE !== null && $ench === null){
                $ench = CustomEnchants::getEnchantmentByName((string) $enchantment);
            }
            if($this->piggyCE !== null && $ench instanceof CustomEnchants){
                $this->piggyCE->addEnchantment($item, $ench->getName(), (int) $level);
            }else{
                $item->addEnchantment(new EnchantmentInstance($ench, (int) $level));
            }
        }
        if(is_int($enchantment)){
            $ench = Enchantment::getEnchantment($enchantment);
            $item->addEnchantment(new EnchantmentInstance($ench, (int) $level));
        }
        $player->getInventory()->setItemInHand($item);
    }
    
    /**
    * @param Player $player
    * @param array $array
    *
    * @return int|mixed|null
    */
    public function isCompatible(Player $player,array $array){
        $item = $player->getInventory()->getItemInHand();
        foreach($array as $enchantment){
            if($item->hasEnchantment($enchantment)){
                $id = $enchantment;
                return $id;
            }
        }
    }
    
   /**
    * @param string $message
    * @param array $keys
    *
    * @return string
    */
    public function replace($message, array $keys): string{
        foreach($keys as $word => $value){
            $message = str_replace("{".$word."}", $value, $message);
        }
        return $message;
    }
}
