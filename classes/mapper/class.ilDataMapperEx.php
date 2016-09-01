<?php
/*
*Mapps the Members to the Exercises
*
*/

/*Includes the DB Mapper*/
require_once ilPlugin::getPluginObject(IL_COMP_SERVICE, 'Repository', 'robj', 'TestOverview')
				->getDirectory() . '/classes/mapper/class.ilDataMapper.php';

class ilMembershipMapperEx extends ilDataMapper
{
	protected $tableName = "exc_members";


	public function getSelectPart()
	{
			$field = "DISTINCT exc_members.usr_id ";
			return $field;

	}

	public function getFromPart()
	{
			$join = "JOIN rep_robj_xtov_exview ON exc_members.usr_id";
	return $this->tableName . " ". $join		;

	}

	public function getWherePart(array $filters)
	{
		global $ilUser;
		$condition = $this->tableName . ".obj_id = ". $this->db->quote($filters['overview_id'], 'integer');
    return $condition;


	}

}



?>
