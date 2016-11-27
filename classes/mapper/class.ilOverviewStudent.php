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
        $average = array();
        //Style für die Tabelle
        //$html = "<style> table, td, th { border: 1px solid black; } </style>";
        //$html = "<style>" . $this-> getCSS()  . "</style>";
        //$html .= "<div ID='student-view'>  <div ID='col-1'> <table>";
        //$html .= "<tr> <td> Test Name </td> <td> Erreichte Punkte</td>  ";

        $data = array();
        $query = "Select DISTINCT title, points, maxpoints  From rep_robj_xtov_t2o Join object_reference Join tst_tests Join tst_active Join tst_pass_result Join object_data ON
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
            $tpl->setCurrentBlock("test_results");
            $tpl->setVariable("Name", $set->title);
            if ($set-> points > ($set-> maxpoints/2)){
                $pointsHtml = "<td class='green-result'>" . $set-> points  ."</td>";
            }else {
                $pointsHtml = "<td class='red-result'>" . $set-> points  ."</td>";
            }        
            $tpl->setVariable("Point", $pointsHtml);
            $tpl->parseCurrentBlock();
        }
        
        
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
        
        $query = "Select Count('ref_id_test') as num From rep_robj_xtov_t2o where obj_id_overview = '". $overviewId ."'";
        $result = $ilDB->query($query);
        $rowNum = $ilDB->fetchObject($result);
        return $rowNum-> num;
                
    }
        
    
    }


?>
