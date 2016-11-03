<?php

/**
 * 
 * 
 */
require_once 'Customizing/global/plugins/Services/Repository/RepositoryObject/TestOverview/classes/GUI/class.ilTestOverviewTableGUI.php';
require_once 'Customizing/global/plugins/Services/Repository/RepositoryObject/TestOverview/classes/mapper/class.ilOverviewMapper.php';
class UserPanel 
    extends ilTestOverviewTableGUI{
    
     public function data(){
        
        
        global $lng, $ilCtrl, $ilUser;
        /*Initalise the Mapper*/
        $this-> setMapper(new ilOverviewMapper)
			  ->populate();
					
		$data = $this->getData();
                $this->tpl->addBlockFile("TBL_CONTENT", "tbl_content", 'tpl.test_overview_rows.html',
				$this->row_template_dir);
	
			foreach($data as $set)
			{
				$this->fillRow($set);
				$this->tpl->setCurrentBlock("tbl_content");
				$this->tpl->parseCurrentBlock();
			}
                        return     $this->tpl-> get();
              
     }
    
    
}

?>
