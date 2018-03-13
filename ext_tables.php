<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

t3lib_div::loadTCA('tt_content');
$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY.'_pi1']='layout,select_key';


t3lib_extMgm::addPlugin(Array('LLL:EXT:cab_imagemenu/locallang_db.xml:tt_content.list_type_pi1', $_EXTKEY.'_pi1'),'list_type');


t3lib_extMgm::addStaticFile($_EXTKEY,"pi1/static/","Image menu");


if (TYPO3_MODE=="BE")	$TBE_MODULES_EXT["xMOD_db_new_content_el"]["addElClasses"]["tx_cabimagemenu_pi1_wizicon"] = t3lib_extMgm::extPath($_EXTKEY).'pi1/class.tx_cabimagemenu_pi1_wizicon.php';

$tempColumns = Array (
	"tx_cabimagemenu_image_width" => Array (		
		"exclude" => 1,		
		"label" => "LLL:EXT:cab_imagemenu/locallang_db.xml:tt_content.tx_cabimagemenu_image_width",		
		"config" => Array (
			"type" => "input",
			"size" => "4",
			"max" => "4",
			"eval" => "int",
			"checkbox" => "0",
			"range" => Array (
				"upper" => "1000",
				"lower" => "10"
			),
			"default" => 0
		)
	),
	"tx_cabimagemenu_image_height" => Array (		
		"exclude" => 1,		
		"label" => "LLL:EXT:cab_imagemenu/locallang_db.xml:tt_content.tx_cabimagemenu_image_height",		
		"config" => Array (
			"type" => "input",
			"size" => "4",
			"max" => "4",
			"eval" => "int",
			"checkbox" => "0",
			"range" => Array (
				"upper" => "1000",
				"lower" => "10"
			),
			"default" => 0
		)
	),
	"tx_cabimagemenu_exclude_current" => Array (		
		"exclude" => 1,		
		"label" => "LLL:EXT:cab_imagemenu/locallang_db.xml:tt_content.tx_cabimagemenu_exclude_current",		
		"config" => Array (
			"type" => "check",
			"default" => 1,
		)
	),
);


t3lib_div::loadTCA("tt_content");
t3lib_extMgm::addTCAcolumns("tt_content",$tempColumns,1);
t3lib_extMgm::addToAllTCAtypes("tt_content","tx_cabimagemenu_image_width;;;;1-1-1, tx_cabimagemenu_image_height, tx_cabimagemenu_exclude_current");

?>