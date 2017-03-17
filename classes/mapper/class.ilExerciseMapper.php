<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 *	@package	TestOverview repository plugin
 *	@category	Core
 *	@author		Jan Ruthardt <janruthardt@web.de>
 *
 **/
require_once ilPlugin::getPluginObject(IL_COMP_SERVICE, 'Repository', 'robj', 'TestOverview')
                ->getDirectory() . '/classes/mapper/class.ilDataMapper.php';

class ilExerciseMapper extends ilDataMapper {

    public $lng;

    public function setParent($lng) {
        $this->lng = $lng;
    }

    protected $tableName = "rep_robj_xtov_e2o";

    public function getSelectPart() {
        return " DISTINCT (user_id), firstname, lastname, obj_id , mark";
    }

    public function getFromPart() {
        return " rep_robj_xtov_e2o  ,exc_returned , exc_mem_ass_status  join usr_data  on (exc_mem_ass_status.usr_id = usr_data.usr_id)";
    }

    public function getWherePart(array $filters) {
        return " exc_returned.ass_id = exc_mem_ass_status.ass_id
                And user_id = exc_mem_ass_status.usr_id and obj_id_exercise = obj_id ";
    }

    /**
     * Get the Class all Exercise Partisipants and there results
     */
    public function getArrayofObjects($overviewID) {
        global $ilDB;
        $DbObject = array();

        $query = "select ut_lp_marks.usr_id as user_id, firstname ,lastname ,obj_id ,mark from  rep_robj_xtov_e2o join ut_lp_marks join usr_data on
                    (rep_robj_xtov_e2o.obj_id_exercise = ut_lp_marks.obj_id and ut_lp_marks.usr_id = usr_data.usr_id)
                    where obj_id_overview = '" . $overviewID .
                "' ORDER BY obj_id DESC";

        $result = $ilDB->query($query);
        while ($record = $ilDB->fetchObject($result)) {
            array_push($DbObject, $record);
        }

        return $DbObject;
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
     * Gets the Name of an Student for the given Member ID
     */
    public function getStudName($studID) {
        global $ilDB;
        $query = "Select usr_id ,firstname ,lastname From usr_data
                where usr_id = '" . $studID . "'";
        $result = $ilDB->query($query);
        $record = $ilDB->fetchObject($result);

        return $record->firstname . " " . $record->lastname;
    }

    /**
     * Renders the HTML code into the Given Template
     */
    public function getHtml($overviewID) {

        global $lng;
        $matrix = $this->buildMatrix($overviewID);
        $tests = $this->getUniqueExerciseId($overviewID);
        $tpl = new ilTemplate("tpl.exercise_view.html", true, true, "Customizing/global/plugins/Services/Repository/RepositoryObject/TestOverview");
        $lng->loadLanguageModule("assessment");
        $tpl->setCurrentBlock("user_colum");
        $tpl->setVariable("user", $lng->txt("eval_search_users"));
        $tpl->parseCurrentBlock();
        foreach ($tests as $test) {
            $txt = "<th> <a href='ilias.php?ref_id=". $this->getRefId($test)."&cmd=showSummary&cmdClass=ilinfoscreengui&cmdNode=bb:au:7f&baseClass=ilExerciseHandlerGUI'>" . $this->getExerciseName($test) . "</th>";
            $tpl->setCurrentBlock("exercise_colum");
            $tpl->setVariable("colum", $txt);
            $tpl->parseCurrentBlock();
        }
        $tpl->setCurrentBlock("score_colum");
        $tpl->setVariable("score", $lng->txt("tst_mark"));
        $tpl->parseCurrentBlock();
        foreach ($matrix as $row) {
            $txt = "<tr>";
            $subString = "";
            $totalScore = 0;
            foreach ($row as $field) {
                $subString .= "<th>";
                $subString .= $field;
                $subString .= "</th>";
                $totalScore += $field;
            }
            $txt .= $subString;
            $txt .= "<th>";
            $txt .= $totalScore;
            $txt .= "</th>";
            $totalScore = 0;
            $txt .= "</tr>";
            $tpl->setCurrentBlock("exercise_row");
            $tpl->setVariable("data", $txt);
            $tpl->parseCurrentBlock();
        }
        return $tpl->get();
    }

    /**
     *
     * @param type $overviewID
     * @return array Is an Array of array
     * the inner arrays have as the first Element the studID, after that are all Marks for the tests
     */
    public function buildMatrix($overviewID) {

        $DbObject = $this->getArrayofObjects($overviewID);
        $tests = $this->getUniqueExerciseId($overviewID);
        $users = $this->getUniqueUserId($overviewID);
        $outerArray = array();
        for ($i = 0; $i < count($users); $i++) {
            $innerArray = array();
            $user = $this->getStudName($users[$i]);
            array_push($innerArray, $user);
            for ($j = 0; $j < count($tests); $j++) {
                $mark = $this->getMark($users[$i], $tests[$j], $DbObject);
                if ($mark > 0) {
                    //array_push($innerArray, $mark);
                    array_push($innerArray, $mark);
                } else {
                    array_push($innerArray, $mark);
                }
            }
            array_push($outerArray, $innerArray);
        }

        return $outerArray;
    }

    /**
     * Get back a Mark of the user in the specific test
     */
    public function getMark($userID, $testID, $Db) {
        foreach ($Db as $row) {
            if ($row->user_id == $userID AND $row->obj_id == $testID) {
                if ($row->mark != null) {
                    return $row->mark;
                } else {
                    return 0;
                }
            }
        }
        return 0;
    }

    /**
     *
     * @global type $ilDB
     * @param type $overviewID
     * @return array of Integer Exercise ID's
     */
    public function getUniqueExerciseId($overviewID) {
        global $ilDB;
        $UniqueIDs = array();
        $query = "SELECT 
                    DISTINCT (exr.obj_id)
                FROM
                    rep_robj_xtov_e2o e2o
                JOIN  
                    exc_returned exr ON e2o.obj_id_exercise = exr.obj_id
                JOIN 
                    exc_mem_ass_status exmem  ON exr.user_id = exmem.usr_id
                JOIN
                    usr_data ud ON exmem.usr_id = ud.usr_id
                JOIN 
                    object_reference ref ON ref.obj_id = e2o.obj_id_exercise
                WHERE
                    exr.ass_id = exmem.ass_id
                    AND e2o.obj_id_overview = %s
                    AND ref.deleted IS NULL
                    ORDER BY exr.obj_id";
        $result = $ilDB->queryF($query, 
                                array('integer'),
                                array($overviewID));
        while ($record = $ilDB->fetchObject($result)) {
            array_push($UniqueIDs, $record->obj_id);
        }
        return $UniqueIDs;
    }

    /**
     *
     * @global type $ilDB
     * @param type $overviewID
     * @return array of Integer User ID's
     */
    public function getUniqueUserId($overviewID) {
        global $ilDB;
        $UniqueIDs = array();
        $query = "Select DISTINCT(exc_mem_ass_status.usr_id) from rep_robj_xtov_e2o  ,exc_returned , exc_mem_ass_status  join usr_data  on (exc_mem_ass_status.usr_id = usr_data.usr_id)
                where exc_returned.ass_id = exc_mem_ass_status.ass_id
                And user_id = exc_mem_ass_status.usr_id and obj_id_exercise = obj_id
                and obj_id_overview = '" . $overviewID . "' order by exc_mem_ass_status.usr_id";
        $result = $ilDB->query($query);
        while ($record = $ilDB->fetchObject($result)) {
            array_push($UniqueIDs, $record->usr_id);
        }
        return $UniqueIDs;
    }

    public function getTotalScores($overviewID) {
        $data = array();
        $results = $this->buildMatrix($overviewID);
        if (!empty($results)) {
            foreach ($results as $row) {
                $totalScore = 0;
                foreach ($row as $colum) {
                    $totalScore += $colum;
                }
                array_push($data, $totalScore);
            }
            return $data;
        }
    }

    public function getRefId($ObjId) {
        global $ilDB;
        $query = "SELECT ref_id FROM object_reference WHERE obj_id = %s";
        $result = $ilDB->queryF($query, array('integer'), array($ObjId));

        $record = $ilDB->fetchAssoc($result);

        return $record['ref_id'];
    }

     /**
         * This method is used to edit the ranking in the database
         * @param type $average
         * @param type $studid
         * @param type $toId
         */
        public function setData2Rank($average,$studid,$eoId)
        {       	  $query=
                            "REPLACE INTO
                                    rep_robj_xtov_eorank(stud_id, rank, eo_id)
                            Values
                                    ($studid,$average,$eoId);";
                      $this->db->query($query);

        }
        /**
         * Delete all rankings for a TestOverview Object
         * @param type $id
         */
        public function resetRanks($id)
        {$query=
                 "DELETE FROM `rep_robj_xtov_eorank` WHERE eo_id=$id" ;
            $this->db->query($query);
        }
        /**
         * Gets a list of students which is sorted by ranking
         * @param type $id
         * @return type
         */
        public function getRankedList($id)
        {
            $query=
                 "SELECT stud_id FROM `rep_robj_xtov_eorank` WHERE eo_id=$id ORDER BY rank ASC "
			   ;

            $result= $this->db->query($query);
            //echo("<script>console.log('PHP: q ');</script>");
             return  $result;

        }


        /**
         * Gets the ranking of a student
         * @global type $ilDB
         * @param type $id
         * @param type $stdID
         * @return int
         */

        public function getRankedStudent($id,$stdID)
        {
            global $ilDB;
            $rank=0;
            $query=
                 "SELECT stud_id FROM `rep_robj_xtov_eorank` WHERE eo_id=$id ORDER BY rank DESC "
			   ;

            $result= $this->db->query($query);
            $index=1;
             while($student= $ilDB ->fetchAssoc ($result) )
       {
                   if($student[stud_id]===$stdID){
                   $rank=$index;
                   break;
               }
               $index++;

        }
             return  $rank;

        }

         public function getCount($id)
        {

             global $ilDB;
            $count=0;
            $query=
                 "SELECT stud_id FROM `rep_robj_xtov_eorank` WHERE eo_id=$id "
			   ;

            $result= $this->db->query($query);
            while($data=$ilDB->fetchAssoc($result))
            {
                $count++;
            }
            //$count = count($data);

             return $count;
        }

        public function createDate($o_id)
        {
            global $ilDB;
            $timestamp = time();
            $datum = (float) date("YmdHis", $timestamp);

             $query=
                            "REPLACE INTO
                                    rep_robj_xtov_rankdate (rankdate, otype,o_id)
                            Values
                                    ($datum,'eo',$o_id);";
                      $this->db->query($query);

        }

         public function getDate($o_id)
         {
             global $ilDB;
             $query=
                 "SELECT rankdate FROM `rep_robj_xtov_rankdate` WHERE o_id=$o_id AND otype='eo'"
			   ;

            $result= $this->db->query($query);

            $result= $this->db->query($query);
            $rankDate=array();

             while($date= $ilDB ->fetchAssoc ($result) ){
             $rankDate=$date['rankdate'];

             }

             return $rankDate;

         }
        /**
	 *	Get pairs of Participants groups
	 *
	 *	This method can be used to list groups in a
	 *	HTML <select>. The index in the returned array
	 *	corresponds to the groups' obj_id and the value
	 *	is the groups' title.
	 *
	 *	@param	integer	$overviewId
	 *	@return array	Where index = obj_id and value = group title
	 */
	public function getGroupPairs($overviewId)
	{
		$pairs   = array();
		$rawData = $this->getList(array(), array("overview_id" => $overviewId));
		foreach ($rawData['items'] as $item) {
			$object = ilObjectFactory::getInstanceByObjId($item->obj_id, false);
			$pairs[$item->obj_id] = $object->getTitle();
		}

		return $pairs;
	}
}
?>
