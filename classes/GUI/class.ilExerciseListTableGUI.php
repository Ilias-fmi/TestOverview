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
        global $ilCtrl,$tpl,$lng;
        $lng->loadLanguageModule("common");
        
        
        $this->parent = $a_parent_obj;

	parent::__construct($a_parent_obj, $a_parent_cmd);

	$this->setTitle(
		sprintf(
			$this->lng->txt('rep_robj_xtov_exercise_list_gui'),
			$a_parent_obj->object->getTitle()
		)
	);
               
		//$this->setRowTemplate('tpl.simple_object_row.html', $plugin->getDirectory());
                
        $this->addColumn($this->lng->txt(''), '', '1px', true);
        $this->addColumn($this->lng->txt('rep_robj_xtov_test_list_hdr_test_title'), 'title');
        $this->addColumn($this->lng->txt('rep_robj_xtov_exercise_list_hdr_exercise_info'), '');
                
        $this->setDescription($this->lng->txt('rep_robj_xtov_exercise_list_description'));
        $this->setFormAction($ilCtrl->getFormAction($this->getParentObject(), 'updateSettings'));
        $this->addCommandButton("initSelectExercise", $lng->txt("exc_add"));
        $this->addMultiCommand("deleteExercises", $lng->txt("delete_selected"));
        
        $this->setShowRowsSelector(true);
        $this->setSelectAllCheckbox('exercise_ids[]');
                
        
    }
    
    	/**
	 *	Fill a table row.
	 *
	 *	This method is called internally by ilias to
	 *	fill a table row according to the row template.
	 *
         *	@param stdClass $item
         */
    protected function fillRow(stdClass $item)
    {
		/* Configure template rendering. */
		$this->tpl->setVariable('VAL_CHECKBOX', ilUtil::formCheckbox(false, 'exercise_ids[]', $item->ref_id));
		$this->tpl->setVariable('OBJECT_TITLE', $item->title);
		$this->tpl->setVariable('OBJECT_INFO', $this->getExercisePath($item));
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
    /**
	 *    Retrieve the tree path to an ilObjExercise.
	 *
	 *    The getExercisePath() method should be used to
	 *    retrieve the full path to a exercise node in the
	 *    ilias tree.
	 *
	 * @params    stdClass    $item
	 * @param stdClass $item
	 * @return string
	 */
    
	private function getExercisePath( stdClass $item )
	{
		/**
		 * @var $tree ilTree
		 */
		global $tree;
		
		$path_str = '';

		$path = $tree->getNodePath($item->ref_id);
		while ($node = current($path))
		{
			$prepend  = empty($path_str) ? '' : "{$path_str} > ";
			$path_str = $prepend . $node['title'];
			next($path);
		}

		return $path_str;
	}
    
    public function getHTML(){
        global $ilCtrl,$lng;
        include_once 'Services/Form/classes/class.ilCheckboxGroupInputGUI.php';
        $this->parent->exerciseDeleteChecks = new ilPropertyFormGUI();
        $group = new ilCheckboxGroupInputGUI("Exercises","jo");
        $this->parent->exerciseDeleteChecks->setFormAction($ilCtrl->getFormAction($this->parent, 'deleteExercises'));
        $this->parent->exerciseDeleteChecks->addCommandButton("initSelectExercise", $lng->txt("exc_add"));
        $this-> parent-> exerciseDeleteChecks->addCommandButton("deleteExercises", $lng->txt("delete_selected"));
        $exercises = $this->getSelectedExercises($this->parent->object-> getId());
        if ($exercises != null){
            foreach ($exercises as $exercise){
                $exc_check = new ilCheckboxOption($exercise-> title, $exercise-> obj_id_exercise, "Bald wird hier ihr Pfad stehen ");
                $group->addOption($exc_check);
            }
        }       
                
                $this->parent->exerciseDeleteChecks-> addItem($group);
                
         return $this->parent->exerciseDeleteChecks -> getHTML();
    }
}



?>
