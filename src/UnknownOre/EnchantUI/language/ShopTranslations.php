<?php

namespace UnknownOre\EnchantUI\language;

use pocketmine\lang\Language;

final class ShopTranslations{

	public static function getLanguage(string $language):Language{
		return LanguagesManager::getInstance()->getLanguage($language);
	}

	public static function language_name(string $language):string{
		return self::getLanguage($language)->translateString('language.name', []);
	}

	public static function form_button_exit(string $language):string{
		return self::getLanguage($language)->translateString('form.button.exit', []);
	}

	public static function form_button_previous(string $language):string{
		return self::getLanguage($language)->translateString('form.button.previous', []);
	}

	public static function form_button_edit(string $language):string{
		return self::getLanguage($language)->translateString('form.button.edit', []);
	}

	public static function form_button_edit_info(string $language):string{
		return self::getLanguage($language)->translateString('form.button.edit.info', []);
	}

	public static function form_button_edit_products(string $language):string{
		return self::getLanguage($language)->translateString('form.button.edit.products', []);
	}

	public static function form_button_edit_metadata(string $language):string{
		return self::getLanguage($language)->translateString('form.button.edit.metadata', []);
	}

	public static function form_button_add_category(string $language):string{
		return self::getLanguage($language)->translateString('form.button.add.category', []);
	}

	public static function form_button_add_product(string $language):string{
		return self::getLanguage($language)->translateString('form.button.add.product', []);
	}

	public static function form_button_delete(string $language):string{
		return self::getLanguage($language)->translateString('form.button.delete', []);
	}

	public static function form_title_edit_category(string $language):string{
		return self::getLanguage($language)->translateString('form.title.edit.category', []);
	}

	public static function form_title_edit_products(string $language):string{
		return self::getLanguage($language)->translateString('form.title.edit.products', []);
	}

	public static function form_title_edit_product(string $language):string{
		return self::getLanguage($language)->translateString('form.title.edit.product', []);
	}

	public static function form_element_name(string $language):string{
		return self::getLanguage($language)->translateString('form.element.name', []);
	}

	public static function form_element_description(string $language):string{
		return self::getLanguage($language)->translateString('form.element.description', []);
	}

	public static function form_element_icon(string $language):string{
		return self::getLanguage($language)->translateString('form.element.icon', []);
	}

	public static function form_element_enchantment(string $language):string{
		return self::getLanguage($language)->translateString('form.element.enchantment', []);
	}

	public static function form_element_price(string $language):string{
		return self::getLanguage($language)->translateString('form.element.price', []);
	}

	public static function form_element_level(string $language):string{
		return self::getLanguage($language)->translateString('form.element.level', []);
	}

	public static function form_element_economy(string $language):string{
		return self::getLanguage($language)->translateString('form.element.economy', []);
	}

	public static function form_element_level_min(string $language):string{
		return self::getLanguage($language)->translateString('form.element.level.min', []);
	}

	public static function form_element_level_max(string $language):string{
		return self::getLanguage($language)->translateString('form.element.level.max', []);
	}

	public static function form_element_item_type(string $language):string{
		return self::getLanguage($language)->translateString('form.element.item.type', []);
	}

	public static function message_error_save_failed(string $language, string $param0):string{
		return self::getLanguage($language)->translateString('message.error.save.failed', [0 => $param0,]);
	}
}