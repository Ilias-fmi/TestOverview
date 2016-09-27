<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */


/*
 *
 *
 */

class BinDiagrammMapper {

    public function getDataArray() {
        $table = new ilTestOverviewTableGUI( $this, 'showContent' );
        $table->setMapper(new ilOverviewMapper)
        ->populate();
        $Html = $table->getHTML();
    }

    public function addData(){
    
}

}

?>
