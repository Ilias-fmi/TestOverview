<?php

/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * 	@package	TestOverview repository plugin
 * 	@category	Core
 * 	@author		Jan Ruthardt <janruthardt@web.de>
 *  
 * */
require_once ilPlugin::getPluginObject(IL_COMP_SERVICE, 'Repository', 'robj', 'TestOverview')->getDirectory() . '/classes/GUI/class.ilMappedTableGUI.php';

class ilExerciseListTableGUI extends ilMappedTableGUI {

    protected $parent;

    public function __construct(ilObjectGUI $a_parent_obj, $a_parent_cmd) {
        global $ilCtrl, $lng;
        /* Pre-configure table */

        $this->setId(
                sprintf(
                        'test_overview_test_list_%d', $a_parent_obj->object->getId()
                )
        );

        $this->parent = $a_parent_obj;

        parent::__construct($a_parent_obj, $a_parent_cmd);

        $this->setDefaultOrderDirection('ASC');
        $this->setDefaultOrderField('title');

        $this->setTitle(
                sprintf(
                        $this->lng->txt('rep_robj_xtov_exercise_list_gui'), $a_parent_obj->object->getTitle()
                )
        );

        $this->setExternalSorting(true);
        $this->setExternalSegmentation(true);


        $plugin = ilPlugin::getPluginObject(IL_COMP_SERVICE, 'Repository', 'robj', 'TestOverview');
        $this->setRowTemplate('tpl.simple_object_row.html', $plugin->getDirectory());

        $this->addColumn($this->lng->txt(''), '', '1px', true);
        $this->addColumn($this->lng->txt('rep_robj_xtov_test_list_hdr_test_title'), 'title');
        $this->addColumn($this->lng->txt('rep_robj_xtov_exercise_list_hdr_exercise_info'), '');

        $this->setDescription($this->lng->txt('rep_robj_xtov_exercise_list_description'));
        $this->setFormAction($ilCtrl->getFormAction($this->getParentObject(), 'deleteExercises'));
        $this->addCommandButton("initSelectExercise", $lng->txt("rep_robj_xtov_exc_add"));
        $this->addMultiCommand("removeExercises", $lng->txt("rep_robj_xtov_remove_from_overview"));

        $this->setShowRowsSelector(true);
        $this->setSelectAllCheckbox('exercise_ids[]');

        $this->initFilter();
        $this->setFilterCommand('applyExerciseFilter');
        $this->setResetCommand('resetExerciseFilter');
    }

    /**
     * 	Initialize the table filters.
     *
     * 	This method is called internally to initialize
     * 	the filters from present on the top of the table.
     */
    public function initFilter() {
        include_once 'Services/Form/classes/class.ilTextInputGUI.php';
        $tname = new ilTextInputGUI($this->lng->txt('rep_robj_xtov_test_list_flt_tst_name'), 'flt_tst_name');
        $tname->setSubmitFormOnEnter(true);
        $this->addFilterItem($tname);


        $tname->readFromSession();
        $this->filter['flt_tst_name'] = $tname->getValue();
    }

    /**
     * 	Fill a table row.
     *
     * 	This method is called internally by ilias to
     * 	fill a table row according to the row template.
     *
     * 	@param stdClass $item
     */
    protected function fillRow(stdClass $item) {
        /* Configure template rendering. */
        $this->tpl->setVariable('VAL_CHECKBOX', ilUtil::formCheckbox(false, 'exercise_ids[]', $item->obj_id));
        $this->tpl->setVariable('OBJECT_TITLE', $item->title);
        $this->tpl->setVariable('OBJECT_INFO', $this->getExercisePath($item));
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
    private function getExercisePath(stdClass $item) {
        /**
         * @var $tree ilTree
         */
        global $tree;

        $path_str = '';

        $path = $tree->getNodePath($item->ref_id);
        while ($node = current($path)) {
            $prepend = empty($path_str) ? '' : "{$path_str} > ";
            $path_str = $prepend . $node['title'];
            next($path);
        }
        $path_str = "<div><a href='ilias.php?baseClass=ilExerciseHandlerGUI&cmd=showOverview&ref_id=$item->ref_id'>" . $path_str . "</a></div>";

        return $path_str;
    }

}

?>
