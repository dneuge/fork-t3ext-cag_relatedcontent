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

// RENDER CONTENT
// $tt_content_conf = array('tables' => 'tt_content','source' => "45,46,47",'dontCheckPid' => 1);
// $content .= $this->cObj->RECORDS($tt_content_conf);  


/**
 * Plugin 'List related pages' for the 'cag_relatedcontent' extension.
 *
 * @author	Jens Eipel <j.eipel@connecta.ag>
 * @package	TYPO3
 * @subpackage	tx_cagrelatedcontent
 */
class tx_cagrelatedcontent_pi5 extends tslib_pibase {
	var $prefixId      = 'tx_cagrelatedcontent_pi5';		// Same as class name
	var $scriptRelPath = 'pi5/class.tx_cagrelatedcontent_pi5.php';	// Path to this script relative to the extension dir.
	var $extKey        = 'cag_relatedcontent';	// The extension key.
	var $pi_checkCHash = true;
	
	/**
	 * The main method of the PlugIn
	 *
	 * @param	string		$content: The PlugIn content
	 * @param	array		$conf: The PlugIn configuration
	 * @return	The content that is displayed on the website
	 */
	function main($content,$conf) {
		$this->conf=$conf;
		$this->pi_setPiVarDefaults();
		$this->pi_loadLL();
        $this->pi_initPIflexform();
		
        $template = $this->loadTemplate();
        if (isset($_REQUEST["detailUid"])) {
            $relatedContentDetailTemplate = $this->cObj->getSubpart($template, '###RELATED_CONTENT_DETAIL###');
            $tt_content_conf = array('tables' => 'tt_content','source' => $_REQUEST["detailUid"], 'dontCheckPid' => 1);
            $tmp = $this->cObj->RECORDS($tt_content_conf);
            $backMessage = $this->getConfValue("backMessage", "[back]");
            $content = $this->cObj->substituteMarker($relatedContentDetailTemplate, '###CONTENT###', $tmp, 1);
            $content = $this->cObj->substituteMarker($content, '###BACKLINK###', "<a href='" . $this->buildUri(array(), $GLOBALS["TSFE"]->id) . "'>" . $backMessage . "</a>" , 1);
            return $content;
        }

        $previewNumberOfCharacters = $this->getConfValue("maxCharacters", 200);
        $maxNumberOfArticles = $this->getConfValue("maxItems", 100);
        $relatedContentListTemplate = $this->cObj->getSubpart($template, '###RELATED_CONTENT_LIST###');
        $relatedContentDetaillinkTemplate = $this->cObj->getSubpart($template, '###RELATED_CONTENT_DETAILLINK###');

        // Substitute Subpart RELATED_CONTENT_BY_CATEGORYLIST
        $relatedContent = $this->getRelatedContent($maxNumberOfArticles);
        // t3lib_div::debug($categories);
        $contentUids = explode(",", $this->column2list($relatedContent, "uid"));
        $content = "";
        foreach ($contentUids as $contentUid) {
            $tt_content_conf = array('tables' => 'tt_content','source' => $contentUid,'dontCheckPid' => 1);
            $tmp = $this->cObj->RECORDS($tt_content_conf);
            if ($previewNumberOfCharacters > 0)
                $tmp = $this->closetags($this->getPreview($tmp, $relatedContentDetaillinkTemplate, $contentUid, $previewNumberOfCharacters));

            $content .= $this->cObj->substituteMarker($relatedContentListTemplate, '###ARTICLE###', $tmp, 1);  
        }


		return $this->pi_wrapInBaseClass($content);
	}

    function  getRelatedContent($maxResults = 100) {

        $statement = '
            select
            distinct(tt_content_tx_cagrelatedcontent_categories_mm.uid_local) as uid,
            pages_tx_cagrelatedcontent_category_mm.sorting as score
            from
            tt_content_tx_cagrelatedcontent_categories_mm, pages_tx_cagrelatedcontent_category_mm, tt_content
            where
            pages_tx_cagrelatedcontent_category_mm.uid_local = ' . $GLOBALS["TSFE"]->id . '
            and pages_tx_cagrelatedcontent_category_mm.uid_foreign = tt_content_tx_cagrelatedcontent_categories_mm.uid_foreign
            and tt_content_tx_cagrelatedcontent_categories_mm.uid_local = tt_content.uid
            and tt_content.sys_language_uid = ' . $this->cObj->data['sys_language_uid'] . '
            and tt_content.deleted = 0 and tt_content.hidden = 0
            order by score asc';
        $relatedContent = $this->dbResultToArray($GLOBALS['TYPO3_DB']->sql_query($statement));
// t3lib_div::debug($relatedContent);

        $relatedContentUnique = array();

        $i = 0;
        $maxScore = 0;
        foreach ($relatedContent as &$row) {
            $row['header'] = $row['page_title_translated'] == "" ? $row['header'] : $row['page_title_translated'];
            if (!isset($relatedContentUnique[$row['uid']])) {
                $relatedContentUnique[$row['uid']] = $row;
                $relatedContentUnique[$row['uid']]['oddeven'] = ($i++ % 2 == 0) ? "odd" : "even";
            } else
                $relatedContentUnique[$row['uid']]['cat_list'] .= ', ' . $row['cat_list'];
            $maxScore = ($row['score'] > $maxScore) ? $row['score'] : $maxScore;
            if (sizeof($relatedContentUnique) >= $maxResults)
                break;
        }
        foreach ($relatedContentUnique as &$row)
            $row['score'] = $maxScore - $row['score'];

        return $relatedContentUnique;
    }

function closetags( $html ) {
    #put all opened tags into an array
    preg_match_all ( "#<([a-z]+)( .*)?(?!/)>#iU", $html, $result );
    $openedtags = $result[1];
    #
    #put all closed tags into an array
    #
    preg_match_all ( "#</([a-z]+)>#iU", $html, $result );
    $closedtags = $result[1];
    $len_opened = count ( $openedtags );
    #
    # all tags are closed
    #
    if ( count ( $closedtags ) == $len_opened ) {
        return $html;
    }
    $openedtags = array_reverse ( $openedtags );
    #
    # close tags
    #
    for( $i = 0; $i < $len_opened; $i++ ) {
        if ( !in_array ( $openedtags[$i], $closedtags ) ) {
            $html .= "</" . $openedtags[$i] . ">";
        }
        else {
            unset ( $closedtags[array_search ( $openedtags[$i], $closedtags)] );
        }
    }
    return $html;
}

    function getPreview(&$content, $detaillinkSubpart, $uid, $numberOfCharacters) {
        $tagOpen = false;
        $waitForClosingTag = false;
        $waitForWordEnd = false;
        $textCount = 0;
        for ($i = 0; $i <= strlen($content); $i++) {
            $curChar = substr($content, $i, 1);
            if (!$tagOpen) {
                if ($curChar == "<") {
                    $tagOpen = true;
                }
                else
                    $textCount++;
            } else {
                if ($curChar == ">")
                    $tagOpen = false;
            }
            if ($textCount >= $numberOfCharacters) {
                if ($curChar != " ")
                    $waitForWordEnd = true;
                else 
                    $waitForWordEnd = false;
                if (!$waitForClosingTag && !$waitForWordEnd)
                    break;
            } 
        }
        $tmp = substr($content, 0, $i);
        if (strlen($tmp) < strlen($content)) {
            $link = $this->buildUri(array(0 => "detailUid=" . $uid), $GLOBALS["TSFE"]->id);
            $tmp .= "&nbsp;...";
        } 
        $detailMessage = $this->getConfValue("detailMessage", "[details]");
        $link = "<a href='" . $link . "'>" . $detailMessage . "</a>";
        $tmp .= $this->cObj->substituteMarker($detaillinkSubpart, '###DETAILLINK###', $link , 1);
        return $tmp;
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



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cag_relatedcontent/pi5/class.tx_cagrelatedcontent_pi5.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cag_relatedcontent/pi5/class.tx_cagrelatedcontent_pi5.php']);
}

?>
