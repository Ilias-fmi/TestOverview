<?php
/*
*Mapps the given ExerciseOverview id to the members
*/

/*Includes the DB Mapper*/
require_once ilPlugin::getPluginObject(IL_COMP_SERVICE, 'Repository', 'robj', 'TestOverview')
				->getDirectory() . '/classes/mapper/class.ilDataMapper.php';

class ilMembershipMapperEx extends ilDataMapper
{
	protected $tableName = "exc_members";
	//protected $ExObjId = -1;

	/*Gets the ID of the ExersiceOverview Object*/
	//public function __construct()
//{
	// $ExObjId = $session_key;
//}
	public function getSelectPart()
	{
			$field = " DISTINCT exc_members.usr_id ";
			return $field;

	}

	public function getFromPart()
	{
			$join = " exc_members ,rep_robj_xtov_e2o ";
	return $join;

	}

	public function getWherePart(array $filters)
	{
		global $ilUser;
		$condition = " obj_id_overview = 300 ";//. $ExObjId;
    return $condition;


	}

}



?>
