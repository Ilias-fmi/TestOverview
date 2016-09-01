<?php
include_once("./Services/Repository/classes/class.ilObjectPlugin.php");

/**
* Application class for example repository object.
*/
class ilObjExample extends ilObjectPlugin
{
        /**
        * Constructor
        *
        * @access        public
        */
        function __construct($a_ref_id = 0)
        {
                parent::__construct($a_ref_id);
        }

        /**
        * Get type.
        */
        final function initType()
        {
                $this->setType("xexo");
        }

        /**
        * Create object
        */
        function doCreate()
        {
                global $ilDB;

                $ilDB->manipulate("INSERT INTO rep_robj_xtov_exview ".
                        "(Obj_id) VALUES (".
                        $ilDB->quote($this->getId(), "integer")
                        .")");
        }

        /**
        * Read data from db
        */
        function doRead()
        {
                global $ilDB;

                $set = $ilDB->query("SELECT * FROM rep_robj_xtov_exview ".
                        " WHERE id = ".$ilDB->quote($this->getId(), "integer")
                        );
        }

        /**
        * Update data
        */
        function doUpdate()
        {
                global $ilDB;

                $ilDB->manipulate($up = "UPDATE rep_robj_xexo_data SET ".
                        " is_online = ".$ilDB->quote($this->getOnline(), "integer").",".
                        " option_one = ".$ilDB->quote($this->getOptionOne(), "text").",".
                        " option_two = ".$ilDB->quote($this->getOptionTwo(), "text").
                        " WHERE id = ".$ilDB->quote($this->getId(), "integer")
                        );
        }

        /**
        * Delete data from db
        */
        function doDelete()
        {
                global $ilDB;

                $ilDB->manipulate("DELETE FROM rep_robj_xexo_data WHERE ".
                        " id = ".$ilDB->quote($this->getId(), "integer")
                        );
        }

        /**
        * Do Cloning
        */
        function doClone($a_target_id,$a_copy_id,$new_obj)
        {
                global $ilDB;

                $new_obj->setOnline($this->getOnline());
                $new_obj->setOptionOne($this->getOptionOne());
                $new_obj->setOptionTwo($this->getOptionTwo());
                $new_obj->update();
        }

//
// Set/Get Methods for our example properties
//

        /**
        * Set online
        *
        * @param        boolean                online
        */
        function setOnline($a_val)
        {
                $this->online = $a_val;
        }

        /**
        * Get online
        *
        * @return        boolean                online
        */
        function getOnline()
        {
                return $this->online;
        }


}
?>

?>
