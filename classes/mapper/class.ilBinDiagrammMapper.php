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
    private $rawData = array();
    public $students = array();
    
    
    /*function __construct() {
      $this-> result= $this-> data();  
      if (result == null){
          /*Expetion Handling
           * TO DO
           */
      //}
    //}
    
    public function buildDiagramm(){
        return $this->data();
       // $this-> splitStudent($this-> data());
        //foreach($this->rawData as $student){
          //  $this-> seperate($student);
        }
        
    }
    
    /*
     * Gets the Data from every Student and there Testresults in a String saperated by "|"
     * 
     */
    public function data(){
        $this-> setMapper(new ilOverviewMapper)
			  ->populate();
        global $lng, $ilCtrl, $ilUser;
					
		$data = $this->getData();
		
		// fill rows
		

			$this->tpl->addBlockFile("TBL_CONTENT", "tbl_content", 'tpl.test_overview_rows.html',
				$this->row_template_dir);
	
             
			foreach($data as $set)
			{
				$this->fillRow($set);
				$this->tpl->setCurrentBlock("tbl_content");
				$this->tpl->parseCurrentBlock();
			}
               
		//return $this->render();
                        return $this->render();
    }

    
	function render()
	{
		global $lng, $ilCtrl;

		return $this->tpl->get();
	}
    public function getResult(){
       
        return $result;
        
    }
    /**
     * Splits the String by the | so that seperate can Create an Obj. of every Student
     * @param type $string
     */
    private function splitStudent($string){
        $this-> rawData =  explode("|",$string);
        
    }
    
    /*
     * Splits the Sting into Name/Results/Average
     * @return Students Object with Name/Results/Average set
     */
    private function seperate($string){
        $string = str_replace("%","",$string);
        $temp = explode('#', $string);
        $name = $temp[0];
        $temp = explode('§',$temp[1]);
        $average = $temp[1];
        $results = $temp[0];
        $student = new Student ();
        $student-> setName($name);
        $student-> setResults($results);
        $student-> setAverage($average);
        $this->students[] = $student; 
       //return $student;
            
        
    }
    /* Test Methode für die Klasse Student*/
    public function testStudent(){
     $jo = $this-> seperate("jo # 100.00% 66.66% § 22.22%");
     return $jo ;
    }
    

}
/*
 * Object for every Student with Name and Results
 * 
 * 
 */
class Student{
    
    private $name = "";
    private $results= array();
    private $average = 0.0;

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
        return $this-> average;
    }
    
    public function setAverage ($average){
        $this-> average = (float)$average;
    }
    
    public function getName (){
        return $this-> name;
    }
   public function setName ($name){
       $this-> name = $name;
       
   }
    
    
    
    
}

class AverageDiagramm{
    
    public $diagrammObject;
    
    public $diaPoints = array();
    
    function __construct($Obj) {
        $this-> diagrammObject = $Obj;
    }
    
    function initDia(){
        $chart = ilChart::getInstanceByType(ilChart::TYPE_GRID, $a_id);
	$chart->setsize(700, 400);
        $data = $chart->getDataInstance(ilChartGrid::DATA_BARS);
	$data->setLabel($this->lng->txt("Average"));
	$data->setBarOptions(0.5, "center");
        $data = $this-> addPoints($data);
        $chart->addData($data);
        return $chart ->getHTML();
    }
    
    function addPoints($data){
        
        $i= 0;
        
        foreach ($points as $point){
        $data-> addPoint(0,$point);
        $i++;
        }
        return $data;
    }
    function 
    
}

?>
