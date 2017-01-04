<?php
require_once ilPlugin::getPluginObject(IL_COMP_SERVICE, 'Repository', 'robj', 'TestOverview')
				->getDirectory() . '/classes/mapper/class.ilDataMapper.php';

class ilExerciseMapper 
    extends ilDataMapper{
    
    
    protected $tableName = "rep_robj_xtov_e2o";
    public function getSelectPart()
    {
        return " DISTINCT (user_id), firstname, lastname, obj_id , mark";
    }
    
    public function getFromPart()
    {
        return " rep_robj_xtov_e2o  ,exc_returned , exc_mem_ass_status  join usr_data  on (exc_mem_ass_status.usr_id = usr_data.usr_id)";
    }
    
    public function getWherePart(array $filters)
    {
        return " exc_returned.ass_id = exc_mem_ass_status.ass_id 
                And user_id = exc_mem_ass_status.usr_id and obj_id_exercise = obj_id ";
    }
    
    /**
     * Get the Class all Exercise Partisipants and there results
     */
    public function getArrayofObjects ($overviewID){
        global $ilDB;
        $DbObject = array ();
  
        $query = "select DISTINCT (user_id), firstname, lastname, obj_id , mark 
                from rep_robj_xtov_e2o  ,exc_returned , exc_mem_ass_status  join usr_data  on (exc_mem_ass_status.usr_id = usr_data.usr_id)
                where exc_returned.ass_id = exc_mem_ass_status.ass_id 
                And user_id = exc_mem_ass_status.usr_id and obj_id_exercise = obj_id 
                and obj_id_overview = '" . $overviewID .
                "' ORDER BY obj_id DESC ";
                $result = $ilDB->query($query);
                while ($record = $ilDB->fetchObject($result)){
                    array_push($DbObject,$record);
                }
               
        return $DbObject;
        
    } 
    
    public function getExerciseName($exerciseID){
        global $ilDB;
        $query = "Select exc_id , title from exc_assignment
                where exc_id = '" . $exerciseID . "'";
        $result = $ilDB->query($query);
        $record = $ilDB->fetchObject($result);
        return $record-> title;
    }
    
    public function  getStudName($studID){
        global $ilDB;
        $query = "Select usr_id ,firstname ,lastname From usr_data
                where usr_id = '" . $studID . "'";
        $result = $ilDB->query($query);
        $record = $ilDB->fetchObject($result);
        
        return $record-> firstname . " " . $record-> lastname;
    }
    
    public function getHtml($overviewID){
       //global $tpl;
        $matrix = $this-> buildMatrix($overviewID);
        $tests = $this-> getUniqueExerciseId($overviewID);
        $tpl = new ilTemplate("tpl.exercise_view.html", true, true, "Customizing/global/plugins/Services/Repository/RepositoryObject/TestOverview");
        foreach ($tests as $test){
            $txt = "<th> ". $this-> getExerciseName($test) . "</th>";
            $tpl->setCurrentBlock("exercise_colum");
            $tpl->setVariable("colum", $txt);
            $tpl->parseCurrentBlock();
        }
        
        foreach ($matrix as $row){
            $txt = "<tr>"; 
            $subString = "";
            foreach ($row as $field){
            $subString .= "<th>";
            $subString .= $field ;
            $subString .= "</th>";
            }
            $txt .= $subString;
            $txt .= "</tr>";
            $tpl->setCurrentBlock("exercise_row");
            $tpl->setVariable("data", $txt);
            $tpl->parseCurrentBlock();
        }
        return $tpl-> get();
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
        
        /**
         * Get back a Mark of the user in the specific test 
         */
        public function getMark ($userID, $testID , $Db){
            foreach ($Db as $row){
                if ($row-> user_id == $userID AND $row-> obj_id == $testID ){
                    if ($row-> mark != null){
                        return $row-> mark ;
                    }else {
                        return 0; 
                    }        
                }
              
            }
            return 0;
    }
    /**
     * Gets the Max Stud ID in the belonging Exercises 
     * @global type $ilDB
     * @return type
     */
    public function getMaxUserId ($overviewID){
        global $ilDB;
        $query = "select DISTINCT (user_id), obj_id , mark 
                from rep_robj_xtov_e2o  ,exc_returned , exc_mem_ass_status 
                where exc_returned.ass_id = exc_mem_ass_status.ass_id And user_id = exc_mem_ass_status.usr_id 
                and obj_id_exercise = obj_id and obj_id_overview = '" . $overviewID ."'
                ORDER BY user_id DESC 
                LIMIT 1";
        $result = $ilDB->query($query);
         while ($record = $ilDB->fetchObject($result)){
             return $record-> user_id;
         }
         
    }
    /**
     * 
     * @global type $ilDB
     * @param type $overviewID
     * @return type
     */
    public function getMaxExerciseId ($overviewID){
        global $ilDB;
        $query = "select DISTINCT (user_id), obj_id , mark 
                from rep_robj_xtov_e2o  ,exc_returned , exc_mem_ass_status 
                where exc_returned.ass_id = exc_mem_ass_status.ass_id 
                And user_id = exc_mem_ass_status.usr_id and obj_id_exercise = obj_id 
                and obj_id_overview = '". $overviewID . "'
                ORDER BY obj_id DESC 
                limit 1";
        $result = $ilDB->query($query);
         while ($record = $ilDB->fetchObject($result)){
             return $record-> obj_id;
         }
    }
    /**
     * 
     * @global type $ilDB
     * @param type $overviewID
     * @return array of Integer Exercise ID's 
     */
    public function getUniqueExerciseId ($overviewID){
        global $ilDB;
        $UniqueIDs = array ();
        $query = "Select DISTINCT(obj_id) from rep_robj_xtov_e2o  ,exc_returned , exc_mem_ass_status  join usr_data  on (exc_mem_ass_status.usr_id = usr_data.usr_id)
                where exc_returned.ass_id = exc_mem_ass_status.ass_id 
                And user_id = exc_mem_ass_status.usr_id and obj_id_exercise = obj_id 
                and obj_id_overview = '" . $overviewID . "' order by obj_id ";
        $result = $ilDB->query($query);
         while ($record = $ilDB->fetchObject($result)){
           array_push($UniqueIDs,$record-> obj_id);  
         }
         return $UniqueIDs;
    }
    /**
     * 
     * @global type $ilDB
     * @param type $overviewID
     * @return array of Integer User ID's
     */
    
    public function getUniqueUserId ($overviewID){
        global $ilDB;
        $UniqueIDs = array ();
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
     * Deletes all relation from the given Overview ID to the Exercises ID's specified in the parameter 
     */
     public function deleteExercises ($exercises,$overviewID){
        if (gettype($exercises) == "array" ){
            foreach ($exercises as $exercise){
                $this-> deleteExercise ($exercise,$overviewID);
            }
        }
    }
    
    public function deleteExercise ($exercise ,$overviewID){
        global $ilDB;
        
        
        
    }
    
        
    }
    
   
    
    
    
    
    
    



?> 
