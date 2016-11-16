<?php
require_once 'Services/Repository/classes/class.ilObjectPluginListGUI.php';


class ilObjExerciseOverviewListGUI
    extends ilObjectPluginListGUI {
    
   /**
	 * @return string
	 */
	public function getGuiClass()
	{
		return 'ilObjTestOverviewGUI';
	}

	/**
	 * @return array
	 */
	public function initCommands()
	{
		return array
		(
			array(
				'permission' => 'read',
				'cmd'        => 'showContent',
				'default'    => true
			),
			array(
				'permission' => 'write',
				'cmd'        => 'editSettings',
				'txt'        => $this->txt('edit'),
				'default'    => false
			),
		);
	}

	/**
 	 *	Set the plugin type.
	 */
	public function initType()
	{
    $this->setType('xtov');
	} 
    
    
    
    
}


?>
