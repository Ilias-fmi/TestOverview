<?php
/**
 * This Class puts and deletes the given Exercise Ids and Overview Ids in the DB to creat a relation between them 
 */
class ExerciseImport {
    
    public function createEntry ($overviewId,$exerciseId){
        global $ilDB;
        
        $query = "INSERT INTO rep_robj_xtov_e2o (obj_id_overview,obj_id_exercise)
                  VALUES (" .$overviewId ."," .$exerciseId .")" ;
        
        $ilDB->manipulate ($query);
    }
}



 ?> 
