<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');
$tempColumns = Array (
	"tx_cagrelatedcontent_category" => Array (		
		"exclude" => 1,		
		"label" => "LLL:EXT:cag_relatedcontent/locallang_db.xml:pages.tx_cagrelatedcontent_category",		
		"config" => Array (
			"type" => "select",	
			"foreign_table" => "tx_cagrelatedcontent_category",	
			"foreign_table_where" => "ORDER BY tx_cagrelatedcontent_category.uid",	
			"size" => 8,	
			"minitems" => 0,
			"maxitems" => 10,	
			"MM" => "pages_tx_cagrelatedcontent_category_mm",	
			"wizards" => Array(
				"_PADDING" => 2,
				"_VERTICAL" => 1,
				"add" => Array(
					"type" => "script",
					"title" => "Create new record",
					"icon" => "add.gif",
					"params" => Array(
						"table"=>"tx_cagrelatedcontent_category",
						"pid" => "###CURRENT_PID###",
						"setValue" => "prepend"
					),
					"script" => "wizard_add.php",
				),
				"list" => Array(
					"type" => "script",
					"title" => "List",
					"icon" => "list.gif",
					"params" => Array(
						"table"=>"tx_cagrelatedcontent_category",
						"pid" => "###CURRENT_PID###",
					),
					"script" => "wizard_list.php",
				),
				"edit" => Array(
					"type" => "popup",
					"title" => "Edit",
					"script" => "wizard_edit.php",
					"popup_onlyOpenIfSelected" => 1,
					"icon" => "edit2.gif",
					"JSopenParams" => "height=350,width=580,status=0,menubar=0,scrollbars=1",
				),
			),
		)
	),
	"tx_cagrelatedcontent_pages" => Array (		
		"exclude" => 1,		
		"label" => "LLL:EXT:cag_relatedcontent/locallang_db.xml:pages.tx_cagrelatedcontent_pages",		
		"config" => Array (
			"type" => "group",	
			"internal_type" => "db",	
			"allowed" => "pages",	
			"size" => 5,	
			"minitems" => 0,
			"maxitems" => 10,	
			"MM" => "pages_tx_cagrelatedcontent_pages_mm",
		)
	),
);


t3lib_div::loadTCA("pages");
t3lib_extMgm::addTCAcolumns("pages",$tempColumns,1);
t3lib_extMgm::addToAllTCAtypes("pages","--div--;Category,tx_cagrelatedcontent_category;;;;1-1-1, tx_cagrelatedcontent_pages");

$TCA["tx_cagrelatedcontent_category"] = array (
	"ctrl" => array (
		'title'     => 'LLL:EXT:cag_relatedcontent/locallang_db.xml:tx_cagrelatedcontent_category',		
		'label'     => 'title',	
		'tstamp'    => 'tstamp',
		'crdate'    => 'crdate',
		'cruser_id' => 'cruser_id',
        'languageField'            => 'sys_language_uid',    
        'transOrigPointerField'    => 'l18n_parent',    
        'transOrigDiffSourceField' => 'l18n_diffsource',
		'default_sortby' => "ORDER BY sorting",	
		'delete' => 'deleted',	
        'sortby' => 'sorting',
		'enablecolumns' => array (		
			'disabled' => 'hidden',	
			'fe_group' => 'fe_group',
		),
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_cagrelatedcontent_category.gif',
	),
	"feInterface" => array (
		"fe_admin_fieldList" => "sys_language_uid, l18n_parent, l18n_diffsource, hidden, fe_group, title, description,sorting",
	)
);


$TCA['tx_cagrelatedcontent_newscat_relcontentcat'] = array (
    'ctrl' => array (
        'title'     => 'LLL:EXT:cag_relatedcontent/locallang_db.xml:tx_cagrelatedcontent_newscat_relcontentcat',        
        'label'     => 'title',    
        'tstamp'    => 'tstamp',
        'crdate'    => 'crdate',
        'cruser_id' => 'cruser_id',
        'default_sortby' => 'ORDER BY title',    
        'delete' => 'deleted',    
        'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
        'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_cagrelatedcontent_newscategory.gif',
    ),
);


// START
$tempColumns = array (
    'tx_cagrelatedcontent_categories' => array (        
        'exclude' => 0,        
        'label' => 'LLL:EXT:cag_relatedcontent/locallang_db.xml:tt_content.tx_cagrelatedcontent_categories',        
        'config' => array (
            'type' => 'group',    
            'internal_type' => 'db',    
            'allowed' => 'tt_content',    
            'size' => 5,    
            'minitems' => 0,
            'maxitems' => 5,    
            "MM" => "tt_content_tx_cagrelatedcontent_categories_mm",
        ),
		"config" => Array (
			"type" => "select",	
			"foreign_table" => "tx_cagrelatedcontent_category",	
			"foreign_table_where" => "ORDER BY tx_cagrelatedcontent_category.uid",	
			"size" => 8,	
			"minitems" => 0,
			"maxitems" => 10,	
			"MM" => "tt_content_tx_cagrelatedcontent_categories_mm",	
			"wizards" => Array(
				"_PADDING" => 2,
				"_VERTICAL" => 1,
				"add" => Array(
					"type" => "script",
					"title" => "Create new record",
					"icon" => "add.gif",
					"params" => Array(
						"table"=>"tx_cagrelatedcontent_category",
						"pid" => "###CURRENT_PID###",
						"setValue" => "prepend"
					),
					"script" => "wizard_add.php",
				),
				"list" => Array(
					"type" => "script",
					"title" => "List",
					"icon" => "list.gif",
					"params" => Array(
						"table"=>"tx_cagrelatedcontent_category",
						"pid" => "###CURRENT_PID###",
					),
					"script" => "wizard_list.php",
				),
				"edit" => Array(
					"type" => "popup",
					"title" => "Edit",
					"script" => "wizard_edit.php",
					"popup_onlyOpenIfSelected" => 1,
					"icon" => "edit2.gif",
					"JSopenParams" => "height=350,width=580,status=0,menubar=0,scrollbars=1",
				),
			),
		)
    ),
);


t3lib_div::loadTCA('tt_content');
t3lib_extMgm::addTCAcolumns('tt_content',$tempColumns,1);
t3lib_extMgm::addToAllTCAtypes('tt_content','--div--;Category,tx_cagrelatedcontent_categories;;;;1-1-1', '', 'after:access');

// END





t3lib_div::loadTCA('tt_content');

$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY.'_pi1']='layout,select_key';
t3lib_extMgm::addPlugin(array('LLL:EXT:cag_relatedcontent/locallang_db.xml:tt_content.list_type_pi1', $_EXTKEY.'_pi1'),'list_type');
t3lib_extMgm::addStaticFile($_EXTKEY,"pi1/static/","List pages of similar category");
if (TYPO3_MODE=="BE")	$TBE_MODULES_EXT["xMOD_db_new_content_el"]["addElClasses"]["tx_cagrelatedcontent_pi1_wizicon"] = t3lib_extMgm::extPath($_EXTKEY).'pi1/class.tx_cagrelatedcontent_pi1_wizicon.php';
t3lib_div::loadTCA('tt_content');

$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY.'_pi2']='layout,select_key';
t3lib_extMgm::addPlugin(array('LLL:EXT:cag_relatedcontent/locallang_db.xml:tt_content.list_type_pi2', $_EXTKEY.'_pi2'),'list_type');
t3lib_extMgm::addStaticFile($_EXTKEY,"pi2/static/","List related pages");
if (TYPO3_MODE=="BE")	$TBE_MODULES_EXT["xMOD_db_new_content_el"]["addElClasses"]["tx_cagrelatedcontent_pi2_wizicon"] = t3lib_extMgm::extPath($_EXTKEY).'pi2/class.tx_cagrelatedcontent_pi2_wizicon.php';

$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY.'_pi3']='layout,select_key';
t3lib_extMgm::addPlugin(array('LLL:EXT:cag_relatedcontent/locallang_db.xml:tt_content.list_type_pi3', $_EXTKEY.'_pi3'),'list_type');
t3lib_extMgm::addStaticFile($_EXTKEY,"pi3/static/","List related pages");
if (TYPO3_MODE=="BE")	$TBE_MODULES_EXT["xMOD_db_new_content_el"]["addElClasses"]["tx_cagrelatedcontent_pi3_wizicon"] = t3lib_extMgm::extPath($_EXTKEY).'pi3/class.tx_cagrelatedcontent_pi3_wizicon.php';

$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY.'_pi4']='layout,select_key';
t3lib_extMgm::addPlugin(array('LLL:EXT:cag_relatedcontent/locallang_db.xml:tt_content.list_type_pi4', $_EXTKEY.'_pi4'),'list_type');
t3lib_extMgm::addStaticFile($_EXTKEY,"pi4/static/","List related pages");
if (TYPO3_MODE=="BE")	$TBE_MODULES_EXT["xMOD_db_new_content_el"]["addElClasses"]["tx_cagrelatedcontent_pi4_wizicon"] = t3lib_extMgm::extPath($_EXTKEY).'pi4/class.tx_cagrelatedcontent_pi4_wizicon.php';

$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY.'_pi5']='layout,select_key';
t3lib_extMgm::addPlugin(array('LLL:EXT:cag_relatedcontent/locallang_db.xml:tt_content.list_type_pi5', $_EXTKEY.'_pi5'),'list_type');
t3lib_extMgm::addStaticFile($_EXTKEY,"pi5/static/","List related pages");
if (TYPO3_MODE=="BE")	$TBE_MODULES_EXT["xMOD_db_new_content_el"]["addElClasses"]["tx_cagrelatedcontent_pi5_wizicon"] = t3lib_extMgm::extPath($_EXTKEY).'pi5/class.tx_cagrelatedcontent_pi5_wizicon.php';


$TCA['tt_content']['types']['list']['subtypes_addlist'][$_EXTKEY.'_pi1']='pi_flexform';
t3lib_extMgm::addPiFlexFormValue($_EXTKEY.'_pi1','FILE:EXT:'.$_EXTKEY.'/pi1/flexform.xml');
$TCA['tt_content']['types']['list']['subtypes_addlist'][$_EXTKEY.'_pi2']='pi_flexform';
t3lib_extMgm::addPiFlexFormValue($_EXTKEY.'_pi2','FILE:EXT:'.$_EXTKEY.'/flexform.xml');
$TCA['tt_content']['types']['list']['subtypes_addlist'][$_EXTKEY.'_pi3']='pi_flexform';
t3lib_extMgm::addPiFlexFormValue($_EXTKEY.'_pi3','FILE:EXT:'.$_EXTKEY.'/flexform.xml');
$TCA['tt_content']['types']['list']['subtypes_addlist'][$_EXTKEY.'_pi4']='pi_flexform';
t3lib_extMgm::addPiFlexFormValue($_EXTKEY.'_pi4','FILE:EXT:'.$_EXTKEY.'/flexform.xml');
$TCA['tt_content']['types']['list']['subtypes_addlist'][$_EXTKEY.'_pi5']='pi_flexform';
t3lib_extMgm::addPiFlexFormValue($_EXTKEY.'_pi5','FILE:EXT:'.$_EXTKEY.'/pi5/flexform.xml');
?>
