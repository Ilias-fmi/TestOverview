<?php

/* Dependencies : */
require_once 'Customizing/global/plugins/Services/Repository/RepositoryObject/TestOverview/classes/GUI/class.ilTestOverviewTableGUI.php';
require_once 'Customizing/global/plugins/Services/Repository/RepositoryObject/TestOverview/classes/mapper/class.ilOverviewMapper.php';
/**
 * The class creates a CSV file from the User Data that can be Downloaded 
 */
class ilCsvExportMapper {
    
    /** @var type Exercise/TestOverview */
    var $type;
    
    /** @var gender */
    var $gender;
    
    var $filter = array();
    
    var $format;
    
    var $filename;
    /**
    
     * @var ilDatabase
     */
    var $ilDB;
    
    
    /**
     * Constructor
     */
    public function __construct($type, $gender, $filter, $format) {
        /**
         * instantiate the variables with form input
         */
        $this->type=$type;
        $this->filter=$filter;
        $this->gender=$gender;
        $this->format=$format;
        
        $date = time();
        
        $this->inst_id = IL_INST_ID;
        
        $this->subdir = $date."__".$this->inst_id."__".$this->getType();
	$this->filename = $this->subdir.$this->getFormat();        
        
    }
    
    /**
     * @return string chosen type 
     */
    function getType(){
        switch ($this->type) {
            case "eo":
                return "eo"; break;
            case "to":
                return "to"; break;
         
        }
    }
    /** 
     * @return chosen format
     */
    function getFormat(){
        switch ($this->format) {
            case "CSV":
                return "csv"; break;

            case "XML":
                return "xml"; break;
        }
    }
    /**
     * @return string chosen gender
     */
    function getGender()
    {
        switch ($this->gender) {
            case "m":
                return "male"; break;
            case "f":
                return "female"; break;
            default:
                
                break;
        }
        
    }
    
    /**
     * Returns the directory where the export files are saved
     * @return string 
     */
    function getExportDir() 
    {
        include_once "./Services/Utilities/classes/class.ilUtil.php";
        $export_dir = ilUtil::getDataDir()."/xtov_data"."/to_"."/export";
        
        return $export_dir;
    }
    
    function buildExportFile()
    {
		switch ($this->type)
		{
			case "to":
				return $this->buildExportCSVFile();
				break;
                        case "eo":
				return $this->buildExportFileXML();
				break;
		}
    }
    
    function buildExportCSVFile() {
        
    }
    
    function getPointsSet($overviewID)
    {
      global $ilDB;
      $points = array();
      
      $query = 'select active_fi, questionId.question_fi, points from tst_test_result join
                (select question_fi from rep_robj_xtov_t2o join object_reference join tst_tests join tst_test_question on 
                (rep_robj_xtov_t2o.ref_id_test = object_reference.ref_id and obj_id = obj_fi and test_id = test_fi)) as questionId
                on (tst_test_result.question_fi = questionId.question_fi)
                order by  active_fi, questionId.question_fi';
      $result = $ilDB->query($query);
      while ($record = $ilDB->fetchObject($result)) {
          array_push($points, $record);
      }
      return $points;
    }
    
    function getQuestionTitle($questionID)
    {
        global $ilDB;
        $titles = array();
        
        $query = "select title from  qpl_questions where question_fi= '".$questionID."'";
        
        $result = $ilDB->query($query);
        $record = $ilDB->fetchObject($result);
        return $record -> title;
        
    }
    function getUniqueTests($overviewID){
        global $ilDB;
        $testID = array();
        
        $query = 'select ';
    }
    function getUniqueUserID($overviewID)
    {
        global $ilDB;
        $uniqueIDs = array();
        
        $query = "Select DISTINCT(exc_mem_ass_status.usr_id) from rep_robj_xtov_e2o  ,exc_returned , exc_mem_ass_status  join usr_data  on (exc_mem_ass_status.usr_id = usr_data.usr_id)
                where exc_returned.ass_id = exc_mem_ass_status.ass_id 
                And user_id = exc_mem_ass_status.usr_id and obj_id_exercise = obj_id 
                and obj_id_overview = '" .$overviewID ."' order by exc_mem_ass_status.usr_id";
        $result = $ilDB->query($query);
        
        while ($record = $ilDB->fetchObject($result)){
           array_push($UniqueIDs,$record->usr_id);  
        }
        
        return $UniqueIDs;
    }
    /**
     * 
     * @param type $overviewID
     * @return array Is an Array of array 
     * the inner arrays have as the first Element the studID, after that are all Marks for the tests
     */
    public function buildMatrix ($overviewID){
        
        $DbObject = $this-> getArrayofObjects($overviewID);
        $tests = $this-> getUniqueExerciseId ($overviewID);
        $users = $this-> getUniqueUserId ($overviewID);
        $outerArray = array ();
        for ($i = 0; $i < count($users); $i++){
            $innerArray = array();
            $user = $this-> getStudName($users[$i]);
            array_push($innerArray,$user);
            for ($j = 0; $j < count($tests); $j++){
                $mark = $this-> getMark ($users[$i] , $tests[$j],$DbObject);
                if ($mark > 0){
                    array_push($innerArray, $mark);
                }else {
                    array_push($innerArray,0);
                }
            }
         array_push($outerArray,$innerArray);   
            
        }
        
        return $outerArray;
            
            
    }
    
    
}
    

 ?>
