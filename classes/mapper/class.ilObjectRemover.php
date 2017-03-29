<?

/*
*Removes all entries of an TestOverview Object
*
*/
require_once ilPlugin::getPluginObject(IL_COMP_SERVICE, 'Repository', 'robj', 'TestOverview')
->getDirectory() . '/classes/mapper/class.ilDataMapper.php';

class ilObjectRemover
extends ilDataMapper
{
protected $ObjId ;

/*
*
* @param Integer $id -> Id of the  OverviewObject that is deleted
*/
public function __construct($Id)
{
/**
* @var $ilDB ilDB
*/
global $ilDB;

$this->db = $ilDB;
$this->ObjId;
}



}
