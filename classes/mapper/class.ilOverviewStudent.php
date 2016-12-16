<?php 

/**
 * DB Mapper for the Student View (User with only read Permissons)
 */

class studentMapper
    {
    /**
     * Gives back the Results for the given Student and TestOverview ID 
     * @global type $ilDB
     * @param type $studId
     * @param type $overviewId
     * @return string
     */
    public function getResults ($studId, $overviewId){
        global $ilDB ;
        $average; 
        $maxPoints;

        $data = array();
        $query = "Select DISTINCT title, points, maxpoints, ending_time, ending_time_enabled as timeded From rep_robj_xtov_t2o Join object_reference Join tst_tests Join tst_active Join tst_pass_result Join object_data ON
                (rep_robj_xtov_t2o.ref_id_test = object_reference.ref_id AND object_reference.obj_id = tst_tests.obj_fi AND tst_active.test_fi = tst_tests.test_id
                AND tst_active.active_id = tst_pass_result.active_fi AND object_reference.obj_id = object_data.obj_id) 
                where obj_id_overview ='" . $overviewId ."'AND tst_active.user_fi = '". $studId ."'" ;
        $result = $ilDB->query($query);
        $tpl = new ilTemplate("tpl.stud_view.html", true, true, "Customizing/global/plugins/Services/Repository/RepositoryObject/TestOverview");
        
        //Baut aus den Einzelnen Zeilen Objekte
        while ($testObj = $ilDB->fetchObject($result)){
            array_push($data, $testObj);
        }
        
        foreach ($data as $set){
            $timestamp = time();
            $datum =  (float) date("YmdHis",$timestamp);
            $testTime = (float)$set-> ending_time;
            /*Checks if the test has been finished or if no end time is given*/
            if (($testTime - $datum) < 0 || $set->timeded == 1  ){ 
                $tpl->setCurrentBlock("test_results");
                $tpl->setVariable("Name", $set->title);
                $average += $set-> points; 
                $maxPoints += $set-> maxpoints;
                if ($set-> points > ($set-> maxpoints/2)){
                    $pointsHtml = "<td class='green-result'>" . $set-> points  ."</td>";
                }else {
                    $pointsHtml = "<td class='red-result'>" . $set-> points  ."</td>";
                }           
            $tpl->setVariable("Point", $pointsHtml);
            
            $tpl->parseCurrentBlock();
            }
        }
        if($this-> numOfTests($overviewId)== 0){
            $averageNum = 0;
        }else {
            $averageNum = $average/$this->numOfTests($overviewId);
        }
        $tpl-> setVariable("AveragePoints",$averageNum);
        if ($maxPoints == 0){
            $Prozentnum = 0;
        }else {
            $Prozentnum = (float) ($average/$maxPoints)*100;
        }
        
        $tpl-> setVariable("Average",round($Prozentnum,2));
        
        return $tpl-> get();
        
    }
    
    /**
     *  Counts the Number of Tests realted to the Overview Object
     * @global type $ilDB
     * @param int $overviewId
     * @return int
     */
    private function numOfTests($overviewId){
        global $ilDB; 
        $query = "Select count(ref_id_test) as num from rep_robj_xtov_t2o where obj_id_overview = '" . $overviewId ."'";
        $result =$ilDB->query($query); 
        $numOfTests = $ilDB->fetchObject($result);
        return $numOfTests-> num;
    }


    private function calcAverage($average){
        $length = count ($average);
        $totalPoints;
        foreach ($average as $points){
            $totalPoints += $points;
        }
        /*Catches the Exception if the array is null */
        try{
        $result = $totalPoints/$length;
        }catch (Exception $e){
            return "0 %";
        }
        $result = round($result ,2 );
        $result .= " %";
        return $result ;
        
    }
    
    private function getNumTests($overviewId){
        global $ilDB;
        $count = 0 ;
        $data = array ();
        $query = "Select ref_id_test ,ending_time from rep_robj_xtov_t2o Join object_reference Join tst_tests "
                . "on (ref_id_test = ref_id And obj_id = obj_fi) where obj_id_overview = '" . $overviewId . "'";
        $result = $ilDB->query($query);
        while ($testObj = $ilDB->fetchObject($result)){
            array_push($data, $testObj);
        }
            $timestamp = time();
            $datum =  (float) date("YmdHis",$timestamp);
        foreach ($data as $set){
            $testTime = (float)$set-> ending_time;
            if (($testTime - $datum) < 0){
                $count ++;
            }
        }
        return $count ;
                
    }
        
    
    }


?>
