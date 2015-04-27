<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2009 Jens Eipel <j.eipel@connecta.ag>
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/

require_once(PATH_tslib.'class.tslib_pibase.php');


/**
 * Plugin 'List pages of similar category' for the 'cag_relatedcontent' extension.
 *
 * @author	Jens Eipel <j.eipel@connecta.ag>
 * @package	TYPO3
 * @subpackage	tx_cagrelatedcontent
 */
class tx_cagrelatedcontent_pi1 extends tslib_pibase {
	var $prefixId      = 'tx_cagrelatedcontent_pi1';		// Same as class name
	var $scriptRelPath = 'pi1/class.tx_cagrelatedcontent_pi1.php';	// Path to this script relative to the extension dir.
	var $extKey        = 'cag_relatedcontent';	// The extension key.
	var $pi_checkCHash = true;
	
	var $newsSinglePid = -1;

	/**
	 * The main method of the PlugIn
	 *
	 * @param	string		$content: The PlugIn content
	 * @param	array		$conf: The PlugIn configuration
	 * @return	The content that is displayed on the website
	 */
	function main($content,$conf)	{
		$this->conf=$conf;
		$this->pi_setPiVarDefaults();
		$this->pi_loadLL();
        $this->pi_initPIflexform();

        $cache = 1;
        $this->pi_USER_INT_obj = 0;
		
        $template = $this->loadTemplate();

        $maxNumerOfItems = $this->getConfValue('maxItems', 100);
        $inheritLevels = $this->getConfValue('inheritLevels', 0);
        $rootLine = $this->getRootline($inheritLevels);
	
        if ($this->getConfValue('matchNewsCategories', 0) == 1) {
            // Match news if Match News is on
            $this->newsSinglePid = $this->getConfValue('newsSinglePid', '-1');
            if ($this->newsSinglePid == -1)
                return "Please specify &quot;newsSinglePid&quot; in configuration. It is the page ID (pid) where your tt_news plugin is located in (News Single Mode)";
            $relatedItems = $this->getRelatedItemsOfSameCategory("news", $rootLine, $maxNumerOfItems);
        } else {
            // Else Match Pages
            $relatedItems = $this->getRelatedItemsOfSameCategory("page", $rootLine, $maxNumerOfItems);		
        }

	    // Substitute Subpart RELATED_CONTENT_LIST
        $relatedContentTemplate = $this->cObj->getSubpart($template, '###RELATED_CONTENT_BY_CATEGORY###');
        if (sizeof($relatedItems) == 0) {
            $content = $this->cObj->substituteSubpart($content, '###RELATED_CONTENT_BY_CATEGORY###', "", 1);
        } else {
            $content = $this->table($relatedContentTemplate, $relatedItems, $columns = "", $tableMarkerName = "TABLE", $start = 0, $limit = 10000);
            /*
            $content = $this->cObj->substituteSubpart($content, '###HEADLINE_WHEN_NO_RESULTS###', "", 1);
            $content = $this->cObj->substituteSubpart($content, '###HEADLINE_WHEN_RESULTS###', $this->cObj->getSubpart($content, '###HEADLINE_WHEN_RESULTS###'), 1);
            */
        }
        

		return $this->pi_wrapInBaseClass($content);
	}

    function  getRelatedItemsOfSameCategory($itemType = "page", $rootLine, $maxNumber = 100) {
        $TYPO3_CONF_VARS['SYS']['displayErrors'] = '1';
        $TYPO3_CONF_VARS['SYS']['sqlDebug'] = true;

        switch ($itemType) {
            case "page":
                $statement = $this->getRelatedPagesSQLStatement($rootLine);
// t3lib_div::debug($statement);
                $relatedItems = $this->dbResultToArray($GLOBALS['TYPO3_DB']->sql_query($statement));
                // t3lib_div::debug($relatedItems, "Related Pages");
                break;
            case "news":
                $statement = $this->getRelatedNewsMatchByNameSQLStatement($rootLine);
// t3lib_div::debug($statement);
                $relatedItems = $this->dbResultToArray($GLOBALS['TYPO3_DB']->sql_query($statement));
// t3lib_div::debug($relatedItems);

                break;
            default:
                return "No itemType specified (pages or news are valid)";
        }
        $relatedItemsUnique = array();
        $i = 0;
        $maxScore = 0;
        foreach ($relatedItems as &$row) {
            $row['page_title'] = $row['page_title_translated'] == "" ? $row['page_title'] : $row['page_title_translated'];
            $catDataTranslated = $GLOBALS['TSFE']->sys_page->getRecordOverlay('tx_cagrelatedcontent_category', array('uid' => $row['cat_uid'], 'title' => $row['cat_title'], 'description' => $row['cat_description']), $GLOBALS['TSFE']->sys_language_content, $OLmode);
            $row['cat_title'] = $catDataTranslated['title'];            
            $row['cat_description'] = $catDataTranslated['description'];            
 
            $OLmode = ($this->sys_language_mode == 'strict'?'hideNonTranslated':'');
	    
	    if (isset($row['newsUid']))
		    $pk = $row['newsUid'];
	    else
		    $pk = $row['uid'];
	    
        if (!isset($relatedItemsUnique[$pk])) {
            $relatedItemsUnique[$pk] = $row;
            $relatedItemsUnique[$pk]['oddeven'] = ($i++ % 2 == 0) ? "odd" : "even";
            $urlParams = array();
            if (isset($row['newsUid'])) {
                $urlParams[] = "tx_ttnews[tt_news]=" . $row['newsUid'];
                $urlParams[] = "tx_ttnews[backPid]=" . $GLOBALS["TSFE"]->id;
            }
            $relatedItemsUnique[$pk]['link'] = $this->buildUri($urlParams, $row['uid']);
        } else
            $relatedItemsUnique[$pk]['cat_list'] .= ', ' . $row['cat_list'];
        $maxScore = ($row['score'] > $maxScore) ? $row['score'] : $maxScore;
        if ($i >= $maxNumber)
            break;
        }
        foreach ($relatedItemsUnique as &$row)
            $row['score'] = $maxScore - $row['score'];

        // t3lib_div::debug($relatedItemsUnique, "relatedItems");
        return $relatedItemsUnique;

    }
    
    function getRootline($maxLevels = 1e5) {
        if ($maxLevels < 1)
            return $GLOBALS["TSFE"]->id;
        $rootlineA = $GLOBALS["TSFE"]->sys_page->getRootLine($GLOBALS['TSFE']->id);
        $r = array();
        foreach ($rootlineA as &$value) {
            if (sizeof($r) == $maxLevels + 1)
                break;
            $r[] = $value['uid'];
        }
        return implode(',', $r);
    }

    
    function getRelatedPagesSQLStatement($rootLine) {
	    $statement = 'select distinct(pages.uid) as uid,
            pages.title as page_title,
            pages_language_overlay.title as page_title_translated,
            (b.sorting + a.sorting) as score,
            a.uid_local,
            tx_cagrelatedcontent_category.title as cat_title,
            tx_cagrelatedcontent_category.title as cat_list,
            tx_cagrelatedcontent_category.description as cat_description,
            a.uid_foreign as cat_uid
            from
            tx_cagrelatedcontent_category,
            pages_tx_cagrelatedcontent_category_mm a,
            pages_tx_cagrelatedcontent_category_mm b,
            pages
            left outer join pages_language_overlay on (pages.uid = pages_language_overlay.pid and pages_language_overlay.sys_language_uid = ' . $GLOBALS['TSFE']->sys_language_content . ')
            where
            b.uid_local = pages.uid
            and a.uid_local != b.uid_local
            and b.uid_local != ' . $GLOBALS["TSFE"]->id . '
            and a.uid_local in  (' . $rootLine .')
            and a.uid_foreign = b.uid_foreign
            and tx_cagrelatedcontent_category.uid = b.uid_foreign
            and tx_cagrelatedcontent_category.deleted = 0
            and tx_cagrelatedcontent_category.hidden = 0
            and pages.deleted = 0
            and pages.hidden = 0'
            . $this->cObj->enableFields("tx_cagrelatedcontent_category") . $this->cObj->enableFields("pages") .
            'order by score asc';
        return $statement;
    }
    
    function getRelatedNewsMatchByNameSQLStatement($rootline) {
	    return '
	    select
	    "' . $this->newsSinglePid . '" as uid,
            tt_news.title as page_title,
            tt_news.uid as newsUid,
            tt_news_cat.title as cat_title,
            pages_tx_cagrelatedcontent_category_mm.sorting as score,
            tx_cagrelatedcontent_category.title as cat_title,
            tx_cagrelatedcontent_category.title as cat_list,
            tx_cagrelatedcontent_category.description as cat_description
            from 
            tt_news,
            tt_news_cat,
            tt_news_cat_mm,
            pages,
            pages_tx_cagrelatedcontent_category_mm,
            tx_cagrelatedcontent_newscat_relcontentcat,
            tx_cagrelatedcontent_category
            where
            pages_tx_cagrelatedcontent_category_mm.uid_local in (' . $rootline . ')
            and tt_news_cat_mm.uid_foreign = tt_news_cat.uid
            and tt_news_cat_mm.uid_local = tt_news.uid
            and pages_tx_cagrelatedcontent_category_mm.uid_local = pages.uid
            and pages_tx_cagrelatedcontent_category_mm.uid_foreign = tx_cagrelatedcontent_newscat_relcontentcat.relcontent_cat
            and tx_cagrelatedcontent_newscat_relcontentcat.news_category = tt_news_cat_mm.uid_foreign
            and tx_cagrelatedcontent_newscat_relcontentcat.relcontent_cat = tx_cagrelatedcontent_category.uid'
            . $this->cObj->enableFields("tt_news") . $this->cObj->enableFields("tt_news_cat") . $this->cObj->enableFields("pages") . $this->cObj->enableFields("tx_cagrelatedcontent_newscat_relcontentcat") . $this->cObj->enableFields("tx_cagrelatedcontent_category") .
            'order by score asc
        ';
    }

    function getConfValue($confKey, $default = null) {
        // Try Flexforms
        $tmp = trim($this->pi_getFFvalue($this->cObj->data['pi_flexform'],  $confKey, "sDEF"));
        if (strlen($tmp) > 0)
            return $tmp;

        // Try Conf	
        $tmp = explode('.', $confKey);
        $cur = $this->conf;
        foreach ($tmp as $value) {			
            if (is_array($cur[$value . "."]))
                $cur = $cur[$value . "."];
            else
                $cur = $cur[$value];
        }
        if (is_array($cur) || strlen($cur) > 0)
            return $cur;
        return $default;
    }

    function loadTemplate($templateFile = null) {
        if ($templateFile == null)
            $templateFile = $this->getConfValue('template', 'EXT:' . $this->extKey . '/templateFile.html');
        return $this->cObj->fileResource($templateFile);	
    }

    function imageProcessing(&$ts, $path) {
        if (substr($path, 0, 1) == "/")
            $path = substr($path, 1);
        $ts['file'] = $path;
        return $this->cObj->IMAGE($ts);	
    }
    // Convert DB Resut to Nested Array (table[0 ... i]{columnName}])
    function dbResultToArray(&$in, $start = 0, $end = 1e6, $firstRow = false) {
        $resultA = array();
        if (!is_resource($in) || $GLOBALS['TYPO3_DB']->sql_num_rows($in) == 0)
            return $resultA;
        $rowId = 0;
        while ($columns = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($in)) {
            if ($rowId < $start)
                continue;
            if ($rowId >= $end)
                break;
            $columns['rowid'] = ++$rowId;
            $columns['oddeven'] = ($rowId % 2 == 0) ? "odd" : "even";		
            $resultA[] = $columns;
        }
        @mysql_free_result($in);
        if ($firstRow)
            return $resultA[0];
        return $resultA;
    }

    function buildUri($params, $page = "") {
        $uri = (is_numeric($page)) ? $this->pi_getPageLink($page) : $page;
        if (!is_array($params))
            $params = explode(",", $params);
        if (sizeof($params) > 0)
            $uri = (strpos($uri, "?") === false) ? ($uri . "?") : ($uri . "&");
        foreach ($params as $param) {
            $tmp = explode("=", $param);
            $uri .= $tmp[0] . "=" . urlencode($tmp[1]) . "&";
        }
        return (substr($uri, -1) == "&") ? substr($uri, 0, -1) : $uri;
    }

    function table(&$template, &$data, $columns = "", $tableMarkerName = "TABLE", $start = 0, $limit = 10000) {
        if (!isset($data))
          return $this->cObj->substituteSubpart($template, '###' . $tableMarkerName . '###', "", 1);
           
        $subpartTable = $this->cObj->getSubpart($template, '###' . $tableMarkerName . '###');
        $subpartTable = $this->cObj->substituteSubpart($subpartTable, "###NO_RESULT###", "");
        
        $subpartRows = $this->cObj->getSubpart($subpartTable, '###ROWS###');
        $subpartRow = $this->cObj->getSubpart($subpartRows, '###ROW###');
        $rows = "";
        $i = 0;

        if (is_array($columns)) {
            foreach ($data as $rowId => $dataRow) {
              if ($i++ < $start) continue;
              if ($i > ($start + $limit)) break;
              $row = $subpartRow;
              foreach ($dataRow as $columnName => $columnContent) {
                  $row = $this->cObj->substituteMarker($row, "###" . strtoupper($columnName) . "###", $columnContent);
              }
              $rows .= $this->cObj->substituteSubpart($subpartRows, "###ROW###", $row);
            }					
        } else {
            foreach ($data as $rowId => $dataRow) {
              if ($i++ < $start) continue;
              if ($i > ($start + $limit)) break;
              $row = $subpartRow;
                if (is_array($dataRow))
                  foreach ($dataRow as $columnName => $columnContent) {
                      $row = $this->cObj->substituteMarker($row, '###'. strtoupper($columnName) . '###', $columnContent);
                  }
                else
                    $row = $dataRow;
            $rows .= $this->cObj->substituteSubpart($subpartRows, "###ROW###", $row);
            }
        }

        // $subpartRows = $this->cObj->substituteSubpart($subpartRows, "###ROWS###", $rows, 1);
        $subpartTable = $this->cObj->substituteSubpart($subpartTable, "###ROWS###", $rows, 1);
        $template = $this->cObj->substituteSubpart($template, "###" . $tableMarkerName . "###", $subpartTable, 1);
        return $template;
    }

    function column2List (&$in, $columnName, $delimiter = ",", $quoted = false) {
        $list = '';
        foreach ($in as $row) {
            $list .= ($quoted ? "'" : "") . $row[$columnName] . ($quoted ? "'" : "") . $delimiter;
        }

        if (strlen($list) > 0)
            return substr($list, 0, strlen($list) - strlen($delimiter));
        return $list;
    }
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cag_relatedcontent/pi1/class.tx_cagrelatedcontent_pi1.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cag_relatedcontent/pi1/class.tx_cagrelatedcontent_pi1.php']);
}

?>
