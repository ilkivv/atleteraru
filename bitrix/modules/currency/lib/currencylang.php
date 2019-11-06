<?php
namespace Bitrix\Currency;

use Bitrix\Main\Entity;
use Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

/**
 * Class CurrencyLangTable
 *
 * Fields:
 * <ul>
 * <li> CURRENCY string(3) mandatory
 * <li> LID string(2) mandatory
 * <li> FORMAT_STRING string(50) mandatory
 * <li> FULL_NAME string(50) optional
 * <li> DEC_POINT string(5) optional default '.'
 * <li> THOUSANDS_SEP string(5) optional default ' '
 * <li> DECIMALS int optional default 2
 * <li> THOUSANDS_VARIANT string(1) optional
 * <li> HIDE_ZERO bool optional default 'N'
 * </ul>
 *
 * @package Bitrix\Currency
 **/

class CurrencyLangTable extends Entity\DataManager
{
	/**
	 * Returns path to the file which contains definition of the class.
	 *
	 * @return string
	 */
	public static function getFilePath()
	{
		return __FILE__;
	}

	/**
	 * Returns DB table name for entity
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_catalog_currency_lang';
	}

	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 */
	public static function getMap()
	{
		return array(
			'CURRENCY' => array(
				'data_type' => 'string',
				'primary' => true,
				'validation' => array(__CLASS__, 'validateCurrency'),
				'title' => Loc::getMessage('CURRENCY_LANG_ENTITY_CURRENCY_FIELD'),
			),
			'LID' => array(
				'data_type' => 'string',
				'primary' => true,
				'validation' => array(__CLASS__, 'validateLid'),
				'title' => Loc::getMessage('CURRENCY_LANG_ENTITY_LID_FIELD'),
			),
			'FORMAT_STRING' => array(
				'data_type' => 'string',
				'required' => true,
				'validation' => array(__CLASS__, 'validateFormatString'),
				'title' => Loc::getMessage('CURRENCY_LANG_ENTITY_FORMAT_STRING_FIELD'),
			),
			'FULL_NAME' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateFullName'),
				'title' => Loc::getMessage('CURRENCY_LANG_ENTITY_FULL_NAME_FIELD'),
			),
			'DEC_POINT' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateDecPoint'),
				'title' => Loc::getMessage('CURRENCY_LANG_ENTITY_DEC_POINT_FIELD'),
			),
			'THOUSANDS_SEP' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateThousandsSep'),
				'title' => Loc::getMessage('CURRENCY_LANG_ENTITY_THOUSANDS_SEP_FIELD'),
			),
			'DECIMALS' => array(
				'data_type' => 'integer',
				'title' => Loc::getMessage('CURRENCY_LANG_ENTITY_DECIMALS_FIELD'),
			),
			'THOUSANDS_VARIANT' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateThousandsVariant'),
				'title' => Loc::getMessage('CURRENCY_LANG_ENTITY_THOUSANDS_VARIANT_FIELD'),
			),
			'HIDE_ZERO' => array(
				'data_type' => 'boolean',
				'values' => array('N', 'Y'),
				'title' => Loc::getMessage('CURRENCY_LANG_ENTITY_HIDE_ZERO_FIELD'),
			),
			'LANGUAGE' => array(
				'data_type' => 'Bitrix\Main\Localization\Language',
				'reference' => array('=this.LID' => 'ref.LID'),
			),
		);
	}

	/**
	 * Returns validators for CURRENCY field.
	 *
	 * @return array
	 */
	public static function validateCurrency()
	{
		return array(
			new Entity\Validator\Length(null, 3),
		);
	}

	/**
	 * Returns validators for LID field.
	 *
	 * @return array
	 */
	public static function validateLid()
	{
		return array(
			new Entity\Validator\Length(null, 2),
		);
	}

	/**
	 * Returns validators for FORMAT_STRING field.
	 *
	 * @return array
	 */
	public static function validateFormatString()
	{
		return array(
			new Entity\Validator\Length(null, 50),
		);
	}

	/**
	 * Returns validators for FULL_NAME field.
	 *
	 * @return array
	 */
	public static function validateFullName()
	{
		return array(
			new Entity\Validator\Length(null, 50),
		);
	}

	/**
	 * Returns validators for DEC_POINT field.
	 *
	 * @return array
	 */
	public static function validateDecPoint()
	{
		return array(
			new Entity\Validator\Length(null, 5),
		);
	}

	/**
	 * Returns validators for THOUSANDS_SEP field.
	 *
	 * @return array
	 */
	public static function validateThousandsSep()
	{
		return array(
			new Entity\Validator\Length(null, 5),
		);
	}

	/**
	 * Returns validators for THOUSANDS_VARIANT field.
	 *
	 * @return array
	 */
	public static function validateThousandsVariant()
	{
		return array(
			new Entity\Validator\Length(null, 1),
		);
	}
}