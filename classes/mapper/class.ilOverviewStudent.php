<?php 





class studentMapper
    {
    
    public function getResults ($studId, $overviewId){
        global $ilDB;
        $html = "<style> table, td, th { border: 1px solid black; } </style>";
        $html.= "<div> <table>";

        $html .= "";

        
        $query = "Select DISTINCT title, points, maxpoints  From rep_robj_xtov_t2o Join object_reference Join tst_tests Join tst_active Join tst_pass_result Join object_data ON
                (rep_robj_xtov_t2o.ref_id_test = object_reference.ref_id AND object_reference.obj_id = tst_tests.obj_fi AND tst_active.test_fi = tst_tests.test_id
                AND tst_active.active_id = tst_pass_result.active_fi AND object_reference.obj_id = object_data.obj_id) 
                where obj_id_overview ='" . $overviewId ."'AND tst_active.user_fi = '". $studId ."'" ;
        $result = $ilDB->query($query);
        
        
        
        while ($testObj = $ilDB->fetchObject($result)){
            $html .= "<tr>";
            $html .= "<td>";
            $html .= $testObj->title;
            $html .= "</td>";
            $html .= "<td>";
            $html .= $testObj->points;
            $html .= "</td>";
            $html .= "<td>";
            $html .= $testObj->maxpoints;
            $html .= "</td";
            $html .= "</tr>";
            
            
        }
        $html .="</table> </div>";
        
        return $html;
    }
        
    
    }


?>
