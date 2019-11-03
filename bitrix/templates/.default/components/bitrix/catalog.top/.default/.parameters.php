<?php 
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arTemplateParameters = array(
   "ALLOW_SALE" => Array(
      "NAME" => "Разрешить продажи",
      "TYPE" => "LIST",
      "VALUES" => array("Y" => "Да", "N" => "Нет")
   ),
   	"HIDE_CHECK_AVAILABILITY" => array(
			"PARENT" => "BASE",
			"NAME" => 'Скрыть ссылку уточнить наличие',
      "TYPE" => "STRING",
			//"TYPE" => "LIST",
      //"VALUES" => array("Y" => "Да", "N" => "Нет"),
      "DEFAULT" => "N",
		),
);