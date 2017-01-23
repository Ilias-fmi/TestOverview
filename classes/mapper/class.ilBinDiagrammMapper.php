<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */


/*
 * Class gets the Results in a String and Maps it to the Diagramms
 * @package	TestOverview repository plugin
 * 
 */

/* Dependencies : */
require_once 'Customizing/global/plugins/Services/Repository/RepositoryObject/TestOverview/classes/GUI/class.ilTestOverviewTableGUI.php';
require_once 'Customizing/global/plugins/Services/Repository/RepositoryObject/TestOverview/classes/mapper/class.ilOverviewMapper.php';

/* Required Exeptions */
require_once 'Customizing/global/plugins/Services/Repository/RepositoryObject/TestOverview/Exceptions/class.ilDiagrammExeption.php';

class BinDiagrammMapper extends ilTestOverviewTableGUI {

    //Name+Results+Sum of Results for every Student as a String
    private $result = null;
    public $students = array();
    private $rawData = array();

    public function createAverageDia($type) {
        $this->data();

        foreach ($this->rawData as $student) {
            $this->seperate($student);
        }
        switch ($type) {
            case "BARS":
                $averageObj = new AverageDiagramm($this->students);
                return $averageObj->initDia();
            case "PIE":
                $averageObj = new PieAverageDiagramm($this->students);
                return $averageObj->initDia();
        }
    }

    /*
     * Gets the Data from every Student and there Testresults in a String saperated by "|"
     * 
     */

    public function data() {


        global $lng, $ilCtrl, $ilUser;
        /* Initalise the Mapper */
        $this->setMapper(new ilOverviewMapper)
                ->populate();

        $data = $this->getData();

        $this->tpl->addBlockFile("TBL_CONTENT", "tbl_content", 'tpl.test_overview_rows.html', $this->row_template_dir);

        foreach ($data as $set) {
            $this->fillRow($set);
            $this->tpl->setCurrentBlock("tbl_content");
            $this->tpl->parseCurrentBlock();
        }

        try {
            $this->splitStudent($this->tpl->get());
        } catch (ilDiagrammExeption $e) {
            throw new ilDiagrammException('Cannot split String');
        }
    }

    public function getResult() {

        return $result;
    }

    /**
     * Splits the String by the | so that seperate can Create an Obj. of every Student
     * @param type $string
     */
    private function splitStudent($string) {
        $this->rawData = explode("|", $string);
    }

    /*
     * Splits the Sting into Name/Results/Average
     * @return Students Object with Name/Results/Average set
     */

    private function seperate($string) {
        $string = str_replace("Nicht teilgenommen", "00.00", $string);
        $string = str_replace("|", "", $string);
        $string = str_replace("%", "", $string);
        $temp = explode("#", $string);
        $name = $temp[0];
        $ende = explode(" ", end($temp));
        $average = $ende[1];
        array_splice($temp, 0, 1);
        array_splice($temp, count($temp) - 1);
        array_push($temp, $ende[0]);
        $student = new Student ();
        $student->setName($name);
        $student->setResults($temp);
        $student->setAverage($average);
        array_push($this->students, $student);
    }

}

/*
 * Object for every Student with Name and Results
 * 
 * 
 */

class Student {

    private $name = "";
    private $results = array();
    private $average = 0.0;

    /* Getter und Setter für Results und Average des Studenten */

    public function getResults() {
        return $this->results;
    }

    public function setResults($result) {
        $temp = array();
        foreach ($result as $part) {
            array_push($temp, (float) $part);
        }
        $this->results = $temp;
    }

    public function getAverage() {
        return $this->average;
    }

    public function setAverage($average) {
        $this->average = (float) $average;
    }

    public function getName() {
        return $this->name;
    }

    public function setName($name) {
        $this->name = $name;
    }

}

/**
 * Creats a Bar Diagramm that shows the number of students with there Points in a Range of 10 Percent
 */
class AverageDiagramm {

    private $studentObject;
    public $diaPoints = array();
    private $buckets = array(0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0);

    function __construct($Obj) {
        $this->studentObject = $Obj;
        $this->getAverage();
    }

    function initDia() {
        require_once 'Services/Chart/classes/class.ilChartGrid.php';
        require_once 'Services/Chart/classes/class.ilChartLegend.php';
        require_once 'Services/Chart/classes/class.ilChartSpider.php';
        require_once 'Services/Chart/classes/class.ilChartLegend.php';
        $chart = ilChart::getInstanceByType(ilChart::TYPE_GRID, $a_id);
        $chart->setsize(900, 400);
        $data = $chart->getDataInstance(ilChartGrid::DATA_BARS);




        /* Creation of the Legend */
        $legend = new ilChartLegend();
        $legend->setOpacity(50);
        $chart->setLegend($legend);
        $chart->setYAxisToInteger(true);
        $legend = $this->legend();
        /* Width of the colums */
        $data->setBarOptions(0.5, "center");

        $data->addPoint(1, $this->buckets[0]);
        $data->addPoint(2, $this->buckets[1]);
        $data->addPoint(3, $this->buckets[2]);
        $data->addPoint(4, $this->buckets[3]);
        $data->addPoint(5, $this->buckets[4]);
        $data->addPoint(6, $this->buckets[5]);
        $data->addPoint(7, $this->buckets[6]);
        $data->addPoint(8, $this->buckets[7]);
        $data->addPoint(9, $this->buckets[8]);
        $data->addPoint(10, $this->buckets[9]);
        $chart->addData($data);
        return "<div style=\"margin:10px\"><table><tr valign=\"bottom\"><td>" . $chart->getHTML() . "</td><td class=\"small\" style=\"padding-left:15px\">" . $legend . "</td></tr></table></div>";
    }

    function legend() {
        $legend = "<div style ='background-color: #EDC240;opacity: 0.8;margin-top: 6px;'>";
        $legend .= "<table>";
        $legend .= "<tr valign=\"top\"><td>Nummer</td><td>|Punkte</td></tr>";
        $legend .= "<tr valign=\"top\"><td>1</td><td>| 0%-10%</td></tr>";
        $legend .= "<tr valign=\"top\"><td>2</td><td>| 11%-20%</td></tr>";
        $legend .= "<tr valign=\"top\"><td>3</td><td>| 21%-30%</td></tr>";
        $legend .= "<tr valign=\"top\"><td>4</td><td>| 31%-40%</td></tr>";
        $legend .= "<tr valign=\"top\"><td>5</td><td>| 41%-50%</td></tr>";
        $legend .= "<tr valign=\"top\"><td>6</td><td>| 51%-60%</td></tr>";
        $legend .= "<tr valign=\"top\"><td>7</td><td>| 61%-70%</td></tr>";
        $legend .= "<tr valign=\"top\"><td>8</td><td>| 71%-80%</td></tr>";
        $legend .= "<tr valign=\"top\"><td>9</td><td>| 81%-90%</td></tr>";
        $legend .= "<tr valign=\"top\"><td>10</td><td>| 91%-100%</td></tr>";
        $legend .= "</table>";
        $legend .= "</div>";
        return $legend;
    }

    function getAverage() {
        foreach ($this->studentObject as $student) {
            $this->fillBuckets($student->getAverage());
        }
    }

    function fillBuckets($average) {
        if ($average > 0.0 && $average <= 10.00) {
            $this->buckets[0] ++;
        } else if ($average > 10.00 && $average <= 20.00) {
            $this->buckets[1] ++;
        } else if ($average > 20.00 && $average <= 30.00) {
            $this->buckets[2] ++;
        } else if ($average > 30.00 && $average <= 40.00) {
            $this->buckets[3] ++;
        } else if ($average > 40.00 && $average <= 50.00) {
            $this->buckets[4] ++;
        } else if ($average > 50.00 && $average <= 60.00) {
            $this->buckets[5] ++;
        } else if ($average > 60.00 && $average <= 70.00) {
            $this->buckets[6] ++;
        } else if ($average > 70.00 && $average <= 80.00) {
            $this->buckets[7] ++;
        } else if ($average > 80.00 && $average <= 90.00) {
            $this->buckets[8] ++;
        } else if ($average > 90.00 && $average <= 100.00) {
            $this->buckets[9] ++;
        } else {
            //last index checks vor errors 
            $this->buckets[10] ++;
        }
    }

}

/**
 * Creats a Pie Diagramm that shows the number of students with there Points in a Range of 10 Percent
 */
class PieAverageDiagramm extends AverageDiagramm {

    private $studentObject;
    public $diaPoints = array();
    private $buckets = array(0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0);

    function __construct($Obj) {
        $this->studentObject = $Obj;
        $this->getAverage();
    }

    function initDia() {
        require_once 'Services/Chart/classes/class.ilChartGrid.php';
        require_once 'Services/Chart/classes/class.ilChartLegend.php';
        require_once 'Services/Chart/classes/class.ilChartSpider.php';
        require_once 'Services/Chart/classes/class.ilChartLegend.php';
        require_once 'Services/Chart/classes/class.ilChartDataPie.php';
        $chart = ilChart::getInstanceByType(ilChart::TYPE_PIE, $a_id);
        $chart->setsize(900, 400);
        $data = $chart->getDataInstance();




        /* Creation of the Legend */
        $legend = new ilChartLegend();
        $legend->setOpacity(50);
        $chart->setLegend($legend);
        $legend = $this->legend();
        /* Width of the colums */

        if ($this->buckets[0] > 0) {
            $data->addPoint(10, $this->buckets[0]);
        }
        if ($this->buckets[1] > 0) {
            $data->addPoint(20, $this->buckets[1]);
        }
        if ($this->buckets[2] > 0) {
            $data->addPoint(30, $this->buckets[2]);
        }
        if ($this->buckets[3] > 0) {
            $data->addPoint(40, $this->buckets[3]);
        }
        if ($this->buckets[4] > 0) {
            $data->addPoint(50, $this->buckets[4]);
        }
        if ($this->buckets[5] > 0) {
            $data->addPoint(60, $this->buckets[5]);
        }
        if ($this->buckets[6] > 0) {
            $data->addPoint(70, $this->buckets[6]);
        }
        if ($this->buckets[7] > 0) {
            $data->addPoint(80, $this->buckets[7]);
        }
        if ($this->buckets[8] > 0) {
            $data->addPoint(90, $this->buckets[8]);
        }
        if ($this->buckets[9] > 0) {
            $data->addPoint(100, $this->buckets[9]);
        }
        $chart->addData($data);
        return $chart->getHTML();
    }

    function fillBuckets($average) {
        if ($average > 0.0 && $average <= 10.00) {
            $this->buckets[0] ++;
        } else if ($average > 10.00 && $average <= 20.00) {
            $this->buckets[1] ++;
        } else if ($average > 20.00 && $average <= 30.00) {
            $this->buckets[2] ++;
        } else if ($average > 30.00 && $average <= 40.00) {
            $this->buckets[3] ++;
        } else if ($average > 40.00 && $average <= 50.00) {
            $this->buckets[4] ++;
        } else if ($average > 50.00 && $average <= 60.00) {
            $this->buckets[5] ++;
        } else if ($average > 60.00 && $average <= 70.00) {
            $this->buckets[6] ++;
        } else if ($average > 70.00 && $average <= 80.00) {
            $this->buckets[7] ++;
        } else if ($average > 80.00 && $average <= 90.00) {
            $this->buckets[8] ++;
        } else if ($average > 90.00 && $average <= 100.00) {
            $this->buckets[9] ++;
        } else {
            //last index checks vor errors 
            $this->buckets[10] ++;
        }
    }

    function getAverage() {
        foreach ($this->studentObject as $student) {
            $this->fillBuckets($student->getAverage());
        }
    }

}
/**
 * Creats a Bar diagramm for exercises.
 * The Bucketsize is calculatet by a given value from the user 
 */

class exerciseCharts {

    private $buckets = array(0, 0, 0, 0, 0, 0, 0, 0, 0, 0);
    private $bucketSize = array ("","","","","","","","","","");
    private $data = null;
    private $diagramSize = 0;
    private $overviewId;
    private $error = false;

    function __construct($diagramSize, $overviewId) {
        $this->diagramSize = $diagramSize;
        $this->overviewId = $overviewId;

        require_once ilPlugin::getPluginObject(IL_COMP_SERVICE, 'Repository', 'robj', 'TestOverview')
                        ->getDirectory() . '/classes/mapper/class.ilExerciseMapper.php';
        $excMapper = new ilExerciseMapper();
        $this->data = $excMapper->getTotalScores($overviewId);
        if ($this->diagramSize > 0 && !empty($this->data)) {
            $this->fillBuckets();
        }
    }

    function fillBuckets() {
        $size = $this->diagramSize / 10;
        foreach ($this->data as $value) {
            if ($value < $size ) {
                $this->buckets[0] ++;
            } else if ($value >= $size && $value < ($size * 2)) {
                $this->buckets[1] ++;
            } else if ($value >= ($size * 2) && $value < ($size * 3)) {
                $this->buckets[2] ++;
            } else if ($value >= ($size * 3) && $value < ($size * 4)) {
                $this->buckets[3] ++;
            } else if ($value >= ($size * 4) && $value < ($size * 5)) {
                $this->buckets[4] ++;
            } else if ($value >= ($size * 5) && $value < ($size * 6)) {
                $this->buckets[5] ++;
            } else if ($value >= ($size * 6) && $value < ($size * 7)) {
                $this->buckets[6] ++;
            } else if ($value >= ($size * 7) && $value < ($size * 8)) {
                $this->buckets[7] ++;
            } else if ($value >= ($size * 8) && $value < ($size * 9)) {
                $this->buckets[8] ++;
            } else if ($value >= ($size * 9) && $value < ($size * 10)) {
                $this->buckets[9] ++;
            }
        }
    }

    function getHTML() {
        global $lng;
        require_once 'Services/Chart/classes/class.ilChartGrid.php';
        require_once 'Services/Chart/classes/class.ilChartLegend.php';
        require_once 'Services/Chart/classes/class.ilChartSpider.php';
        require_once 'Services/Chart/classes/class.ilChartLegend.php';
        require_once 'Services/Chart/classes/class.ilChartDataPie.php';
        $this-> calcBuckets();
        $chart = ilChart::getInstanceByType(ilChart::TYPE_GRID, 1);
        $chart->setsize(900, 400);
        $data = $chart->getDataInstance(ilChartGrid::DATA_BARS);
        $lng->loadLanguageModule("bibitem");
        $lng->loadLanguageModule("assessment");


        $data->setBarOptions(2, "center");
        /* Creation of the Legend */
        
        $tpl = new ilTemplate("tpl.DigramLegend.html", true, true, "Customizing/global/plugins/Services/Repository/RepositoryObject/TestOverview");
        $tpl->setVariable("number",$lng->txt("bibitem_number"));
        $tpl->setVariable("percent",$lng->txt("tst_percent_solved"));
        $i=10;
        foreach ($this->bucketSize as $bucket){
            $tpl->setCurrentBlock("buckets");
            $tpl->setVariable("Numbers",$i);
            $tpl->setVariable("Percents",$bucket);
            $tpl->parseCurrentBlock();
            $i += 10;
        }
        $legend = new ilChartLegend();
        $legend->setOpacity(50);
        $chart->setLegend($legend);
        $chart->setYAxisToInteger(true);
       

        if ($this->buckets[0] > 0) {
            $data->addPoint(10, $this->buckets[0]);
        }
        if ($this->buckets[1] > 0) {
            $data->addPoint(20, $this->buckets[1]);
        }
        if ($this->buckets[2] > 0) {
            $data->addPoint(30, $this->buckets[2]);
        }
        if ($this->buckets[3] > 0) {
            $data->addPoint(40, $this->buckets[3]);
        }
        if ($this->buckets[4] > 0) {
            $data->addPoint(50, $this->buckets[4]);
        }
        if ($this->buckets[5] > 0) {
            $data->addPoint(60, $this->buckets[5]);
        }
        if ($this->buckets[6] > 0) {
            $data->addPoint(70, $this->buckets[6]);
        }
        if ($this->buckets[7] > 0) {
            $data->addPoint(80, $this->buckets[7]);
        }
        if ($this->buckets[8] > 0) {
            $data->addPoint(90, $this->buckets[8]);
        }
        if ($this->buckets[9] > 0) {
            $data->addPoint(100, $this->buckets[9]);
        }
        $chart->addData($data);
        $tpl->setVariable("diagram",$chart->getHTML());
        return $tpl-> get();
        // return $this-> buckets;
    }
    
    function calcBuckets(){
        $size = $this->diagramSize / 10;
        $this->bucketSize[0] = "&le; " . $size ;
        $this->bucketSize[1] = "&le; " . $size*2;
        $this->bucketSize[2] = "&le; " . $size*3;
        $this->bucketSize[3] = "&le; " . $size*4;
        $this->bucketSize[4] = "&le; " . $size*5;
        $this->bucketSize[5] = "&le; " . $size*6;
        $this->bucketSize[6] = "&le; " . $size*7;
        $this->bucketSize[7] = "&le; " . $size*8;
        $this->bucketSize[8] = "&le; " . $size*9;
        $this->bucketSize[9] = "&le; " . $size*10;
    }

}


?>
