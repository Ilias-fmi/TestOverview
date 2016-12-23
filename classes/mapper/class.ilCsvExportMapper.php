<?php

/* Dependencies : */
require_once 'Customizing/global/plugins/Services/Repository/RepositoryObject/TestOverview/classes/GUI/class.ilTestOverviewTableGUI.php';
require_once 'Customizing/global/plugins/Services/Repository/RepositoryObject/TestOverview/classes/mapper/class.ilOverviewMapper.php';
/**
 * The class creates a CSV file from the User Data that can be downloaded 
 */
class ilCsvExportMapper {
    
    /** @var type extended/reduced (TestQuestions) */
    var $type;

    var $overviewID;
    
    var $filename;
    /**
     *
     * @var testMap containing student-information for tests
     */
    var $testMap = array();

    /**
     * Constructor
     */
    public function __construct($type, $overviewID) {
        /**
         * instantiate the variables with form input
         */
        $this->type=$type;
        $this->overviewID = $overviewID;
        $date = time();
        
        $this->inst_id = IL_INST_ID;
        
        $this->subdir = $date."__".$type."_extov";
	$this->filename = $this->subdir;  
        
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
			case "reduced":
				return $this->buildReducedFile();
				break;
                        case "extended":
                                return $this->buildExtendedFile();
                                break;
                        
		}
    }
    
    
    function buildExtendedFile() {
        $data= $this->buildStudentMap();
        $rows= array();
        $datarow = array();
        $col = 1;
        array_push($datarow, $this->lng->txt("name"));
        array_push($datarow,$this->lng->txt("matriculation"));
        array_push($datarow,$this->lng->txt("email"));
        array_push($datarow,$this->lng->txt("gender"));
        
    }
    
    
    
    /**
     * 
     * @global type $ilDB
     * @param type $overviewID
     * @return array containing all testIDs associtated with the TO-Object
     */
    protected function getTestIDs() {
        global $ilDB;
        $testIDs=array();
        $query = "SELECT 
                  tst_tests.test_id
                  FROM
                  rep_robj_xtov_t2o
                  JOIN object_reference
                  JOIN tst_tests
                  ON (rep_robj_xtov_t2o.ref_id_test = object_reference.ref_id
                  AND obj_id = obj_fi)
                  WHERE obj_id_overview =".$ilDB->quote($this->overviewID, 'integer');
        
        $result= $ilDB->query($query);
        while ($record = $ilDB->fetchAssoc($result)){
            array_push($testIDs, $record);
        }
        return $testIDs;
    }
    
    
    protected function getTest($questionID) {
        global $ilDB;
        $query = "  SELECT 
                    DISTINCT test_fi
                    FROM
                    tst_test_question
                    WHERE question_fi=".$ilDB->quote($questionID, 'integer');
        
        $result= $ilDB->query($query);
        $record = $ilDB->fetchAssoc($result);
        return $record['test_fi'];
    }
    
    
    
    /**
     * 
     * @global type $ilDB
     * @param type $overviewID
     * @return array filled with all existing question-ids for a given XTOV-instance
     */
    private function getQuestionIDs()
    {
        global $ilDB;
        $qIDs = array();
        
        $query = "SELECT 
                  question_fi
                  FROM
                  rep_robj_xtov_t2o
                  JOIN object_reference
                  JOIN tst_tests
                  JOIN tst_test_question ON (rep_robj_xtov_t2o.ref_id_test = object_reference.ref_id
                  AND obj_id = obj_fi
                  AND test_id = test_fi)
                  WHERE obj_id_overview =".$ilDB->quote($this->overviewID, 'integer');
        $result= $ilDB->query($query);
        while ($record = $ilDB->fetchAssoc($result)){
            array_push($qIDs, $record);
        }
        return $qIDs;
    }
    
    
    /**
     * 
     * @global type $ilDB
     * @param type $userID
     * @param type $testID
     * @return string 
     */
    private function getActiveID($userID, $testID) {
        global $ilDB;
           
        $result = $ilDB->queryF("SELECT tst_active.active_id FROM tst_active WHERE user_fi = %s AND test_fi = %s",
			array('integer', 'integer'),
			array($userID, $testID));
        
        if ($result->numRows()) 
	{
            $row = $ilDB->fetchAssoc($result);
            return $row["active_id"];
	}else {
            return "";
        }
        
                
       
    }
    /**
     * 
     * @global type $ilDB
     * @param type $userID
     * @return type
     */
    private function getInfo($userID){
        global $ilDB;
        $info = array();
        $query = "SELECT lastname, firstname, matriculation, email
                  FROM
                  usr_data
                  WHERE (usr_id = '".$userID."')";
        $result = $ilDB->query($query);
        while ($record = $ilDB->fetchAssoc($result)){
            //array_push($info, $record);
            return $record;
        }
    }
    /**
     * 
     * @global type $ilDB
     * @param type $overviewID
     * @return array
     */
    
    public function getTestResultsForActiveId($active_id)
    {
	global $ilDB;   
	
	$query = "
		SELECT		*
		FROM		tst_result_cache
		WHERE		active_fi = %s
	";
	
	$result = $ilDB->queryF($query, 
                       array('integer'), array($active_id)
	);
		
		
		
	$row = $ilDB->fetchAssoc($result);
	
	return $row;
		
    }
    
    
    
    protected function getAssociation()
    {
      global $ilDB;
      $points = array();
      
        $query = "SELECT 
                  active_fi, questionId.question_fi, points
                  FROM
                  tst_test_result
                  JOIN
                      (SELECT 
                      question_fi
                      FROM
                      rep_robj_xtov_t2o
                      JOIN object_reference
                      JOIN tst_tests
                      JOIN tst_test_question ON (rep_robj_xtov_t2o.ref_id_test = object_reference.ref_id
                      AND obj_id = obj_fi
                      AND test_id = test_fi)) AS questionId ON (tst_test_result.question_fi = questionId.question_fi)
                      ORDER BY active_fi , questionId.question_fi";
      $result = $ilDB->query($query);
      $foundusers = array();
		/** @noinspection PhpAssignmentInConditionInspection */
		while ($row = $ilDB->fetchAssoc($result))
		{
			if (!array_key_exists($row["active_fi"], $foundusers))
			{
				$foundusers[$row["active_fi"]] = array();
			}
			array_push($foundusers[$row["active_fi"]], array( "qid" => $row["question_fi"], "points" => $row["points"]));
		}
        return $foundusers;
    }
    
    

    
    protected function getQuestionTitle($questionID)
    {
        global $ilDB;
        $titles = array();
        
        $query = "SELECT title FROM qpl_questions WHERE question_fi= ".$ilDB->quote($questionID, 'integer');
        
        $result = $ilDB->query($query);
        $record = $ilDB->fetchObject($result);
        return $record -> title;
        
    }
    /**
     * 
     * @global type $ilDB
     * @return array of all users that participated on tests added
     */
    protected function getUniqueTestUserID()
    {
        global $ilDB;
        $uniqueIDs = array();
        
        $query = "SELECT user_fi from tst_active JOIN 
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
            array_push($uniqueIDs, $record);
        }
        
        return $uniqueIDs;
    }

    /**
     * 
     * @param type $activeID
     * @param type $questionID
     * @param type $questionResultObject
     * @return int Points for a given activeID (TestID-StudentID) and a questionID
     */
    public function getMark ($activeID, $questionID , $questionResultObject){
            foreach ($questionResultObject as $row => $value){
                //echo "10 Punkte: $value->active_fi, $value->question_fi, $value->points\n";

                if ($value->active_fi == $activeID && $value->question_fi == $questionID ){

                    if ($value->points != null){
                        return $value->points;   
                    }
              
                }
            }
            return 0;
    }
    
    /**
     * 
     * @param type $overviewID#
     * Builds a map for retrieving user data 
     * Storage is like: array testMap = (Student1 => (Question1 => Points,
     *                                                Question2 => Points,...),
     *                                   Student2 => (Question1 => Points,
     *                                                Question2 => Points,...))
     *                                               
     */
    public function buildStudentMap(){
        $this->testMap=array(); 
        $resultObject= $this->getAssociation();
        $userArray= $this->getUniqueTestUserID();
        $questions= $this->getQuestionIDs();
        
        echo $this->testsQuestion;
        $userID;
        $questionID;
        
        //var_dump($userArray);
        var_dump($resultObject);
        //var_dump($questions);
        //var_dump($tests);
        //var_dump($userArray);
        if(empty($userArray))
            ilUtil::sendFailure("No user-results to export.");
        if(empty($questions))
            ilUtil::sendFailure("No questions to export.");
        
        
        //foreach($resultObject as $elem => $object){
            //$userID = $userFi['user_fi'];
            //$active_ID = $object['active_fi'];
            //$userInfo = $this->getInfo($userID); // Retrieve UserInfo (Lastname, Firstname, Email, Matriculation) for userID
            //$questionID = $object['question_fi'];
            //$testID = $this->getTest($questionID);
                    
            //$activeID = $this->getActiveID($userID, $testID);

            //$points = $object['points'];
            //$points = $this->getMark($activeID, $questionID, $resultObject); //Points for a given user on a given question
            //echo "UserID: $active_ID, QuestionID: $questionID, Punkte: $points\n";
                    
            
            
            
            //if (!array_key_exists($active_ID, $this->testMap)) {
            //    $this->testMap['active_fi']= array();
              //$this->testMap['active_fi'][]= ($questionID => $points); 
            //}
            //    array_push($this->testMap[$active_ID], array("question_id"=>$questionID, "points"=>$points));
            
            
        //}            
        
        
        //var_dump($this->testMap);
        //return $this->testMap;
    }
    
    
}
    

 ?>
