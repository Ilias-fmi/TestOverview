<?php

/* Dependencies : */
require_once 'Customizing/global/plugins/Services/Repository/RepositoryObject/TestOverview/classes/GUI/class.ilTestOverviewTableGUI.php';
require_once 'Customizing/global/plugins/Services/Repository/RepositoryObject/TestOverview/classes/mapper/class.ilOverviewMapper.php';
/**
 * The class creates a CSV file from the User Data that can be Downloaded 
 */
class ilCsvExportMapper {
    /** @var gender */
    var $gender;
    
    var $type;
    
    var $filter = array();
    
    var $format;
    
    var $filename;
    /**
    
     * @var ilDatabase
     */
    var $ilDB;
    
    
    /**
     * Constructor
     */
    public function __construct($type, $gender, $filter, $format) {
        /**
         * instantiate the variables with form input
         */
        $this->type=$type;
        $this->filter=$filter;
        $this->gender=$gender;
        $this->format=$format;
        
        $date = time();
        
        $this->inst_id = IL_INST_ID;
        
        $this->subdir = $date."__".$this->inst_id."__".$this->getType();
	$this->filename = $this->subdir.$this->getFormat();        
        
    }
    
    /**
     * @return string chosen type 
     */
    function getType(){
        switch ($this->type) {
            case "eo":
                return "eo"; break;
            case "to":
                return "to"; break;
         
        }
    }
    /** 
     * @return chosen format
     */
    function getFormat(){
        switch ($this->format) {
            case "CSV":
                return "csv"; break;

            case "XML":
                return "xml"; break;
        }
    }
    /**
     * @return string chosen gender
     */
    function getGender()
    {
        switch ($this->gender) {
            case "m":
                return "male"; break;
            case "f":
                return "female"; break;
            default:
                
                break;
        }
        
    }
    
    /**
     * Returns the directory where the export files are saved
     * @return string 
     */
    function getExportDir() 
    {
        include_once "./Services/Utilities/classes/class.ilUtil.php";
        $export_dir = ilUtil::getDataDir()."/xtov_data"."/to_".$this->getId()."/export";
        
        return $export_dir;
    }
    
    function buildExportFile()
    {
		switch ($this->type)
		{
			case "to":
				return $this->buildExportCSVFile();
				break;
                        case "eo":
				return $this->buildExportFileXML();
				break;
		}
    }
    
    function buildExportCSVFile() {
        
    }
    
}
    

 ?>
