<?php

//require_once ilPlugin::getPluginObject(IL_COMP_SERVICE, 'Repository', 'robj', 'TestOverview')
	//			->getDirectory() . '/classes/mapper/class.ilDataMapper.php';

include 'class.ilDataMapper.php';
class ilDB
	extends ilDataMapper
{

	public function getSelectPart(){}
	public function getFromPart(){}
	public function getWherePart(array $filters){}
	public function doQuery(){
		echo "funktion aufgerufen";
		$result = $ilDB->query("SELECT * FROM usr_data");
		echo "j";
		$this->db->query("SELECT * FROM usr_data");
	}
}
echo "Anfang";
$mapper = new ilDB();
echo "Klasse erstellt";
$DB = $mapper->dbZurückgeben();
echo "Datenbank in Variable";
$mapper-> doQuery();
echo "Query ausgeführt";
?>
