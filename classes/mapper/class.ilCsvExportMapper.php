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
    
    var $testMap;
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
                        
		}
    }
    
    function buildExportCSVFile() {
        
    }
    
    private function getInfo($userID){
        global $ilDB;
        $info = array();
        $query = "SELECT lastname, firstname, matriculation, email
                  FROM
                  usr_data
                  WHERE (usr_id = ".$userID.")";
        $result = $ilDB->query($query);
        while ($record = $ilDB->fetchObject($result)){
            array_push($info, $record);
        }
        
        return $info;
    }
    /**
     * 
     * @global type $ilDB
     * @param type $overviewID
     * @return array
     */
    protected function getPoints($overviewID)
    {
      global $ilDB;
      $points = array();
      
      $query = 'select active_fi, questionId.question_fi, points from tst_test_result join
                (select question_fi from rep_robj_xtov_t2o join object_reference join tst_tests join tst_test_question on 
                (rep_robj_xtov_t2o.ref_id_test = object_reference.ref_id and obj_id = obj_fi and test_id = test_fi)) as questionId
                on (tst_test_result.question_fi = questionId.question_fi)
                order by  active_fi, questionId.question_fi';
      $result = $ilDB->query($query);
      while ($record = $ilDB->fetchAssoc($result)) {
          array_push($points, $record);
      }
      return $points;
    }
    /**
     * 
     * @global type $ilDB
     * @param type $overviewID
     * @return array filled with all existing question-ids for a given XTOV-instance
     */
    protected function getQuestionIDs($overviewID)
    {
        global $ilDB;
        $ids = array();
        
        $query = "SELECT question_fi FROM rep_robj_xtov_t2o JOIN object_reference JOIN tst_tests JOIN tst_test_question ON 
                    (rep_robj_xtov_t2o.ref_id_test = object_reference.ref_id AND obj_id = obj_fi AND test_id = test_fi)";
        
        $result= $ilDB->query($query);
        while ($record = $ilDB->fetchAssoc($result)){
            array_push($ids, $record);
        }
        return $ids;
    }
    
    protected function getQuestionTitle($questionID)
    {
        global $ilDB;
        $titles = array();
        
        $query = "select title from qpl_questions where question_fi= '".$questionID."'";
        
        $result = $ilDB->query($query);
        $record = $ilDB->fetchObject($result);
        return $record -> title;
        
    }
    
    protected function getUniqueUserID($overviewID)
    {
        global $ilDB;
        $this->uniqueIDs = array();
        
        $query = "select DISTINCT(user_fi) from tst_active JOIN 
                 (SELECT 
                    *
                   FROM
                rep_robj_xtov_t2o
                   JOIN
                object_reference
                   JOIN
                tst_tests ON (rep_robj_xtov_t2o.ref_id_test = object_reference.ref_id
                AND obj_id = obj_fi)) as TestUsers ON 
                (TestUsers.test_id=tst_active.test_fi)";
        $result = $ilDB->query($query);
        
        while ($record = $ilDB->fetchAssoc($result)){
           array_push($uniqueIDs,$record);  
        }
        
        return $uniqueIDs;
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
    
    public function buildHashMap($overviewID){
        $this->testMap=array(); 
        $user= $this->getUniqueUserID($overviewID);
        $questions = $this->getQuestionIDs($overviewID);
        if(!empty($user)&&!empty(questions)){
        
        for ($i = 0; $i < count($user); $i++){              //jeden User als Key in array stecken
            $userInfo = $this->getInfo($user[i]);
            array_push($this->testMap, $userInfo);
                for ($j = 0; $j<count($questions);$j++){
                
                }
            //jedenUser als Key in array stecken - Student => (Questions => Students Punkte)
        }
        }
    }
    
    
}
    

 ?>
