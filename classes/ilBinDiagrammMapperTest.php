<?php

class BinDiagrammMapperTest extends PHPUnit_Framework_TestCase{
    public $toTest ;
    public function setup(){
        require_once '/var/www/html/iliasTO/Customizing/global/plugins/Services/Repository/RepositoryObject/TestOverview/classes/mapper/class.ilBinDiagrammMapper.php';
        $toTest = new BinDiagrammMapper();
    }
    public function testSplit(){
        $txtToTest = "root user 100.00 % 100.00% | jo jo 100.00 % 100.00% |";
        $result = $toTest -> testSplit($txtToTest);
        $this->assertEquals ($result[0],"root user 100.00 % 100.00% ");
        $this->asserEquals ($result[1]," jo jo 100.00 % 100.00% ");
        $this-> assertEquals ( sizeof($result),2);
    }
    
    
    
}



?>
