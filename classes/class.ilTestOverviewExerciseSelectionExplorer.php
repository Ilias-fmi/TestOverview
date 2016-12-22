<?php
require_once 'Services/Object/classes/class.ilPasteIntoMultipleItemsExplorer.php';

class ilTestOverviewExerciseSelectionExplorer extends ilPasteIntoMultipleItemsExplorer{
    public function __construct($session_key)
	{
		parent::__construct(ilPasteIntoMultipleItemsExplorer::SEL_TYPE_CHECK, 'ilias.php?baseClass=ilRepositoryGUI&cmd=goto', $session_key);
		$this->removeFormItemForType('root');
		$this->removeFormItemForType('crs');
		$this->removeFormItemForType('grp');
		$this->removeFormItemForType('cat');
		$this->removeFormItemForType('fold');
		$this->addFormItemForType('exc');
		$this->addFilter('exc');
	}
    
  
    
}


?>
