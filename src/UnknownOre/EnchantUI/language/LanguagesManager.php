<?php
declare(strict_types=1);

namespace UnknownOre\EnchantUI\language;

use pocketmine\lang\Language;
use UnknownOre\EnchantUI\EnchantUI;
use function str_contains;
use const DIRECTORY_SEPARATOR;

class LanguagesManager{

	public const FALLBACK_LANGUAGE = "en_US";

	private static self $instance;
	private array $languages = [];

	public function __construct(private EnchantUI $plugin){
		self::$instance = $this;
		$this->load();
	}

	public static function getInstance(): self{
		return self::$instance;
	}

	public function getLanguage(string $code): Language{
		if(isset($this->languages[$code])){
			return $this->languages[$code];
		}

		return $this->languages["eng"];
	}

	private function load(): void{
		foreach($this->plugin->getResources() as $key => $resource) {
			if(str_contains($key, "languages")) {
				$this->plugin->saveResource($key);
			}
		}

		$dir = $this->plugin->getDataFolder() . "languages" . DIRECTORY_SEPARATOR;
		$languages = Language::getLanguageList($dir);

		foreach($languages as $code => $language) {
			$this->languages[$code] = new Language($code, $dir, self::FALLBACK_LANGUAGE);
		}
	}

}