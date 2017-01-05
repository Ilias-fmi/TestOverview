<?php
/**
 *
 */

require_once ilPlugin::getPluginObject(IL_COMP_SERVICE, 'Repository', 'robj', 'TestOverview')->getDirectory() . '/classes/GUI/class.ilMappedTableGUI.php';
class ExerciseListTableGUI extends ilMappedTableGUI {
    protected $parent;
    


    /**
     * des is die query 
     * Select * from rep_robj_xtov_e2o join object_reference join exc_assignment on (obj_id_exercise = ref_id and object_reference.obj_id = exc_assignment.exc_id )
     */
    public function __construct(ilObjectGUI $a_parent_obj, $a_parent_cmd){
        global $ilCtrl,$tpl;
        
        
        $this->parent = $a_parent_obj;

		parent::__construct($a_parent_obj, $a_parent_cmd);

		$this->setTitle(
			sprintf(
				$this->lng->txt('rep_robj_xtov_exercise_list_gui'),
				$a_parent_obj->object->getTitle()
			)
		);
               
		//$this->setRowTemplate('tpl.simple_object_row.html', $plugin->getDirectory());
                
            
        
    }
    public function getSelectedExercises($overviewId){
        global $ilDB;
        $exercises = array ();
        $query = "Select obj_id_exercise,title from rep_robj_xtov_e2o join exc_assignment on (rep_robj_xtov_e2o.obj_id_exercise = exc_assignment.exc_id) where obj_id_overview ='". $overviewId . "'" ;
        $result = $ilDB->query($query);
                while ($record = $ilDB->fetchObject($result)){
                    array_push($exercises,$record);
                }
        return $exercises;           
    }
    
    
    public function getHTML(){
        global $ilCtrl;
        include_once 'Services/Form/classes/class.ilCheckboxGroupInputGUI.php';
        $this->parent->exerciseDeleteChecks = new ilPropertyFormGUI();
        $group = new ilCheckboxGroupInputGUI("Exercises","jo");
        $this->parent->exerciseDeleteChecks->setFormAction($ilCtrl->getFormAction($this->parent, 'deleteExercises'));
        $this->parent->exerciseDeleteChecks->addCommandButton("initSelectExercise", "Insert Exercise");
        $exercises = $this->getSelectedExercises($this->parent->object-> getId());
        if ($exercises != null){
            foreach ($exercises as $exercise){
                $exc_check = new ilCheckboxOption($exercise-> title, $exercise-> obj_id_exercise, "Bald wird hier ihr Pfad stehen ");
                $group->addOption($exc_check);
            }
        }       
                
                $this->parent->exerciseDeleteChecks-> addItem($group);
                $this-> parent-> exerciseDeleteChecks->addCommandButton("deleteExercises", "Delete Exercises");
         return $this->parent->exerciseDeleteChecks -> getHTML();
    }
}



?>