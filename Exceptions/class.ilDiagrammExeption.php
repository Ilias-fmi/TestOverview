<?php
/* Copyright (c) 1998-2011 ILIAS open source, Extended GPL, see docs/LICENSE */
 
require_once 'Services/Exceptions/classes/class.ilException.php'; 
 
/** 
 * Class for advanced editing exception handling in ILIAS. 
 * 
 * @author jan <mjansen@databay.de>
 * @version $Id$ 
 * 
 */
class ilDiagrammException extends ilException
{
        /** 
         * Constructor
         * 
         * A message is not optional as in build in class Exception
         * 
         * @param        string $a_message message
         */
        public function __construct($a_message)
        {
                 parent::__construct($a_message);
        }
}
?>
