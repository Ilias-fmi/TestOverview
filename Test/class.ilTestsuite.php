<?php
class testsuite {
   // Zusicherungen aktivieren und stumm schalten
    
public $html = "";
public $test_array = array ();
function  __construct(){
assert_options(ASSERT_ACTIVE, 1);
assert_options(ASSERT_WARNING, 0);
assert_options(ASSERT_QUIET_EVAL, 1);
assert_options(ASSERT_CALLBACK, 'my_assert_handler');


//Zum testen 

$Obj2 = new test ();
$Obj2-> expr = '1 == 2';


array_push($this-> test_array, $Obj2);
$this-> test_1 ();
}
// Eine Handlerfunktion erzeugen
function my_assert_handler($file, $line, $code)
{  
}



function assert_test($test) {
$bool = assert ($test-> expr);
if ($bool != 1){
     $this-> html .= '<p>' . $test-> comment . '</p>'  ;
    } 
}

function testIT(){
    foreach ($this-> test_array as $test){
        $this-> assert_test($test);
    }
    return $this-> html;
}

function test_1(){
    global $ilUser;
$Obj = new test ();
$soll = '			
    <div id="scoped-content">
    <style type="text/css" scoped="">

      td{
        text-align: center;
        -moz-border-radius: 20px;
        -webkit-border-radius: 20px;
        -khtml-border-radius: 20px;
        border-radius: 20px;
        padding: 6px;


      }
      .green-result
      {
      	background: #0c0;
      	color: #000;
      }

      .red-result
       {
         background: red;
         color: #000;
       }
      .grey{

      }
      table{
      margin-left:auto;
      margin-right:auto;

      }
      tr {
        background-color: #FCFCFC;
      }

      tr:nth-child(2n) {
        background-color: #F4F4F4;
      }


  </style>
  <div style="width:33.33%;float: left;">
      <table>
        <tbody><tr> <td>Test Name</td> <td>Erreichte Punkte</td> </tr>

        
        <tr>
          <td class=""> 456 </td> <td class="green-result">1</td>
        </tr>
        
        <tr>
          <td class=""> 456 </td> <td class="red-result">0</td>
        </tr>
        
        <tr>
          <td class=""> 789 </td> <td class="green-result">3</td>
        </tr>
        <tr>
          <td class=""> adfs </td> <td class="green-result">2</td>
        </tr>
        <tr>
          <td class=""> asdd </td> <td class="green-result">9</td>
        </tr>
        <tr>
          <td class=""> hohePunkte </td> <td class="green-result">777</td>
        </tr>
        <tr>
          <td class=""> hohePunkte </td> <td class="red-result">0</td>
        </tr>
      </tbody></table>
  </div>
</div>
<div style="width:33.33%;">
</div>
<div style="width: 33%; float: right;">
<p> Durchschnittliche Punkte : 158.4 </p>
<p> Durschnitt (Prozent) : 50.38 % </p>
</div>
';
        
         require_once ilPlugin::getPluginObject(IL_COMP_SERVICE, 'Repository', 'robj', 'TestOverview')
				->getDirectory() . '/classes/mapper/class.ilOverviewStudent.php';
$dataMapper = new studentMapper ();



$txt = $dataMapper-> getResults($ilUser->getId(),305). ' == ' . $soll;
str_replace(' ','',$soll);
str_replace(' ','',$txt);        

$Obj-> comment = strpos ("158.4",$soll);
    array_push($this-> test_array, $Obj);
}


}
class test{
    public $expr;
    public $comment; 
}


?>
