<?php
declare(strict_types=1);

namespace UnknownOre\EnchantUI\economy\type;

use Closure;
use pocketmine\player\Player;

abstract class EconomyProvider{

	public abstract function getName():string;

	public abstract function getBalance(Player $player, Closure $closure):void;

	public abstract function reduceBalance(Player $player, float $value):void;

	public abstract function format(float $amount): string;

}