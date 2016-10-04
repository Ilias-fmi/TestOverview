<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */


/*
 *Class gets the Results in a String and Maps it to the Diagramms
 *@package	TestOverview repository plugin
 * 
 */

/* Dependencies : */
require_once 'Customizing/global/plugins/Services/Repository/RepositoryObject/TestOverview/classes/GUI/class.ilTestOverviewTableGUI.php';
require_once 'Customizing/global/plugins/Services/Repository/RepositoryObject/TestOverview/classes/mapper/class.ilOverviewMapper.php';

class BinDiagrammMapper 
    extends ilTestOverviewTableGUI{
    //Name+Results+Sum of Results for every Student as a String
    private $result = null;
    
    /*function __construct() {
      $this-> result= $this-> data();  
      if (result == null){
          /*Expetion Handling
           * TO DO
           */
      //}
    //}
    
    /*
     * Gets the Data from every Student and there Testresults in a String saperated by "|"
     * 
     */
    public function data(){
        
        
        global $lng, $ilCtrl, $ilUser;
        /*Initalise the Mapper*/
        $this-> setMapper(new ilOverviewMapper)
			  ->populate();
					
		$data = $this->getData();
		
		$this->tpl->addBlockFile("TBL_CONTENT", "tbl_content", 'tpl.test_overview_rows.html',
				$this->row_template_dir);
	
			foreach($data as $set)
			{
				$this->fillRow($set);
				$this->tpl->setCurrentBlock("tbl_content");
				$this->tpl->parseCurrentBlock();
			}
               
                      return $this->tpl->get();
                      
    }
    
    public function getResult(){
       
        return $result;
        
    }
    private function serperate(){
        
    }
    /* Test Methode für die Klasse Student*/
    public function testStudent(){
      $Obj = new Student ();
      $Obj->setResults("100.00 100.00 99.99");
      return $Obj-> getResults();
    }
    

}
/*
 * Object for every Student with Name and Results
 * 
 * 
 */
class Student{
    
    public $name = "";
    private $results= array();
    private $average = 0;
    /* Getter und Setter für Results und Average des Studenten*/
    public function getResults(){
        return $this-> results;
    }
    
    public function setResults($result){
        
        $array = explode(' ', $result);
         $temp = array();
        foreach ($array as $part){
        array_push($temp,(float)$part);
        }
       $this-> results=  $temp;
        
    }
    
    public function getAverage (){
        return $average;
    }
    
    
    
    
}

?>
