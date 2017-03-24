<?php

/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * 	@package	TestOverview repository plugin
 * 	@category	Core
 * 	@author		Jan Ruthardt <janruthardt@web.de>
 *  
 *
 * DB Mapper for the Student View (User with only read Permissons)
 */
class studentMapper {

    /**
     * Gives back the Results for the given Student and TestOverview ID 
     * @global type $ilDB
     * @param type $studId
     * @param type $overviewId
     * @return string
     */
    public function getResults($studId, $overviewId) {
        global $ilDB, $lng;
        $average;
        $maxPoints;

        $data = array();
        $query = "Select DISTINCT title, points,ref_id_test, maxpoints, tst_tests.ending_time, ending_time_enabled as timeded From
                    rep_robj_xtov_t2o Join object_reference Join tst_tests Join tst_active Join tst_pass_result Join object_data ON
                (rep_robj_xtov_t2o.ref_id_test = object_reference.ref_id AND object_reference.obj_id = tst_tests.obj_fi AND tst_active.test_fi = tst_tests.test_id
                AND tst_active.active_id = tst_pass_result.active_fi AND object_reference.obj_id = object_data.obj_id) 
                where obj_id_overview ='" . $overviewId . "'AND tst_active.user_fi = '" . $studId . "'";
        $result = $ilDB->query($query);
        $tpl = new ilTemplate("tpl.stud_view.html", true, true, "Customizing/global/plugins/Services/Repository/RepositoryObject/TestOverview");
        //Internationalization
        $lng->loadLanguageModule("assessment");
        $lng->loadLanguageModule("certificate");
        $lng->loadLanguageModule("rating");
        $lng->loadLanguageModule("common");
        $lng->loadLanguageModule("trac");
        $tpl->setCurrentBlock("head_row");
        $tpl->setVariable("test", $lng->txt("rep_robj_xtov_testOverview"));
        $tpl->setVariable("exercise", $lng->txt("rep_robj_xtov_ex_overview"));
        $tpl->setVariable("testTitle", $lng->txt("certificate_ph_testtitle"));
        $tpl->setVariable("score", $lng->txt("toplist_col_score"));
        $tpl->parseCurrentBlock();
        $tpl->setVariable("average", $lng->txt('rep_robj_xtov_avg_points'));
        $tpl->setVariable("averagePercent", $lng->txt("trac_average"));
        $tpl->setVariable("exerciseTitle", $lng->txt("certificate_ph_exercisetitle"));
        $tpl->setVariable("mark", $lng->txt("tst_mark"));
        $tpl->setVariable("studentRanking", $lng->txt("toplist_your_result"));
        //Baut aus den Einzelnen Zeilen Objekte
        while ($testObj = $ilDB->fetchObject($result)) {
            array_push($data, $testObj);
        }

        foreach ($data as $set) {
            $timestamp = time();
            $datum = (float) date("YmdHis", $timestamp);
            $testTime = (float) $set->ending_time;
            
            var_dump( $testTime);
            var_dump ($datum);
            /* Checks if the test has been finished or if no end time is given */
           if ((($testTime - $datum) < 0 || $set->timeded == 0) && $this->isTestDeleted($set->ref_id_test) == null) {
                $tpl->setCurrentBlock("test_results");
                $tpl->setVariable("Name", $set->title);
                $average += $set->points;
                $maxPoints += $set->maxpoints;
                if ($set->points > ($set->maxpoints / 2)) {
                    $pointsHtml = "<td class='green-result'>" . $set->points . "</td>";
                } else {
                    $pointsHtml = "<td class='red-result'>" . $set->points . "</td>";
                }
                $tpl->setVariable("Point", $pointsHtml);

                $tpl->parseCurrentBlock();
            }
        }
        ////Exercise Part ////
        require_once ilPlugin::getPluginObject(IL_COMP_SERVICE, 'Repository', 'robj', 'TestOverview')
                        ->getDirectory() . '/classes/mapper/class.ilExerciseMapper.php';
        $excMapper = new ilExerciseMapper();
        $grades = $this->getExerciseMarks($studId, $overviewId);
        $totalGrade = 0;

        foreach ($grades as $grade) {
            if ($this->isExerciseDeleted($grade->obj_id) == null) {
                $gradeName = $excMapper->getExerciseName($grade->obj_id);
                $totalGrade += $grade->mark;
                $tpl->setCurrentBlock("exercise_results");
                $tpl->setVariable("Exercise", $gradeName);
                $tpl->setVariable("Mark", $grade->mark);
                $tpl->parseCurrentBlock();
            }
        }

        //// generall Part /////
        if ($this->numOfTests($overviewId) == 0) {
            $averageNum = 0;
        } else {
            $averageNum = round($average / $this->numOfTests($overviewId), 2);
        }
        $tpl->setVariable("AveragePoints", $averageNum);
        if ($maxPoints == 0) {
            $Prozentnum = 0;
        } else {
            $Prozentnum = (float) ($average / $maxPoints) * 100;
        }
        $lng->loadLanguageModule("crs");
        $tpl->setVariable("averageMark", $lng->txt('rep_robj_xtov_average_mark'));

        if (count($grades) > 0) {

            $tpl->setVariable("AverageMark", $totalGrade / count($grades));
        }
        $tpl->setVariable("totalMark", $lng->txt('rep_robj_xtov_total_mark'));
        $tpl->setVariable("TotalMark", $totalGrade);

        $tpl->setVariable("Average", round($Prozentnum, 2));
        /// ranking part /////
        $ilOverviewMapper = new ilOverviewMapper();
        $rank = $ilOverviewMapper->getRankedStudent($overviewId, $studId);
        $count = $ilOverviewMapper->getCount($overviewId);
        $date = $ilOverviewMapper->getDate($overviewId);
        if (!$rank == '0') {
            $tpl->setVariable("toRanking", $rank . " " . $lng->txt('rep_robj_xtov_out_of') . " " . $count . "<br> last update: " . $date);
        } else {
            $tpl->setVariable("toRanking", $lng->txt('links_not_available'));
        }
        $ilExerciseMapper = new ilExerciseMapper();
        $rank = $ilExerciseMapper->getRankedStudent($overviewId, $studId);
        $count = $ilExerciseMapper->getCount($overviewId);
        $date = $ilExerciseMapper->getDate($overviewId);
        if (!$rank == '0') {
            $tpl->setVariable("eoRanking", $rank . " " . $lng->txt('rep_robj_xtov_out_of') . "  " . $count . "<br> last update: " . $date);
        } else {
            $tpl->setVariable("eoRanking", $lng->txt('links_not_available'));
        }

        return $tpl->get();
    }

    public function isTestDeleted($refTestId) {
        global $ilDB;
        $query = "select deleted from object_reference where ref_id = '$refTestId'";
        $result = $ilDB->query($query);
        $deleteObj = $ilDB->fetchObject($result);
        return $deleteObj->deleted;
    }

    public function isExerciseDeleted($ObjExerciseId) {
        global $ilDB;
        $query = "select deleted from object_reference where obj_id = '$ObjExerciseId'";
        $result = $ilDB->query($query);
        $deleteObj = $ilDB->fetchObject($result);
        return $deleteObj->deleted;
    }

    /**
     *  Counts the Number of Tests realted to the Overview Object
     * @global type $ilDB
     * @param int $overviewId
     * @return int
     */
    private function numOfTests($overviewId) {
        global $ilDB;
        $query = "Select count(ref_id_test) as num from rep_robj_xtov_t2o where obj_id_overview = '" . $overviewId . "'";
        $result = $ilDB->query($query);
        $numOfTests = $ilDB->fetchObject($result);
        return $numOfTests->num;
    }

    private function calcAverage($average) {
        $length = count($average);
        $totalPoints;
        foreach ($average as $points) {
            $totalPoints += $points;
        }
        /* Catches the Exception if the array is null */
        try {
            $result = $totalPoints / $length;
        } catch (Exception $e) {
            return "0 %";
        }
        $result = round($result, 2);
        $result .= " %";
        return $result;
    }

    private function getTotalMark($overviewId) {
        
    }

    private function getNumTests($overviewId) {
        global $ilDB;
        $count = 0;
        $data = array();
        $query = "Select ref_id_test ,ending_time from rep_robj_xtov_t2o Join object_reference Join tst_tests "
                . "on (ref_id_test = ref_id And obj_id = obj_fi) where obj_id_overview = '" . $overviewId . "'";
        $result = $ilDB->query($query);
        while ($testObj = $ilDB->fetchObject($result)) {
            array_push($data, $testObj);
        }
        $timestamp = time();
        $datum = (float) date("YmdHis", $timestamp);
        foreach ($data as $set) {
            $testTime = (float) $set->ending_time;
            if (($testTime - $datum) < 0) {
                $count ++;
            }
        }
        return $count;
    }

    /////////////////////////////////////////// Exercise VIEW ////////////////////////////////////////
    /**
     * Gets all Exercises + Marks for the given Student 
     * @param type $studId
     */
    private function getExerciseMarks($studId, $overviewId) {
        global $ilDB;
        $grades = array();
        $query = "select ut_lp_marks.usr_id as user_id,obj_id ,mark 
                  from  rep_robj_xtov_e2o join ut_lp_marks join usr_data on 
                  (rep_robj_xtov_e2o.obj_id_exercise = ut_lp_marks.obj_id and ut_lp_marks.usr_id = usr_data.usr_id) 
                  where obj_id_overview = '" . $overviewId . "' And ut_lp_marks.usr_id='" . $studId . "'";
        $result = $ilDB->query($query);
        while ($exercise = $ilDB->fetchObject($result)) {
            array_push($grades, $exercise);
        }
        return $grades;
    }

}

?>
