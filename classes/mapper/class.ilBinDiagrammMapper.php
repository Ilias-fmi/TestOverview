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
    
    public $students = array();
    private $rawData = array();
    
    
    public function createDia(){
        $this-> data();
        foreach ($this-> rawData as $student){
            $this-> seperate($student);
        }
       // return sizeof($this-> students);
        $averageObj = new AverageDiagramm($this-> students);
        return $averageObj-> initDia();
    }
    
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
               
                      //return $this->tpl->get();
                        $this-> splitStudent ($this->tpl-> get());
                      
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
    
    private $studentObject;
    
    public $diaPoints = array();
    
    private $buckets = array(0,0,0,4,0,3,6,0,0,0,0,0);
    
    function __construct($Obj) {
        $this-> studentObject = $Obj;
        $this-> getAverage();
    }
    
    function initDia(){
        require_once 'Services/Chart/classes/class.ilChartGrid.php';
        require_once 'Services/Chart/classes/class.ilChartLegend.php';
        require_once 'Services/Chart/classes/class.ilChartSpider.php';
        include_once 'Services/jQuery/classes/class.iljQueryUtil.php';
        $chart = ilChart::getInstanceByType(ilChart::TYPE_GRID, $a_id);
	$chart->setsize(700, 400);
        $data = $chart->getDataInstance(ilChartGrid::DATA_BARS);
	//$data->setLabel($this->lng->txt("category_nr_selected"));
	$data->setBarOptions(0.5, "center");
        $data->addPoint(1,$this-> buckets[0]);
        $data->addPoint(2,$this-> buckets[1]);
        $data->addPoint(3,$this-> buckets[2]);
        $data->addPoint(4,$this-> buckets[3]);
        $data->addPoint(5,$this-> buckets[4]);
        $data->addPoint(6,$this-> buckets[5]);
        $data->addPoint(7,$this-> buckets[6]);
        $data->addPoint(8,$this-> buckets[7]);
        $data->addPoint(9,$this-> buckets[8]);
        $data->addPoint(10,$this-> buckets[9]);
        $chart->addData($data);
        return $chart ->getHTML();
        
    }
    
    function addPoints($data){
        $data->addData(1,$this-> buckets[0]);
        $data->addData(2,$this-> buckets[1]);
        $data->addData(3,$this-> buckets[2]);
        $data->addData(4,$this-> buckets[3]);
        $data->addData(5,$this-> buckets[4]);
        $data->addData(6,$this-> buckets[5]);
        $data->addData(7,$this-> buckets[6]);
        $data->addData(8,$this-> buckets[7]);
        $data->addData(9,$this-> buckets[8]);
        $data->addData(10,$this-> buckets[9]);
        
        return $data;
    }
    
    function getAverage(){
        foreach ($this->studentObject  as $student){
            $this-> fillBuckets($student-> getAverage ());
        }
    }
    
    function fillBuckets($average){
        if ($average > 0.0 && $average <= 10.00  ){
            $this-> buckets[0] ++;
        }else if ($average > 10.00 && $average <= 20.00 ){
            $this-> buckets[1] ++;
        }else if ($average > 20.00 && $average <= 30.00 ){
            $this-> buckets[2] ++;
        }else if ($average > 30.00 && $average <= 40.00){
            $this-> buckets[3] ++;
        }else if ($average > 40.00 && $average <= 50.00){
            $this-> buckets[4] ++;
        }else if ($average > 50.00 && $average <= 60.00){
            $this-> buckets[5] ++;
        }else if ($average > 60.00 && $average <= 70.00){
            $this-> buckets[6] ++;
        }else if ($average > 70.00 && $average <= 80.00){
            $this-> buckets[7] ++;
        }else if ($average > 80.00 && $average <= 90.00){
            $this-> buckets[8] ++;
        }else if ($average > 90.00 && $average <= 100.00){
            $this-> buckets[9] ++;
        }else {
            //last index checks vor errors 
            $this-> buckets[10] ++;
            
        }
        
    }
}

?>
