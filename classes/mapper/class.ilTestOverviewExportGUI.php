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
    /**
     * Constructor
     */
    public function __construct($a_parent_gui, $id ,$a_main_object= null) {
               parent::__construct($a_parent_gui,$a_main_object);
               $this->parent=$a_parent_gui;
               $this->id = $id;
               $this->export();
               
    }
    
    function &performCommand($cmd)
	{
		
               
                //echo $cmd; 
                
		//$next_class = $this->ctrl->getNextClass($this);

		switch ($cmd){
                    
                    case 'triggerExport': $this->triggerExport();
                        break;
                    case 'export': $this->export();
                        break;
                    
                }
                $this->addHeaderAction();
	}
        
     
    
    public function export() {
        global $tpl;
        
        
        /* initialize Export form */
        $this->initExportForm();

        /* Populate template */
        $tpl->setContent($this->form->getHTML());
        
        
    }

    protected function triggerExport() {
        global $tpl, $lng, $ilCtrl;
        if ($this->form->checkInput()) {
            $export_type = $this->form->getInput("export_type");
            require_once 'Customizing/global/plugins/Services/Repository/RepositoryObject/TestOverview/classes/GUI/class.ilTestOverviewExport.php';;
            $to_exp = new ilTestOverviewExport($this->id,$export_type);
            $to_exp->buildExportFile();  
            //$this->object->update();
            ilUtil::sendSuccess('Exportfile created');
            
            
        }
            $this->form->setValuesByPost();
            $tpl->setContent($this->form->getHTML());
         
        
        //$ilCtrl->redirect($this, 'export');
    }

    protected function initExportForm() {
   
        global $ilCtrl, $tpl;
        include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
        

        $this->form = new ilPropertyFormGUI();
        $this->form->setTitle("Export properties");
        $this->form->setFormAction($ilCtrl->getFormAction($this, 'triggerExport'));

        //radio group: Export type
        $checkbox_overview = new ilRadioGroupInputGUI("Type", "export_type");
        $overview_op = new ilCheckboxOption("Reduced", "reduced", "Export the results for all tests and exercises linked to the TestOverview Object ");
        $overview_op2 = new ilCheckboxOption("Extended", "extended", "Export the results for all questions for every test and for all assignments for all exercises");

        $checkbox_overview->addOption($overview_op);
        $checkbox_overview->addOption($overview_op2);
        $checkbox_overview->setRequired(true);


        $this->form->addItem($checkbox_overview);

        $this->form->addCommandButton("triggerExport", "Export");

        $tpl->setContent($this->form->getHTML());
    }
    
    
}

 ?>
