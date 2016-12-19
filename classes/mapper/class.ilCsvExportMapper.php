<?php

/* Dependencies : */
require_once 'Customizing/global/plugins/Services/Repository/RepositoryObject/TestOverview/classes/GUI/class.ilTestOverviewTableGUI.php';
require_once 'Customizing/global/plugins/Services/Repository/RepositoryObject/TestOverview/classes/mapper/class.ilOverviewMapper.php';
/**
 * The class creates a CSV file from the User Data that can be downloaded 
 */
class ilCsvExportMapper {
    
    /** @var type extended (TestQuestions) */
    var $type;

    
    var $filename;

    /**
     *
     * @var testMap containing student-information
     */
    var $testMap = array();
    /**
     * Constructor
     */
    public function __construct($type) {
        /**
         * instantiate the variables with form input
         */
        $this->type=$type;
        
        $date = time();
        
        $this->inst_id = IL_INST_ID;
        
        $this->subdir = $date."__".$this->inst_id;
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
			case "to":
				return $this->buildExportCSVFile();
				break;
                        
		}
    }
    
    function buildExportCSVFile() {
        
    }
    
    function getHashMap() {
        return $this->testMap;
    }
    
    private function getActiveID($userID) {
        global $ilDB;
        $activeID = array();
        $query = "SELECT active_id
                  FROM
                  tst_active
                  WHERE user_fi = ".$ilDB->quote($userID, "integer");
        $result = $ilDB->query($query);
        while ($record = $ilDB->fetchAssoc($result)){
            array_push($activeID, $record);
        }
        
        return $activeID;
    }
    
    private function getInfo($userID){
        global $ilDB;
        $info = array();
        $query = "SELECT lastname, firstname, matriculation, email
                  FROM
                  usr_data
                  WHERE (usr_id = '".$userID."')";
        $result = $ilDB->query($query);
        while ($record = $ilDB->fetchAssoc($result)){
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
      while ($record = $ilDB->fetchObject($result)) {
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
    protected function getQuestionIDs()
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
        
        $query = "SELECT title FROM qpl_questions WHERE (question_fi= '".$questionID."')";
        
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
                if ($value-> active_fi == $activeID AND $value-> questionId.question_fi == $questionID ){
                    if ($value-> points != null){
                        return $value-> points ;
                    }else {
                        return 0; 
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
    public function buildHashMap(){
        $this->testMap=array(); 
        $resultObject= $this->getAssociation();
        $userArray= $this->getUniqueTestUserID();
        $questions = $this->getQuestionIDs();
        
        
        $userID;
        $questionID;
        
        //var_dump($userArray);
        var_dump($resultObject);
        //var_dump($questions);
        if(!empty($userArray)&&!empty($questions)){
        
        for ($i = 0; $i < count($userArray); $i++){
            $userID = $userArray[$i]['user_fi'];
            $activeID = $this->getActiveID($userID)[$i]['active_id'];
            
            $userInfo = $this->getInfo($userID); // Retrieve UserInfo (Lastname, Firstname, Email, Matriculation) for userID
             ;//Store UserInfo in Array 
            
                for ($j = 0; $j<count($questions);$j++){  
                    $questionID = $questions[$j]['question_fi'];
                    $this->testMap[$userID] = array();
                    $points = $this->getMark($activeID, $questionID, $resultObject); //Points for a given user on a given question
                    echo $points;  
                    if($points>0){
                        array_push($this->testMap, $points);
                        $this->testMap[$userID][$questionID] = $points; 
                        
                        }else{
                        $this->testMap[$userID][$questionID] = 0;
                        } 
                        var_dump($this->testMap);
                }            
        }
        }else{
            ilUtil::sendFailure("Either no Users or no Tests");
        }
    }
    
    
}
    

 ?>
