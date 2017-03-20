<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 *	@package	TestOverview repository plugin
 *	@category	Core
 *	@author		Jan Ruthardt <janruthardt@web.de>
 *
 * This Class puts and deletes the given Exercise Ids and Overview Ids in the DB to creat a relation between them
 */
class ExerciseImport {

    /**
     *
     * @global type $ilDB
     * @param type $overviewId
     * @param type $exerciseId
     */
    public function createEntry ($overviewId,$exerciseId){
        global $ilDB;

        $obj_id = $this-> getObjectRef($exerciseId);
        $query = "INSERT IGNORE INTO rep_robj_xtov_e2o (obj_id_overview,obj_id_exercise)
                  VALUES (" .$overviewId ."," .$obj_id .")" ;

        $ilDB->manipulate ($query);

    }

    /**
     *
     * @global type $ilDB
     * @param type $refId
     * @return Int
     */
    public function getObjectRef ($refId){
        global $ilDB;

        $query = "Select obj_id from object_reference
                  where ref_id = '" . $refId ."'";

       $result = $ilDB->query($query);
        $obj_id = $ilDB->fetchObject($result);
        return $obj_id-> obj_id;
    }
    /**
     *
     * @param type $overviewId
     * @param type $exerciseId
     */
    public function deleteEntry ($exerciseId){
        global $ilDB;

        $query = "DELETE FROM rep_robj_xtov_e2o
                  WHERE obj_id_exercise = '" . $exerciseId . "'";

        $affected_rows = $ilDB->manipulate ($query);
    }
}



 ?>
