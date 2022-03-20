<?php
declare(strict_types=1);

namespace UnknownOre\EnchantUI\economy\default;

use Closure;
use pocketmine\player\Player;
use UnknownOre\EnchantUI\economy\type\EconomyProvider;

class XPProvider extends EconomyProvider{

	public function getName():string{
		return "XP Levels";
	}

	public function getBalance(Player $player, Closure $closure):void{
		$closure($player->getXpManager()->getXpLevel());
	}

	public function reduceBalance(Player $player, float $value):void{
		$player->getXpManager()->setXpLevel($player->getXpManager()->getXpLevel() - $value);
	}
}