<?php
/**
 *
 */

require_once ilPlugin::getPluginObject(IL_COMP_SERVICE, 'Repository', 'robj', 'TestOverview')->getDirectory() . '/classes/GUI/class.ilMappedTableGUI.php';
class ExerciseListTableGUI extends ilMappedTableGUI {
    
    
    /**
     * des is die query 
     * Select * from rep_robj_xtov_e2o join object_reference join exc_assignment on (obj_id_exercise = ref_id and object_reference.obj_id = exc_assignment.exc_id )
     */
    public function __construct(ilObjectGUI $a_parent_obj, $a_parent_cmd){
        global $ilCtrl,$tpl;
        
        
        

		parent::__construct($a_parent_obj, $a_parent_cmd);

		$this->setTitle(
			sprintf(
				$this->lng->txt('rep_robj_xtov_test_list_table_title'),
				$a_parent_obj->object->getTitle()
			)
		);
               
		//$this->setRowTemplate('tpl.simple_object_row.html', $plugin->getDirectory());
                
            
        
    }
    public function getSelectedExercises(){
        global $ilDB;
        $exercises = array ();
        $query = "Select obj_id_exercise,title from rep_robj_xtov_e2o join exc_assignment on (rep_robj_xtov_e2o.obj_id_exercise = exc_assignment.exc_id)";
        $result = $ilDB->query($query);
                while ($record = $ilDB->fetchObject($result)){
                    array_push($exercises,$record);
                }
        return $exercises;           
    }
    
    public function getHTML(){
        $exercises = $this->getSelectedExercises();
         $plugin = ilPlugin::getPluginObject(IL_COMP_SERVICE, 'Repository', 'robj', 'TestOverview');
        $tpl = new ilTemplate ('tpl.simple_object_row_exercise.html',true, true ,$plugin->getDirectory());
        //$tpl-> addColumn($this->lng->txt('rep_robj_xtov_test_list_hdr_test_title'), 'title');
        $tpl->setCurrentBlock("exercises");
        foreach($exercises as $exercise){
            $tpl->setVariable("VAL_CHECKBOX", ilUtil::formCheckbox(false, 'test_ids[]', $exercise-> obj_id_exercise));
            $tpl->setVariable("OBJECT_TITLE", $exercise-> title);
            $tpl->setVariable("OBJECT_INFO", "Des der Pfad");
            $tpl->parseCurrentBlock();
        }
return $tpl->get();
    }
    
}



?>
