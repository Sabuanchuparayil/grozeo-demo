<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of simpleExcelWriter
 *
 * @author jijutm
 */
class simpleExcelWriter
{
    public $totalFields;
    public $exportFile;
    public $writeFile;
    
    private $totalValues;
    
    public function __construct(){
        $this->totalFields = false;
        $this->totalValues = false;
        $this->exportFile = 'export.xls';
	$this->writeFile = false;
    }

    public function output($query, $heads, $fields, $file = false){
	$this->writeFile = $file;
	$this->export($query, $heads, $fields);
    }

    public function export($query, $heads, $fields){
        global $db;
        $this->checkTotalValues();
        $rs = $db->query($query);
       // print_r($rs);exit();
                
        if($db->num_rows($rs) > 0 ){
	  if($this->writeFile !== false){
	     ob_start();
	  }
          $this->header($heads);
          while ($row = $db->fetch_array($rs)) {
            echo '<tr>';
            foreach($fields as $key){
               $this->cell($row[$key]);
               $this->updateTotalValues($key, $row[$key]);
            }
            echo '</tr>';
          }
          $this->showTotals($fields);
          $this->footer();
	  if($this->writeFile !== false){
	     $xls = ob_get_clean();
	     file_put_contents($this->writeFile, $xls);
	  }
          $db->clearResult($rs); 
       }
    }
    
    public function exportFromArray($queryResult, $heads, $fields){
        global $db;
        $this->checkTotalValues();
        $rs = $queryResult;
        print_r($rs);exit();
        if($db->num_rows($rs) > 0 ){
	  if($this->writeFile !== false){
	     ob_start();
	  }
          $this->header($heads);
          while ($row = $db->fetch_array($rs)) {
            echo '<tr>';
            foreach($fields as $key){
               $this->cell($row[$key]);
               $this->updateTotalValues($key, $row[$key]);
            }
            echo '</tr>';
          }
          $this->showTotals($fields);
          $this->footer();
	  if($this->writeFile !== false){
	     $xls = ob_get_clean();
	     file_put_contents($this->writeFile, $xls);
	  }
          $db->clearResult($rs); 
       }
    }
    
    private function showTotals($fields){
        if(empty($this->totalFields)){
            return;
        }
        echo '<tr>';
        foreach($fields as $key){
            $total = (isset($this->totalValues[$key]) && !empty($this->totalValues[$key])) ? $this->totalValues[$key] : '';
            $this->cell($total);
        }
        echo '</tr>';
    }
    
    private function updateTotalValues($key, $value){
        if(!isset($this->totalValues[$key])){
            return;
        }else{
            $this->totalValues[$key] += $value;
        }
    }
    
    private function checkTotalValues(){
        if(empty($this->totalFields)){
            return;
        }
        $totalFields = (!is_array($this->totalFields)) ? array($this->totalFields) : $this->totalFields ;
        foreach($totalFields as $key){
            $this->totalValues[$key] = 0;
        }
    }
    
    private function cell($val){
        echo '<td>', $val, '</td>';
    }
    
    private function header($heads){
        $wks = basename($this->exportFile, '.xls');
	if($this->writeFile === false){
           header("Content-type: application/vnd.ms-excel");
           header("Content-disposition: attachment; filename=\"".$this->exportFile."\"");
	}
        echo '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel"',
            'xmlns="http://www.w3.org/TR/REC-html40"><head><!--[if gte mso 9]><xml><x:ExcelWorkbook><x:ExcelWorksheets>',
            '<x:ExcelWorksheet><x:Name>'.$wks.'</x:Name><x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions>',
            '</x:ExcelWorksheet></x:ExcelWorksheets></x:ExcelWorkbook></xml><![endif]--></head><body><table>';
        echo '<thead><tr><td>', join('</td><td>', $heads), '</td></tr></thead>';
    }
    
    private function footer(){
        echo '</table></body></html>';
    }
}
