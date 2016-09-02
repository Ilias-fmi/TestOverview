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
	protected $Id;

  /*Is required for all type of Querys */


	public function __construct($ExObjId)
	{
		/**
		 * @var $ilDB ilDB
		 */
		global $ilDB;

		$this->db = $ilDB;

		$this->Id = $ExObjId ;
	}


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
		$condition = " obj_id_overview = " .$this->Id ;
    return $condition;


	}

}



?>
