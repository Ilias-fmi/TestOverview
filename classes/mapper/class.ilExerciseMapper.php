<?php
require_once ilPlugin::getPluginObject(IL_COMP_SERVICE, 'Repository', 'robj', 'TestOverview')
				->getDirectory() . '/classes/mapper/class.ilDataMapper.php';

class ilExerciseMapper 
    extends ilDataMapper{
    
    
    protected $tableName = "rep_robj_xtov_e2o";
    public function getSelectPart()
	{
        return "DISTINCT (user_id), obj_id , mark";
    }
    
    public function getFromPart()
	{
        return "rep_robj_xtov_e2o  ,exc_returned , exc_mem_ass_status";
    }
    
    public function getWherePart(array $filters)
	{
        return "exc_returned.ass_id = exc_mem_ass_status.ass_id And user_id = exc_mem_ass_status.usr_id and obj_id_exercise = obj_id";
    }
    
    /**
     * Get the Class all Exercise Partisipants and there results
     */
    public function getArrayofObjects (){
        global $ilDB;
        $DbObject = array ();
        
        $query = 
                "Select "
                .$this-> getSelectPart()
                ." From "
                .$this-> getFromPart()
                ." Where "
                .$this-> getWherePart(array());
                
                $result = $ilDB->query($query);
                while ($record = $ilDB->fetchObject($result)){
                    array_push($DbObject,$record);
                }
               
        return $DbObject;
        
    } 
        
    }
    
    
    
    
    
    



?> 
