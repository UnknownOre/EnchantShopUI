<?php
declare(strict_types=1);

namespace UnknownOre\EnchantUI\economy;

use UnknownOre\EnchantUI\economy\default\XPProvider;
use UnknownOre\EnchantUI\economy\type\EconomyProvider;
use function strcasecmp;
use function strtolower;

class EconomyManager{

	private static self $instance;

	private array $providers = [];

	public function __construct(){
		self::$instance = $this;

		$this->register(new XPProvider());
	}

	public static function getInstance():self{
		return self::$instance;
	}

	public function getProviders():array{
		return $this->providers;
	}

	public function register(EconomyProvider $provider):void{
		$this->providers[strtolower($provider->getName())] = $provider;
	}

	public function getProviderByName(string $name):?EconomyProvider{
		return $this->providers[strtolower($name)] ?? null;
	}

}