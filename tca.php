<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

$TCA["tx_cagrelatedcontent_category"] = array (
	"ctrl" => $TCA["tx_cagrelatedcontent_category"]["ctrl"],
	"interface" => array (
		"showRecordFieldList" => "hidden,title,description"
	),
	"feInterface" => $TCA["tx_cagrelatedcontent_category"]["feInterface"],
	"columns" => array (
		'hidden' => array (		
			'exclude' => 1,
			'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.hidden',
			'config'  => array (
				'type'    => 'check',
				'default' => '0'
			)
		),
        'sys_language_uid' => array (        
            'exclude' => 1,
            'label'  => 'LLL:EXT:lang/locallang_general.xml:LGL.language',
            'config' => array (
                'type'                => 'select',
                'foreign_table'       => 'sys_language',
                'foreign_table_where' => 'ORDER BY sys_language.title',
                'items' => array(
                    array('LLL:EXT:lang/locallang_general.xml:LGL.allLanguages', -1),
                    array('LLL:EXT:lang/locallang_general.xml:LGL.default_value', 0)
                )
            )
        ),
        'l18n_parent' => array (        
            'displayCond' => 'FIELD:sys_language_uid:>:0',
            'exclude'     => 1,
            'label'       => 'LLL:EXT:lang/locallang_general.xml:LGL.l18n_parent',
            'config'      => array (
                'type'  => 'select',
                'items' => array (
                    array('', 0),
                ),
                'foreign_table'       => 'tx_cagrelatedcontent_category',
                'foreign_table_where' => 'AND tx_cagrelatedcontent_category.pid=###CURRENT_PID### AND tx_cagrelatedcontent_category.sys_language_uid IN (-1,0)',
            )
        ),
        'l18n_diffsource' => array (        
            'config' => array (
                'type' => 'passthrough'
            )
        ),

/*
		'fe_group' => array (		
			'exclude' => 1,
			'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.fe_group',
			'config'  => array (
				'type'  => 'select',
				'items' => array (
					array('', 0),
					array('LLL:EXT:lang/locallang_general.xml:LGL.hide_at_login', -1),
					array('LLL:EXT:lang/locallang_general.xml:LGL.any_login', -2),
					array('LLL:EXT:lang/locallang_general.xml:LGL.usergroups', '--div--')
				),
				'foreign_table' => 'fe_groups'
			)
		),
*/
		"title" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:cag_relatedcontent/locallang_db.xml:tx_cagrelatedcontent_category.title",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",	
				"eval" => "required,trim",
			)
		),
		"description" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:cag_relatedcontent/locallang_db.xml:tx_cagrelatedcontent_category.description",		
			"config" => Array (
				"type" => "text",
				"cols" => "30",	
				"rows" => "3",
			)
		),
	),
	"types" => array (
		"0" => array("showitem" => "sys_language_uid;;;;1-1-1, l18n_parent, l18n_diffsource, hidden;;1;;1-1-1, title;;;;2-2-2, description;;;;3-3-3")
	),
	"palettes" => array (
		//"1" => array("showitem" => "fe_group")
	)
);

$TCA['tx_cagrelatedcontent_newscat_relcontentcat'] = array (
    'ctrl' => $TCA['tx_cagrelatedcontent_newscat_relcontentcat']['ctrl'],
    'interface' => array (
        'showRecordFieldList' => 'title,news_category,relcontent_cat'
    ),
    'feInterface' => $TCA['tx_cagrelatedcontent_newscat_relcontentcat']['feInterface'],
    'columns' => array (
		"title" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:cag_relatedcontent/locallang_db.xml:tx_cagrelatedcontent_newscat_relcontentcat.title",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",	
				"eval" => "required,trim",
			)
		),
        'news_category' => array (        
            'exclude' => 0,        
            'label' => 'LLL:EXT:cag_relatedcontent/locallang_db.xml:tx_cagrelatedcontent_newscat_relcontentcat.news_category',        
            'config' => array (
                'type' => 'group',    
                'internal_type' => 'db',    
                'allowed' => 'tt_news_cat',    
                'size' => 1,    
                'minitems' => 0,
                'maxitems' => 1,
            )
        ),
        'relcontent_cat' => array (        
            'exclude' => 0,        
            'label' => 'LLL:EXT:cag_relatedcontent/locallang_db.xml:tx_cagrelatedcontent_newscat_relcontentcat.relcontent_cat',        
            'config' => array (
                'type' => 'group',    
                'internal_type' => 'db',    
                'allowed' => 'tx_cagrelatedcontent_category',    
                'size' => 1,    
                'minitems' => 0,
                'maxitems' => 1,
            )
        ),
    ),
    'types' => array (
        '0' => array('showitem' => 'title, news_category;;;;1-1-1, relcontent_cat')
    ),
    'palettes' => array (
        '1' => array('showitem' => '')
    )
);

?>
