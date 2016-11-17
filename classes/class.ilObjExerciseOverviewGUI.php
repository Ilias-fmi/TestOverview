<?php
require_once 'Services/Repository/classes/class.ilObjectPluginListGUI.php';
require_once 'Customizing/global/plugins/Services/Repository/RepositoryObject/TestOverview/classes/GUI/class.ilTestOverviewTableGUI.php';



class ilObjExerciseOverviewListGUI
    extends ilTestOverviewTableGUI{
    
    public function __construct(){
        
    }
     /*
     * Gets the Data from every Student and there Testresults in a String saperated by "|"
     * 
     */
    public function data(){
        global $ilDB, $tpl ;
        require_once ilPlugin::getPluginObject(IL_COMP_SERVICE, 'Repository', 'robj', 'TestOverview')
				->getDirectory() . '/classes/mapper/class.ilExerciseMapper.php';
        $mapper = new ilExerciseMapper;
        $data = $mapper-> getArrayofObjects ();
        // Nur als Platzhalter 
        $tpl = new ilTemplate("tpl.test_exerciseview_row.html", true, true, "Customizing/global/plugins/Services/Repository/RepositoryObject/TestOverview");
        
       foreach($data as $set){
        $tpl->setCurrentBlock("my_block");
        $tpl->setVariable("TEXT", "der");
        $tpl->setVariable("VALUE", "text") ;
        $tpl->parseCurrentBlock();
        }
        $tpl->show();
        return $tpl-> get();
        
    }
    
    
}


?>
