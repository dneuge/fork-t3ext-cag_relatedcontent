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
 * Plugin 'List related pages' for the 'cag_relatedcontent' extension.
 *
 * @author	Jens Eipel <j.eipel@connecta.ag>
 * @package	TYPO3
 * @subpackage	tx_cagrelatedcontent
 */
class tx_cagrelatedcontent_pi3 extends tslib_pibase {
	var $prefixId      = 'tx_cagrelatedcontent_pi3';		// Same as class name
	var $scriptRelPath = 'pi3/class.tx_cagrelatedcontent_pi3.php';	// Path to this script relative to the extension dir.
	var $extKey        = 'cag_relatedcontent';	// The extension key.
	var $pi_checkCHash = true;
	
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
		
        $template = $this->loadTemplate();
        $inheritLevels = $this->getConfValue('inheritLevels', 0);
        $rootLine = $this->getRootline($inheritLevels);

        // Substitute Subpart RELATED_CONTENT_BY_CATEGORYLIST
        $categories = $this->getCategories($rootLine);
        $categoriesTemplate = $this->cObj->getSubpart($template, '###PAGE_CATEGORIES###');
        if (sizeof($categories) == 0) {
            $content = $this->cObj->substituteSubpart($content, '###PAGE_CATEGORIES###', "", 1);
        } else {
            $content = $this->table($categoriesTemplate, $categories, $columns = "", $tableMarkerName = "TABLE", $start = 0, $limit = 10000);
        }
        

		return $this->pi_wrapInBaseClass($content);
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

    function  getCategories($rootLine) {
        $statement = 'select
            tx_cagrelatedcontent_category.title as category_title,
            tx_cagrelatedcontent_category.description as category_description,
            a.sorting as sorting,
            a.uid_foreign as uid
            from
            pages_tx_cagrelatedcontent_category_mm a,
            tx_cagrelatedcontent_category
            where
            a.uid_local in (' . $rootLine . ')
            and a.uid_foreign = tx_cagrelatedcontent_category.uid
            and tx_cagrelatedcontent_category.deleted = 0
            and tx_cagrelatedcontent_category.hidden = 0
            order by sorting
        ';
        $categories = $this->dbResultToArray($GLOBALS['TYPO3_DB']->sql_query($statement));

        $categoriesUnique = array();

        $i = 0;
        foreach ($categories as &$row) {
            if (!isset($categoriesUnique[$row['uid']])) {
                $catDataTranslated = $GLOBALS['TSFE']->sys_page->getRecordOverlay('tx_cagrelatedcontent_category', array('uid' => $row['uid'], 'title' => $row['category_title'], 'description' => $row['category_description']), $GLOBALS['TSFE']->sys_language_content, $OLmode);
                $row['category_title'] = $catDataTranslated['title'];            
                $row['category_description'] = $catDataTranslated['description'];            
                $categoriesUnique[$row['uid']] = $row;
                $categoriesUnique[$row['uid']]['oddeven'] = ($i++ % 2 == 0) ? "odd" : "even";
                //$categoriesUnique[$row['uid']]['link'] = $this->buildUri(array(), $row['uid']);
            }
        }
        // t3lib_div::debug($categoriesUnique, "categories");
        return $categoriesUnique;
    }



    function getConfValue($confKey, $default = null) {
        // Try Flexforms
        $tmp = trim($this->pi_getFFvalue($this->cObj->data['pi_flexform'],  $confKey, "sDEF"));
        if (strlen($tmp) > 0)
            return trim($this->pi_getFFvalue($this->cObj->data['pi_flexform'],  $confKey, "sDEF"));

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

}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cag_relatedcontent/pi3/class.tx_cagrelatedcontent_pi3.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cag_relatedcontent/pi3/class.tx_cagrelatedcontent_pi3.php']);
}

?>
