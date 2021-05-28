<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

if(!CModule::IncludeModule("iblock"))
	return;


$arComponentParameters = array(
	"GROUPS" => array(
	),
	"PARAMETERS" => array(
		"GET_MAGAZINE" => array(
			"PARENT" => "BASE",
			"NAME" => "Получить магазины",
			"TYPE" => "CHECKBOX",

		),
		"GET_ONLY_CURRENT" => array(
			"PARENT" => "BASE",
			"NAME" => "Получить для текущего города",
			"TYPE" => "CHECKBOX",

		),

	),
);




