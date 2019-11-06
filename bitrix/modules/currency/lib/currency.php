<?php
namespace Bitrix\Currency;

use Bitrix\Main\Entity;
use Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

/**
 * Class CurrencyTable
 *
 * Fields:
 * <ul>
 * <li> CURRENCY string(3) mandatory
 * <li> AMOUNT_CNT int optional default 1
 * <li> AMOUNT float optional
 * <li> SORT int optional default 100
 * <li> DATE_UPDATE datetime mandatory
 * </ul>
 *
 * @package Bitrix\Currency
 **/
class CurrencyTable extends Entity\DataManager
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
		return 'b_catalog_currency';
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
				'required' => true,
				'title' => Loc::getMessage('CURRENCY_ENTITY_CURRENCY_FIELD'),
			),
			'AMOUNT_CNT' => array(
				'data_type' => 'integer',
				'title' => Loc::getMessage('CURRENCY_ENTITY_AMOUNT_CNT_FIELD'),
			),
			'AMOUNT' => array(
				'data_type' => 'float',
				'title' => Loc::getMessage('CURRENCY_ENTITY_AMOUNT_FIELD'),
			),
			'SORT' => array(
				'data_type' => 'integer',
				'title' => Loc::getMessage('CURRENCY_ENTITY_SORT_FIELD'),
			),
			'DATE_UPDATE' => array(
				'data_type' => 'datetime',
				'required' => true,
				'title' => Loc::getMessage('CURRENCY_ENTITY_DATE_UPDATE_FIELD'),
			),
			'LANG_FORMAT' => array(
				'data_type' => 'Bitrix\Currency\CurrencyLang',
				'reference' => array('=this.CURRENCY' => 'ref.CURRENCY'),
			)
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
}