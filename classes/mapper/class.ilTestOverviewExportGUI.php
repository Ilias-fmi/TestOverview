<?php

/**
 * The class creates a CSV file from the User Data that can be downloaded 
 */

/**
 * @ilCtrl_isCalledBy ilTestOverviewExportGUI: ilObjTestOverviewGUI
 */
class ilTestOverviewExportGUI extends ilObjTestOverviewGUI {

    /**
     * 	@var ilPropertyFormGUI
     */
    protected $form;
    protected $parent;
    protected $id;
    public $ref_id;
    protected $file = null;

    /**
     * Constructor
     */
    public function __construct($a_parent_gui, $id, $a_main_object = null) {
        parent::__construct($a_parent_gui, $a_main_object);
        $this->parent = $a_parent_gui;
        $this->id = $id;
        $this->ref_id = $this->object->getRefId();
        $this->export();
    }

    function &performCommand() {
        global $ilCtrl;
        $next_class = $ilCtrl->getNextClass($this);
        $cmd = $ilCtrl->getCmd();
        switch ($next_class) {
            default :
                switch ($cmd) {

                    case 'deliverData':
                    case 'triggerExport':
                    case 'export': $this->$cmd();
                        break;
                }
        }
    }

    public function export() {
        global $tpl, $ilCtrl;


        /* initialize Export form */
        $form = $this->initExportForm();

        /* Populate template */
        $tpl->setContent($form->getHTML());
    }

    protected function triggerExport() {
        global $tpl, $lng, $ilCtrl;
        if ($this->form->checkInput()) {
            $export_type = $this->form->getInput("export_type");
            require_once 'Customizing/global/plugins/Services/Repository/RepositoryObject/TestOverview/classes/GUI/class.ilTestOverviewExport.php';
            $to_exp = new ilTestOverviewExport($this->parent, $this->id, $export_type);
            $to_exp->buildExportFile();
        }
    }

    protected function initExportForm() {

        global $ilCtrl, $tpl, $lng;
        include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");


        $this->form = new ilPropertyFormGUI();
        $this->form->setTitle("Export " . $this->txt("properties"));
        $this->form->setFormAction($ilCtrl->getFormAction($this, 'triggerExport'));

        //radio group: Export type
        $checkbox_overview = new ilRadioGroupInputGUI("Type", "export_type");
        $overview_op = new ilCheckboxOption($this->txt("reduced"), "reduced", $this->txt("reduced_exp"));
        $overview_op2 = new ilCheckboxOption($this->txt("extended"), "extended", $this->txt("extended_exp"));

        $checkbox_overview->addOption($overview_op);
        $checkbox_overview->addOption($overview_op2);
        $checkbox_overview->setRequired(true);


        $this->form->addItem($checkbox_overview);

        $this->form->addCommandButton("triggerExport", "Export");

        return $this->form;
    }

}

?>
