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
        global $ilDB;
        $average = array();
        //Style für die Tabelle
        $html = "<style> table, td, th { border: 1px solid black; } </style>";
        //$html = "<style>" . $this-> getCSS()  . "</style>";
        $html .= "<div ID='student-view'>  <div ID='col-1'> <table>";

        $html .= "<tr> <td> Test Name </td> <td> Erreichte Punkte</td>  ";

        
        $query = "Select DISTINCT title, points, maxpoints  From rep_robj_xtov_t2o Join object_reference Join tst_tests Join tst_active Join tst_pass_result Join object_data ON
                (rep_robj_xtov_t2o.ref_id_test = object_reference.ref_id AND object_reference.obj_id = tst_tests.obj_fi AND tst_active.test_fi = tst_tests.test_id
                AND tst_active.active_id = tst_pass_result.active_fi AND object_reference.obj_id = object_data.obj_id) 
                where obj_id_overview ='" . $overviewId ."'AND tst_active.user_fi = '". $studId ."'" ;
        $result = $ilDB->query($query);
        
        
        //Baut aus den Einzelnen Zeilen Objekte
        while ($testObj = $ilDB->fetchObject($result)){
            $html .= "<tr>";
            $html .= "<td>";
            $html .= $testObj->title;
            $html .= "</td>";
            $html .= "<td>";
            $points = (int)$testObj->points;
            $maxPoints = (int)$testObj->maxpoints;
            $res = (intval($points) / intval($maxPoints)) * 100;
            array_push($average, $res);
            $res = round($res,2);
            $res .= " %";
            $html .= $res ;
            $html .= "</td";
            $html .= "</tr>";
            
            
        }
        $html .="</table> </div> <div ID='col-2'> ". $this-> calcAverage($average) ."</div> </div>";
        
        
        
        return $html;
    }
    
    private function getCSS(){
        $css = file_get_contents("Customizing/global/plugins/Services/Repository/RepositoryObject/TestOverview/templates/css/testoverview.css");
        if (empty($css)){
        $css .= "table, td, th { border: 1px solid black; }" ;
        }
        return $css;
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
