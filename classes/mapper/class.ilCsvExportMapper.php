<?php

/**
 * The class creates a CSV file from the User Data that can be downloaded 
 */
class ilCsvExportMapper {
    
    /** @var type extended/reduced (TestQuestions) */
    var $type;
    /** @var integer ID of current TestOverview  */
    var $overviewID;
    
    
    var $filename;
      
    var $testIDs;
    
    var $exerciseIDs;
    
    var $users;
    
    var $assoc;
    
    /**
     * Constructor
     */
    public function __construct($parentObject, $type) {
        /**
         * instantiate the variables with form input
         */
        $this->type=$type;
        $this->overviewID = $parentObject->object->getID();
        $date = time();
        
        $this->testIDs = $this->getTestIDs();
        $this->exerciseIDs = $this->getUniqueExerciseIDs();
        $this->users = $this->concatUsers();
        $this->assoc = $this->getAssociation();
        
        $this->inst_id = IL_INST_ID;
        
        $this->subdir = $date."_".$type."_extov";
	$this->filename = $this->subdir.".csv";  
        
    }
    

    function buildExportFile()
    {       
                if(empty($this->users))
                ilUtil::sendFailure("No user-results to export.", true);
                
                if(empty($this->testIDs))
                ilUtil::sendFailure("No tests to export.",true);
                
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
    
    /**
     * 
     * @private function, builds reduced Export file (Only TestNames, no Questions)
     *  (lastname|firstname|matriculation-number|email|gender|Testname1|Testname2|...)

     */
    private function buildReducedFile() {
        global $lng;
        $rows= array();
        $datarow = array();
        //build headrow
        array_push($datarow, "name");
        array_push($datarow,"matriculation");
        array_push($datarow,"email");
        array_push($datarow,"gender");
        //push all TestNames into the headrow
        foreach ($this->testIDs as $key => $value) {
            $testID = $value['test_id'];
            $testName = $this->getTestTitle($testID);
            array_push($datarow, $testName);
        }
        //push a name of every exercise into headrow
        foreach ($this->exerciseIDs as $num => $exvalue) {
            $exerciseID = $exvalue['obj_id'];
            
            $exName = $this->getExerciseName($exerciseID);
            echo $exName;
            array_push($datarow, $exName);
        }
        
        $headrow= $datarow;
        array_push($rows, $headrow);
        //push user specific info into every row (userInfo)
        foreach ($this->users as $num => $preValue) {
            $userID = $preValue['user_fi'];
            $datarow = $headrow;
            $datarow2 = array();
            $userInfo = $this->getInfo($userID);
            array_push($datarow2, $userInfo['lastname'].', '.$userInfo['firstname']); 
            array_push($datarow2, $userInfo['matriculation']);
            array_push($datarow2, $userInfo['email']);
            array_push($datarow2, $userInfo['gender']);
            //push test results into user row
            foreach ($this->testIDs as $num2 => $preValue2) {
                $testID = $preValue2['test_id'];
                $activeID = $this->getActiveID($userID, $testID);
                $testResult = $this ->getTestResultsForActiveId($activeID);
                array_push($datarow2, $testResult);
            }
            //push exerciseResult into user row
            foreach ($this->exerciseIDs as $num3 => $preValue3) {
                $exerciseID = $preValue3['obj_id'];
                $exerciseResult = $this->getExerciseMark($userID, $exerciseID);
                array_push($datarow2, $exerciseResult);
            }
            array_push($rows,$datarow2);
            
        }
        
        $csv = "";
        $separator = ";";
	foreach ($rows as $evalrow)
	{
		$csvrow = $this->processCSVRow($evalrow, TRUE, $separator);
		$csv .= join($csvrow, $separator) . "\n";
                //var_dump($evalrow);
	}
        ilUtil::deliverData($csv, ilUtil::getASCIIFilename($this->filename));
        
        
    }
    
    
    
    /**
     * 
     * @private function, builds extended export file (TestNames and Questions)
     *  (lastname|firstname|matriculation-number|email|gender|Testname1|Question1|Question2|..|Testname2|Question1|Question2|..)

     */
    function buildExtendedFile() {
        global $lng;
        $rows= array();
        $datarow = array();
        //build headrow        
        array_push($datarow,"name");
        array_push($datarow,"matriculation");
        array_push($datarow,"email");
        array_push($datarow,"gender");
        //push all TestNames into the headrow
        foreach ($this->testIDs as $key => $value) {
            $testID = $value['test_id'];
            $testName = $this->getTestTitle($testID);
            array_push($datarow, $testName);
            $questionIDs = $this->getQuestionID($testID);
            $qCounter = 1;
            //push a name for every subtask a test has into the headrow
            foreach ($questionIDs as $key => $questionInfo) {
                $questionID = $questionInfo['question_fi'];
                //$questionName = $this->getQuestionTitle($questionID);
                $questionName = "Teilaufgabe ".$qCounter;
                array_push($datarow, $questionName);
                $qCounter++;
            }
        }
        //push a name of every exercise into headrow
        foreach ($this->exerciseIDs as $num => $exvalue) {
            $exerciseID = $exvalue['obj_id'];
            $exName = $this->getExerciseName($exerciseID);
            array_push($datarow, $exName);
            $assignmentIDs = $this->getAssignments($exerciseID);
            $aCounter = 1;
            //push a name for every assignments a exercise has into the headrow
            foreach ($assignmentIDs as $key => $assignmentInfo) {
                $assignmentIDs = $assignmentInfo['id'];
                $assignmentName = "Assignment ".$aCounter;
                array_push($datarow, $assignmentName);
                $aCounter++;
            }
        }
        
        $headrow= $datarow;
        array_push($rows, $headrow);
        //push user specific info into every row (userInfo)
        foreach ($this->users as $num => $preValue) {
            $userID = $preValue['user_fi'];
            $datarow = $headrow;
            $datarow2 = array();
            $userInfo = $this->getInfo($userID);
            array_push($datarow2, $userInfo['lastname'].', '.$userInfo['firstname']);    
            array_push($datarow2, $userInfo['matriculation']);
            array_push($datarow2, $userInfo['email']);
            array_push($datarow2, $userInfo['gender']);
            //push test results into user row
            foreach ($this->testIDs as $num2 => $preValue2) {
                $testID = $preValue2['test_id'];
                $activeID = $this->getActiveID($userID, $testID);
                $testResult = $this ->getTestResultsForActiveId($activeID);
                array_push($datarow2, $testResult);
                $questionIDs2 = $this->getQuestionID($testID);
                //push question results into user row
                foreach ($questionIDs2 as $key => $value) {
                    $questionID2 = $value['question_fi'];
                    $questionPoints = $this->getMark($activeID, $questionID2);
                    array_push($datarow2, $questionPoints);
                }
            }
            
            //push exerciseResult into user row
            foreach ($this->exerciseIDs as $num3 => $preValue3) {
                $exerciseID = $preValue3['obj_id'];
                $exerciseResult = $this->getExerciseMark($userID, $exerciseID);
                array_push($datarow2, $exerciseResult);
                $assignmentIDs = $this->getAssignments($exerciseID);
                //push assignmentResults into user row
                foreach ($assignmentIDs as $key => $preValue4) {
                   $assignmentIDs = $preValue4['id'];
                   $assignmentPoints = $this->getAssignmentMark($userID, $assignmentIDs);
                   array_push($datarow2, $assignmentPoints);
                }
            }
            array_push($rows,$datarow2);
            $datarow2 = array();
        }
        
        
        
        $csv = "";
        $separator = ";";
	foreach ($rows as $evalrow)
	{
		$csvrow = $this->processCSVRow($evalrow, TRUE, $separator);
		$csv .= join($csvrow, $separator) . "\n";
                
	}
        ilUtil::deliverData($csv, ilUtil::getASCIIFilename($this->filename));
        
    }
    
    
    function processCSVRow($row, $quoteAll = FALSE, $separator = ";")
	{
		$resultarray = array();
		foreach ($row as $rowindex => $entry)
		{
			$surround = FALSE;
			if ($quoteAll)
			{
				$surround = TRUE;
			}
			if (strpos($entry, "\"") !== FALSE)
			{
				$entry = str_replace("\"", "\"\"", $entry);
				$surround = TRUE;
			}
			if (strpos($entry, $separator) !== FALSE)
			{
				$surround = TRUE;
			}
			// replace all CR LF with LF (for Excel for Windows compatibility
			$entry = str_replace(chr(13).chr(10), chr(10), $entry);

			if ($surround)
			{
			    $entry = "\"" . $entry . "\"";
			}

			$resultarray[$rowindex] = $entry;
		}
		return $resultarray;
	}
    
    /**
     * 
     * @global type $ilDB
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
    
    /**
     * 
     * @global type $ilDB
     * @param type $questionID
     * @return testID for a given questionID
     */
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
    private function getQuestionID($testID)
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
                  WHERE obj_id_overview = %s AND test_id = %s";
        $result= $ilDB->queryF($query, 
                               array('integer', 'integer'),
                               array($this->overviewID, $testID));
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
        $query = "SELECT lastname, firstname, matriculation, email, gender
                  FROM
                  usr_data
                  WHERE (usr_id = '".$userID."')";
        $result = $ilDB->query($query);
        $record = $ilDB->fetchAssoc($result);
            //array_push($info, $record);
        return $record;
        
    }
    /**
     * 
     * @global type $ilDB
     * @param type $overviewID
     * @return integer Reached points in a test for a given activeID
     */
    
    public function getTestResultsForActiveId($active_id)
    {
	global $ilDB;   
	
	$query = "
		SELECT		reached_points
		FROM		tst_result_cache
		WHERE		active_fi = %s
	";
	
	$result = $ilDB->queryF($query, 
                       array('integer'), array($active_id)
	);
		
		
		
	$row = $ilDB->fetchAssoc($result);
	
	return $row['reached_points'];
		
    }
    
    
    /**
     * 
     * @global type $ilDB
     * @return array with all answered questions, the reached points for every activeID
     *          activeID => (QuestionID->points)
     */
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
                      ORDER BY active_fi ASC, questionId.question_fi ASC, tstamp DESC";
        $result = $ilDB->query($query);
        
        while ($record = $ilDB->fetchObject($result)){
            array_push($points, $record);
        }
        return $points;
    }
    
    /**
     * 
     * @global type $ilDB
     * @param type $testID
     * @return String TestTitle for given testID
     */
    protected function getTestTitle($testID) {
        global $ilDB;
        
        
        $query = "SELECT 
                  od.title
                  FROM
                  rep_robj_xtov_t2o
                  INNER JOIN
                  object_reference ref ON (ref.ref_id = rep_robj_xtov_t2o.ref_id_test
                  AND deleted IS NULL)
                  INNER JOIN
                  object_data od ON (od.obj_id = ref.obj_id)
                  AND od.type = 'tst'
                  INNER JOIN
                  tst_tests test ON (test.obj_fi = od.obj_id)
                  WHERE test_id = %s";
        $result= $ilDB->queryF($query, 
                               array('integer'),
                               array($testID));
        $record = $ilDB->fetchAssoc($result);
        
        return $record['title']; 
    }

    /**
     * 
     * @global type $ilDB
     * @param type $questionID
     * @return Name of Question
     */
    protected function getQuestionTitle($questionID)
    {
        global $ilDB;
        
        
        $query = "SELECT title FROM qpl_questions WHERE question_id= %s";
        
        $result = $ilDB->queryF($query, array('integer'), array($questionID));
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
        
        $query = "SELECT DISTINCT(user_fi) 
                  FROM tst_active 
                  JOIN 
                 (SELECT 
                    *
                   FROM
                rep_robj_xtov_t2o
                   JOIN
                object_reference
                   JOIN
                tst_tests ON (rep_robj_xtov_t2o.ref_id_test = object_reference.ref_id
                AND obj_id = obj_fi)) as TestUsers ON 
                (TestUsers.test_id=tst_active.test_fi)
                WHERE obj_id_overview = %s";
        $result= $ilDB->queryF($query, 
                               array('integer'),
                               array($this->overviewID));
       
        
        
        while ($record = $ilDB->fetchAssoc($result)){
            array_push($uniqueIDs, $record);
        }
        
        return $uniqueIDs;
    }
    
    
    
    

    /**
     * 
     * @param type $activeID
     * @param type $questionID
     * @return int Points for a given activeID (TestID-StudentID) and a questionID
     */
    public function getMark ($activeID, $questionID){
            foreach ($this->assoc as $row => $value){
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
     * @global type $ilDB
     * @return array of unique ExerciseIDs added to TO-Object 
     */
    protected function getUniqueExerciseIDs(){
        
        global $ilDB;
        $uniqueIDs = array();
        
        $query = "SELECT obj_id_exercise AS obj_id from rep_robj_xtov_e2o WHERE obj_id_overview = %s";
        $result = $ilDB->queryF($query, 
                               array('integer'),
                               array($this->overviewID));
        
        while ($record = $ilDB->fetchAssoc($result)){
            array_push($uniqueIDs, $record);
        }
        
        return $uniqueIDs;
        
    }
    
    /**
     * Returns the name of a Exercise 
     */
    public function getExerciseName($exerciseID) {
        global $ilDB;
        $query = "SELECT title FROM object_data WHERE obj_id = %s";
        $result = $ilDB->queryF($query, 
                        array('integer'),
                        array($exerciseID));
        $record = $ilDB->fetchAssoc($result);
        return $record['title'];
    }
    /**
     * 
     * @global type $ilDB
     * @return array with all userId that participated on Exercises in to object
     */
    protected function getUniqueExerciseUserID() {
        
        global $ilDB;
        $uniqueIDs = array();
        
        $query = "SELECT DISTINCT
                 (exc_mem_ass_status.usr_id)
                 FROM
                 rep_robj_xtov_e2o,
                 exc_returned,
                 exc_mem_ass_status
                 JOIN
                 usr_data ON (exc_mem_ass_status.usr_id = usr_data.usr_id)
                 WHERE
                 exc_returned.ass_id = exc_mem_ass_status.ass_id
                 AND user_id = exc_mem_ass_status.usr_id
                 AND obj_id_exercise = obj_id
                 AND obj_id_overview = %s ";
    
        $result = $ilDB->queryF($query, 
                               array('integer'),
                               array($this->overviewID));
        
        while ($record = $ilDB->fetchAssoc($result)){
            array_push($uniqueIDs, $record);
        }
        
        return $uniqueIDs;
    }
    
    /* Concatenate all Test and Exercise users */
    public function concatUsers() {
        $testUsers = $this->getUniqueTestUserID();
        $exerciseUsers = $this->getUniqueExerciseUserID();
        $allUsers = array_unique(array_merge($testUsers, $exerciseUsers));
        
        return $allUsers;
    }
    /* Returns all Assignments for a given excerciseID */
    public function getAssignments($exerciseID){
        global $ilDB;
        $assignmentID = array();
        
        $query = "SELECT 
                  id
                  FROM
                  exc_assignment
                  WHERE
                  exc_id = %s ";
    
        $result = $ilDB->queryF($query, 
                               array('integer'),
                               array($exerciseID));
        
        while ($record = $ilDB->fetchAssoc($result)){
            array_push($assignmentID, $record);
        }
        
        return $assignmentID;
    }
    /* Returns the exercise mark for a given userID and exerciseID  */
    protected function getExerciseMark($userID, $exerciseID) {
        global $ilDB;
                                
        $query = "SELECT mark FROM ut_lp_marks WHERE obj_id = %s AND usr_id = %s ";
    
        $result = $ilDB->queryF($query, 
                               array('integer', 'integer'),
                               array($exerciseID, $userID));
        
       $record = $ilDB->fetchAssoc($result);
            
        
       return $record['mark'];
    }
    /* Returns the assignment mark for a given userID and assignmentID  */
    protected function getAssignmentMark($userID, $assignmentID) {
        global $ilDB;

        
        $query = "SELECT mark FROM exc_mem_ass_status WHERE ass_id = %s AND usr_id = %s";
    
        $result = $ilDB->queryF($query, 
                               array('integer', 'integer'),
                               array($assignmentID, $userID));
        
        $record = $ilDB->fetchAssoc($result);
        
        return $record['mark'];
    }
}

 ?>
