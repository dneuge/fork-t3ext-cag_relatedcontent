<?php

	class cag_relatedcontent_utils {

        function getConfValue(&$self, $confKey, $default = null) {
            // Try Flexforms
            $tmp = trim($self->pi_getFFvalue($self->cObj->data['pi_flexform'],  $confKey, "sDEF"));
            if (strlen($tmp) > 0)
                return trim($self->pi_getFFvalue($self->cObj->data['pi_flexform'],  $confKey, "sDEF"));

            // Try Conf	
            $tmp = explode('.', $confKey);
            $cur = $self->conf;
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

        function loadTemplate(&$self, $templateFile = null) {
            if ($templateFile == null)
                $templateFile = $self->getConfValue('template', 'EXT:' . $self->extKey . '/templateFile.html');
            return $self->cObj->fileResource($templateFile);	
        }

        function imageProcessing(&$self, &$ts, $path) {
            if (substr($path, 0, 1) == "/")
                $path = substr($path, 1);
            $ts['file'] = $path;
            return $self->cObj->IMAGE($ts);	
        }

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

        function buildUri(&$self, $params, $page = "") {
            $uri = (is_numeric($page)) ? $self->pi_getPageLink($page) : $page;
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

        function table(&$self, &$template, &$data, $columns = "", $tableMarkerName = "TABLE", $start = 0, $limit = 10000) {
            if (!isset($data))
              return $self->cObj->substituteSubpart($template, '###' . $tableMarkerName . '###', "", 1);
               
            $subpartTable = $self->cObj->getSubpart($template, '###' . $tableMarkerName . '###');
            $subpartTable = $self->cObj->substituteSubpart($subpartTable, "###NO_RESULT###", "");
            
            $subpartRows = $self->cObj->getSubpart($subpartTable, '###ROWS###');
            $subpartRow = $self->cObj->getSubpart($subpartRows, '###ROW###');
            $rows = "";
            $i = 0;

            if (is_array($columns)) {
                foreach ($data as $rowId => $dataRow) {
                  if ($i++ < $start) continue;
                  if ($i > ($start + $limit)) break;
                  $row = $subpartRow;
                  foreach ($dataRow as $columnName => $columnContent) {
                      $row = $self->cObj->substituteMarker($row, "###" . strtoupper($columnName) . "###", $columnContent);
                  }
                  $rows .= $self->cObj->substituteSubpart($subpartRows, "###ROW###", $row);
                }					
            } else {
                foreach ($data as $rowId => $dataRow) {
                  if ($i++ < $start) continue;
                  if ($i > ($start + $limit)) break;
                  $row = $subpartRow;
                    if (is_array($dataRow))
                      foreach ($dataRow as $columnName => $columnContent) {
                          $row = $self->cObj->substituteMarker($row, '###'. strtoupper($columnName) . '###', $columnContent);
                      }
                    else
                        $row = $dataRow;
                $rows .= $self->cObj->substituteSubpart($subpartRows, "###ROW###", $row);
                }
            }

            // $subpartRows = $self->cObj->substituteSubpart($subpartRows, "###ROWS###", $rows, 1);
            $subpartTable = $self->cObj->substituteSubpart($subpartTable, "###ROWS###", $rows, 1);
            $template = $self->cObj->substituteSubpart($template, "###" . $tableMarkerName . "###", $subpartTable, 1);
            return $template;
        }

        function column2List (&$self, &$in, $columnName, $delimiter = ",", $quoted = false) {
            $list = '';
            foreach ($in as $row) {
                $list .= ($quoted ? "'" : "") . $row[$columnName] . ($quoted ? "'" : "") . $delimiter;
            }

            if (strlen($list) > 0)
                return substr($list, 0, strlen($list) - strlen($delimiter));
            return $list;
        }


	}

?>
