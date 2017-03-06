<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of rankGui
 *
 * @author Dinkel
 */
class rankGui 
extends ilMappedTableGUI
{
	private $accessIndex = array();
	
	/**
	 *	 @var	array
	 */
	protected $filter = array();

	/**
	 * @var array
	 */
	protected $linked_tst_column_targets = array();

	/**
	 *	Constructor logic.
	 *
	 *	This table GUI constructor method initializes the
	 *	object and configures the table rendering.
	 */
	public function __construct( ilObjectGUI $a_parent_obj, $a_parent_cmd )
	{
		/**
		 *	@var ilCtrl	$ilCtrl
		 */
		global $ilCtrl, $tpl, $ilAccess,$lng;
                $lng->loadLanguageModule("administration");

		/* Pre-configure table */
		$this->setId(sprintf(
			"test_overview_%d", $a_parent_obj->object->getId()));

		$this->setDefaultOrderDirection('ASC');
		$this->setDefaultOrderField('obj_id');
		
		// ext ordering with db is ok, but ext limiting with db is not possible,
		// since the rbac filtering is downstream to the db query
		$this->setExternalSorting(true);
		$this->setExternalSegmentation(false);

        parent::__construct($a_parent_obj, $a_parent_cmd);

		$this->setTitle("ExerciseOverview");

		$overview = $this->getParentObject()->object;

		$this->addColumn($this->lng->txt('rep_robj_xtov_test_overview_hdr_user'));
                
                //get Object_reference mapper from Exercise Overview
                require_once ilPlugin::getPluginObject(IL_COMP_SERVICE, 'Repository', 'robj', 'TestOverview')
                ->getDirectory() . '/classes/mapper/class.ilExerciseMapper.php';
                $excMapper = new ilExerciseMapper();
		
                $dataArray=$excMapper->getUniqueExerciseId($overview->getID());
                for ($index = 0; $index < count($dataArray); $index++) {
                $obj_id=$dataArray[$index];
			
                        $this->addColumn( $excMapper->getExerciseName($obj_id),$ilCtrl->getLinkTargetByClass('ilobjtestgui', 'infoScreen'));
                        
		}
                $this->lng->loadLanguageModule("trac");
		$this->addColumn($this->lng->txt('stats_summation'));
		$plugin = ilPlugin::getPluginObject(IL_COMP_SERVICE, 'Repository', 'robj', 'TestOverview');
		$this->setRowTemplate('tpl.test_overview_row.html', $plugin->getDirectory());
		$this->setDescription($this->lng->txt("rep_robj_xtov_test_overview_description"));
		$cssFile = $plugin->getDirectory() . "/templates/css/testoverview.css";
		$tpl->addCss($cssFile);

		/* Configure table filter */
		$this->initFilter();
		$this->setFilterCommand("applyExerciseFilterRanking");
		$this->setResetCommand("resetExerciseFilterRanking");

		$this->setFormAction($ilCtrl->getFormAction($this->getParentObject(), 'subTabEoRanking') );
	}

	/**
	 *	Initialize the table filters.
	 *
	 *	This method is called internally to initialize
	 *	the filters from present on the top of the table.
	 */
	public function initFilter()
    {
        include_once 'Services/Form/classes/class.ilTextInputGUI.php';
        include_once 'Services/Form/classes/class.ilSelectInputGUI.php';
		include_once ilPlugin::getPluginObject(IL_COMP_SERVICE, 'Repository', 'robj', 'TestOverview')
						->getDirectory() . "/classes/mapper/class.ilOverviewMapper.php";

		/* Configure participant name filter (input[type=text]) */
        $pname = new ilTextInputGUI($this->lng->txt('rep_robj_xtov_overview_flt_participant_name'), 'flt_participant_name');
        $pname->setSubmitFormOnEnter(true);
        /* Martins code für gender filter */        
        $pgender = new ilSelectInputGUI("Gender", 'flt_participant_gender');
        $genderArray=array("" => "-- Select --","f"=>"female","m"=>"male");
        $pgender->setOptions($genderArray);        
         /* Martins code gender filter ende */
        
		/* Configure participant group name filter (select) */
		$mapper = new ilOverviewMapper();
		$groups = $mapper->getGroupPairs($this->getParentObject()->object->getId());
		$groups = array("" => "-- Select --") + $groups;

		$gname = new ilSelectInputGUI($this->lng->txt("rep_robj_xtov_overview_flt_group_name"), 'flt_group_name');
		$gname->setOptions($groups);

		/* Configure filter form */
        $this->addFilterItem($pname);
        $this->addFilterItem($gname);        
        /*Martins code gender filter*/
        $this->addFilterItem($pgender);
        $pgender->readFromSession();
        /*Martins code gender filter ende*/        
        $pname->readFromSession();
        $gname->readFromSession();
        /*martis code gender filter */
        $this->filter['flt_participant_gender'] = $pgender->getValue();
        /* martins code ende*/
        $this->filter['flt_participant_name'] = $pname->getValue();
        $stringN=$pname->getValue();
        echo("<script>console.log('PHP: RANK NAME FILTER $stringN');</script>");
        $this->filter['flt_group_name']		  = $gname->getValue();
    }

	/**
	 *    Fill a table row.
	 *
	 *    This method is called internally by ilias to
	 *    fill a table row according to the row template.
	 *
	 * @param array $row
	 * @internal param \ilObjTestOverview $overview
	 */
    protected function fillRow($row)
    {
 		$overview = $this->getParentObject()->object;
                $dataArray=$this->getMapper()->getUniqueExerciseId($overview->getID());
		$results = array();
                $rowID=$row['member_id'];
                echo("<script>console.log('PHP: fill row member id $rowID ');</script>");
                for ($index = 0; $index < count($dataArray); $index++) {
                $obj_id=$dataArray[$index];

                    $this->getMapper()->getExerciseName($obj_id);
                    $DbObject = $this->getMapper()->getArrayofObjects($overview->getID());		
                    $mark = $this->getMapper()->getMark($row['member_id'], $obj_id, $DbObject);
		    $result= $mark;
                    $memID=$obj_id;
                    $results[] = $result;
		    $this->populateNoLinkCell($mark, "no-prem-result");
			
			
			$this->tpl->setCurrentBlock('cell');
			$this->tpl->parseCurrentBlock();
		}

		if (count($results))
		{
			$average = array_sum($results);
		}
		else
		{
			$average = "";
		}
		
		$this->tpl->setVariable( "AVERAGE_CLASS", "");
		$this->tpl->setVariable( "AVERAGE_VALUE", $average);
		$this->tpl->setVariable('TEST_PARTICIPANT', $row['member_fullname']);                        
    }
	
	private function populateLinkedCell($resultLink, $resultValue, $cssClass)
	{
		$this->tpl->setCurrentBlock('result');
		$this->tpl->setVariable('RESULT_LINK', $resultLink);
		$this->tpl->setVariable('RESULT_VALUE', $resultValue);				
		$this->tpl->setVariable('RESULT_CSSCLASS', $cssClass);
		$this->tpl->parseCurrentBlock();
	}
	
	private function populateNoLinkCell($resultValue, $cssClass)
	{
		$this->tpl->setCurrentBlock('result_nolink');
		$this->tpl->setVariable('RESULT_VALUE_NOLINK', $resultValue);
		$this->tpl->setVariable('RESULT_CSSCLASS_NOLINK', $cssClass);
		$this->tpl->parseCurrentBlock();
	}

	/**
	 *    Format the fetched data.
	 *
	 *    This method is used internally to retrieve ilObjUser
	 *    objects from participant group ids (ilObjCourse || ilObjGroup).
	 *
	 * @params    array    $data    array of IDs
	 *
	 * @param array $data
	 * @throws OutOfRangeException
	 * @return array
	 */
	protected function formatData ( array $data, $b=false )
	{
		/* For each group object we fetched, we need
		   to retrieve the members in order to have
		   a list of Participant. */
		$formatted = array(
			'items' => array(),
			'cnt'	=> 0);
		
		
		$index=0;
                echo("<script>console.log('PHP: format data  ');</script>");
		foreach ($data as $usrId)
		{	
                    echo("<script>console.log('PHP: format data  $usrId ');</script>");
		$formatted['items'][$usrId] = $usrId;              	
		$index++;
		}
                
		$formatted['items'] = $this->fetchUserInformation($formatted['items']);
                
                 if($b==false){   
                    return $this->sortByAveragePoints($formatted);
                 }else
                 {
                    return $this->sortByFullName($formatted); 
                 }
	}
	
	public function fetchUserInformation($usr_ids)
	{
		global $ilDB,$tpl;
		
		$usr_id__IN__usrIds = $ilDB->in('usr_id', $usr_ids, false, 'integer');
		
		$query = "
			SELECT usr_id, title, firstname, lastname, gender FROM usr_data WHERE $usr_id__IN__usrIds
		";
		
		$res = $ilDB->query($query);
		
		$users = array();
		
		while( $row = $ilDB->fetchAssoc($res) )
		{
			$user = new ilObjUser();
			$user->setId($row['usr_id']);
			$user->setUTitle($row['title']);
			$user->setFirstname($row['firstname']);
			$user->setLastname($row['lastname']);
			$user->setFullname();
                        $user->setGender($row['gender']);
                        
                        if (! empty($this->filter['flt_group_name']))
			{       
                                echo("<script>console.log('PHP: Gruppen Filter 1 ');</script>");
				$gname=$filter = $this->filter['flt_group_name']; 
                                $mapper = new ilOverviewMapper();
                                
                                $overviewGroups =$this->getParentObject()->object->getParticipantGroups(TRUE)  ;
                                foreach ($overviewGroups as $group) {
                                //$groupID= $group->getId();
                                //$object = ilObjectFactory::getInstanceByObjId($group);
                                if(false === strstr($group->getTitle(),$gname))
                                {
                                    if(false ===ilObjGroup::_isMember($user->getId(),$group)){
                                      /* User should be skipped. (Does not match filter) */
					continue;  
                                    }
                                }   
                                }
                                echo("<script>console.log('PHP: Gruppen Filter 2 ');</script>");
				
                                
			}
                        
                        
                        
			if (! empty($this->filter['flt_participant_name']))
			{
				$name   = strtolower($user->getFullName());
				$filter = strtolower($this->filter['flt_participant_name']);
                                  echo("<script>console.log('PHP: filter name $name ');</script>");
                                  echo("<script>console.log('PHP: filter namefilter $filter ');</script>");
				/* Simulate MySQL LIKE operator */
				if (false === strstr($name, $filter))
				{
                                    $ausgabe=strstr($name, $filter);
                                    echo("<script>console.log('PHP: user is skipped $ausgabe ');</script>");
					/* User should be skipped. (Does not match filter) */
					continue;
				}
			}
                        if (! empty($this->filter['flt_participant_gender']))
			{          
                                
				$gender   = $user->getGender();
				$filterGender = $this->filter['flt_participant_gender'];
                                
                                 
				/* Simulate MySQL LIKE operator */
				if (false === strstr($gender,$filterGender))
				{
                                    echo("<script>console.log('PHP: user is skipped gender ');</script>");
				
					/* User should be skipped. (Does not match filter) */
					continue;
				}
			}
			echo("<script>console.log('PHP: fetchUI $testString ');</script>");
			$users[ $row['usr_id'] ] = $user;
                        
		}
		$test=count($users);
                 echo("<script>console.log('PHP: fetchUI items count $test ');</script>");
		return $users;
	}

	/**
	 *    Get a CSS class name by the result
	 *
	 *    The getCSSByResult() method is used internally
	 *    to determine the CSS class to be set for a given
	 *    test result.
	 *
	 * @params    int    $progress    Learning progress (0|1|2|3)
	 * @see       ilLPStatus
	 *
	 * @param $progress
	 * @return string
	 */

	private function getCSSByProgress( $progress )
	{
		$map = $this->buildCssClassByProgressMap();

		$progress = (string)$progress;

		foreach($map as $lpNum => $cssClass)
		{
			if( $progress === (string)$lpNum ) // we need identical check !!
			{
				return $cssClass;
			}
		}

		return 'no-perm-result';
	}

	public function buildCssClassByProgressMap()
	{
		if( defined('ilLPStatus::LP_STATUS_NOT_ATTEMPTED_NUM') )
		{
			return array(
				ilLPStatus::LP_STATUS_NOT_ATTEMPTED_NUM => 'no-result',
				ilLPStatus::LP_STATUS_IN_PROGRESS_NUM => 'orange-result',
				ilLPStatus::LP_STATUS_COMPLETED_NUM => 'green-result',
				ilLPStatus::LP_STATUS_FAILED_NUM => 'red-result'
			);
		}

		return array(
			LP_STATUS_NOT_ATTEMPTED_NUM => 'no-result',
			LP_STATUS_IN_PROGRESS_NUM => 'orange-result',
			LP_STATUS_COMPLETED_NUM => 'green-result',
			LP_STATUS_FAILED_NUM => 'red-result'
		);
	}

	/**
	 *    Sort the array of users by their full name.
	 *
	 *    This method had to be implemented in order to sort
	 *    the listed users by their full name. The overview
	 *    settings allows selecting participant groups rather
	 *    than users. This means the data fetched according
	 *    to a test overview, is the participant group's data.
	 *
	 * @params    array    $data    Array with 'cnt' & 'items' indexes.
	 *
	 * @param array $data
	 * @return array
	 */
	protected function sortByFullName( array $data )
	{
		$azList = array();
		$sorted = array(
			'cnt'   => $data['cnt'],
			'items' => array());

		/* Initialize partition array. */
		for ($az = 'A'; $az <= 'Z'; $az++)
			$azList[$az] = array();

		/* Partition data. */
		foreach ($data['items'] as $userObj) {
			$name = $userObj->getFullName();
                        $name=strtoupper($name);
			$azList[$name{0}][] = $userObj;
		}

		/* Group all results. */
		foreach ($azList as $az => $userList) {
			if (! empty($userList))
				$sorted['items'] = array_merge($sorted['items'], $userList);
		}

		return $sorted;
	}
        
        /**
	 *    Sort the array of users by their average points.
	 *
	 *    This method had to be implemented in order to sort
	 *    the listed users by their average points. The overview
	 *    settings allows selecting participant groups rather
	 *    than users. This means the data fetched according
	 *    to a test overview, is the participant group's data.
	 *
	 * @params    array    $data    Array with 'cnt' & 'items' indexes.
	 *
	 * @param array $data
	 * @return array
	 */
        protected function sortByAveragePoints(array $data )
        {          
            global $ilDB, $tpl;
            $overviewMapper= $this->getMapper(); 
            // array which contains the user information
            $rankList = array();
            // array which contains the sum of points
            $sumList =array();
	    $sorted = array(
		'cnt'   => $data['cnt'],
		'items' => array());
                                     
		/* Initialize partition array. */
	    for ($rank = '1'; $rank <= count($data['items']); $rank++){
                $rankList[$rank] = array();
                $sumList[$rank] = array();
             }
             
                $studentIndex=1;
		/* Partition data. */
		foreach ($data['items'] as $userObj) {
                   
                  $stdID = $userObj->getId();                  
                  $overview= $this->getParentObject()->object;
                  $dataArray=$this->getMapper()->getUniqueExerciseId($overview->getID());
		  $results = array();
                
                for ($index = 0; $index < count($dataArray); $index++) {
                $obj_id=$dataArray[$index];
                $this->getMapper()->getExerciseName($obj_id);
                $DbObject = $this->getMapper()->getArrayofObjects($overview->getID());		
                $mark = $this->getMapper()->getMark($stdID, $obj_id, $DbObject);
                
                $result= $mark;
	        $results[] = $result;
                }
		$sum = array_sum($results) ;
                $rankList[$studentIndex ][] = $userObj;
                $sumList[$studentIndex ][] =$sum;
                $studentIndex++;
                }
                asort($sumList);
                $arraySorted=array_keys($sumList);
		/* Group all results. */
		for ($i = '1'; $i <= count($rankList); $i++) {
                    $position=array_pop($arraySorted);
                    $userList=$rankList[$position];
			if (! empty($userList)){
                         $sorted['items'] =  array_merge($sorted['items'], $userList);
                        }
		}
		return $sorted;
         }    
         /**
          * Function to rank all students and save the result in the database
          */
        public function getStudentsRanked()
        {     echo("<script>console.log('PHP: filter EO Set Date Ranking ');</script>");
            
            if( $this->getExternalSegmentation() && $this->getExternalSorting() )
		{
			$this->determineOffsetAndOrder();
		}
		elseif( !$this->getExternalSegmentation() && $this->getExternalSorting() )
		{
			$this->determineOffsetAndOrder(true);
		}
		else
		{
			throw new ilException('invalid table configuration: extSort=false / extSegm=true');
		}
		
		/* Configure query execution */
		$params = array();
		if( $this->getExternalSegmentation() )
		{
			$params['limit'] = $this->getLimit();
			$params['offset'] = $this->getOffset();
		}
		if( $this->getExternalSorting() )
		{
			$params['order_field'] = $this->getOrderField();
			$params['order_direction'] = $this->getOrderDirection();
		}

		$overview = $this->getParentObject()->object;
		$filters  = array("overview_id" => $overview->getId()) + $this->filter;

		/* Execute query. */
                $data = $this->getMapper()->getUniqueUserId($overview->getID());

                if( !count($data) && $this->getOffset() > 0) {
			/* Query again, offset was incorrect. */
                $this->resetOffset();
	        $data = $this->getMapper()->getUniqueUserId($overview->getID());
        }

		/* Post-query logic. Implement custom sorting or display
		   in formatData overload. */
		$data = $this->formatData($data,$sorting);
            
        
         $this->getMapper()->resetRanks($this->getParentObject()->object->getID());
         foreach ($data['items'] as $userObj) 
         {   
            $stdID = $userObj->getId();  
            $overview= $this->getParentObject()->object;
            $dataArray=$this->getMapper()->getUniqueExerciseId($overview->getID());
	    $results = array();
                
                for ($index = 0; $index < count($dataArray); $index++) {
                $obj_id=$dataArray[$index];
                $this->getMapper()->getExerciseName($obj_id);
                $DbObject = $this->getMapper()->getArrayofObjects($overview->getID());		
                $mark = $this->getMapper()->getMark($stdID, $obj_id, $DbObject);
                $result= $mark;
	        $results[] = $result;
                 }
		$average = array_sum($results) ;
		$ilMapper = $this->getMapper();
                $ilMapper->setData2Rank($average, $stdID,$this->getParentObject()->object->getId());    
                
            }
                                              
            $this->getMapper()->createDate($this->getParentObject()->object->getId());
        }
	/**
	 * @param string $a_text
	 * @param string $link
	 */
	public function addTestColumn($a_text, $link)
	{       
		$this->addColumn($a_text, '');
		$this->column[count($this->column) - 1]['link'] = $link;
	}

	/**
	 * 
	 */
	public function fillHeader()
	{
		global $lng;

		$allcolumnswithwidth = true;
		foreach ((array) $this->column as $idx => $column)
		{
			if (!strlen($column["width"]))
			{
				$allcolumnswithwidth = false;
			}
			else if($column["width"] == "1")
			{
				// IE does not like 1 but seems to work with 1%
				$this->column[$idx]["width"] = "1%";
			}
		}
		if ($allcolumnswithwidth)
		{
			foreach ((array) $this->column as $column)
			{
				$this->tpl->setCurrentBlock("tbl_colgroup_column");
				$this->tpl->setVariable("COLGROUP_COLUMN_WIDTH", $column["width"]);
				$this->tpl->parseCurrentBlock();
			}
		}
		$ccnt = 0;
		foreach ((array) $this->column as $column)
		{
			$ccnt++;

			//tooltip
			if ($column["tooltip"] != "")
			{
				include_once("./Services/UIComponent/Tooltip/classes/class.ilTooltipGUI.php");
				ilTooltipGUI::addTooltip("thc_".$this->getId()."_".$ccnt, $column["tooltip"]);
			}
			if ((!$this->enabled["sort"] || $column["sort_field"] == "" || $column["is_checkbox_action_column"]) && !$column['link'])
			{
				$this->tpl->setCurrentBlock("tbl_header_no_link");
				if ($column["width"] != "")
				{
					$this->tpl->setVariable("TBL_COLUMN_WIDTH_NO_LINK"," width=\"".$column["width"]."\"");
				}
				if (!$column["is_checkbox_action_column"])
				{
					$this->tpl->setVariable("TBL_HEADER_CELL_NO_LINK",
						$column["text"]);
				}
				else
				{
					$this->tpl->setVariable("TBL_HEADER_CELL_NO_LINK",
						ilUtil::img(ilUtil::getImagePath("spacer.png"), $lng->txt("action")));
				}
				$this->tpl->setVariable("HEAD_CELL_NL_ID", "thc_".$this->getId()."_".$ccnt);

				if ($column["class"] != "")
				{
					$this->tpl->setVariable("TBL_HEADER_CLASS"," " . $column["class"]);
				}
				$this->tpl->parseCurrentBlock();
				$this->tpl->touchBlock("tbl_header_th");
				continue;
			}
			if (($column["sort_field"] == $this->order_field) && ($this->order_direction != ""))
			{
				$this->tpl->setCurrentBlock("tbl_order_image");
				$this->tpl->setVariable("IMG_ORDER_DIR",ilUtil::getImagePath($this->order_direction."_order.png"));
				$this->tpl->setVariable("IMG_ORDER_ALT", $this->lng->txt("change_sort_direction"));
				$this->tpl->parseCurrentBlock();
			}

			$this->tpl->setCurrentBlock("tbl_header_cell");
			$this->tpl->setVariable("TBL_HEADER_CELL", $column["text"]);
			$this->tpl->setVariable("HEAD_CELL_ID", "thc_".$this->getId()."_".$ccnt);

			// only set width if a value is given for that column
			if ($column["width"] != "")
			{
				$this->tpl->setVariable("TBL_COLUMN_WIDTH"," width=\"".$column["width"]."\"");
			}

			$lng_sort_column = $this->lng->txt("sort_by_this_column");
			$this->tpl->setVariable("TBL_ORDER_ALT",$lng_sort_column);

			$order_dir = "asc";

			if ($column["sort_field"] == $this->order_field)
			{
				$order_dir = $this->sort_order;

				$lng_change_sort = $this->lng->txt("change_sort_direction");
				$this->tpl->setVariable("TBL_ORDER_ALT",$lng_change_sort);
			}

			if ($column["class"] != "")
			{
				$this->tpl->setVariable("TBL_HEADER_CLASS"," " . $column["class"]);
			}
			if($column['link'])
			{
				$this->setExternalLink($column['link']);
			}
			else
			{
				$this->setOrderLink($column["sort_field"], $order_dir);
			}
			$this->tpl->parseCurrentBlock();
			$this->tpl->touchBlock("tbl_header_th");
		}

		$this->tpl->setCurrentBlock("tbl_header");
		$this->tpl->parseCurrentBlock();
	}

	/**
	 * @param string $link
	 */
	public function setExternalLink($link)
	{
		$this->tpl->setVariable('TBL_ORDER_LINK', $link);
	}
	
	/**
	 * overwrite this method for ungregging the object data structures
	 * since ilias tables support arrays only
	 * 
	 * @param mixed $data
	 * @return array
	 */
	protected function buildTableRowsArray($data)
	{
		$rows = array();
		
		foreach($data as $member)
		{
                    
                    if(!($member->getId()==null))
                        { 
			$rows[] = array(
                                      
				'member_id' => $member->getId(),
				'member_fullname' => $member->getFullName()
			);
                          }else{
                            $rows[] = array(
                                      
				'member_id' => '0',
				'member_fullname' => $member->getFullName()
			);         
                             }                      
        
                }
		
		return $rows;
	}
	
	protected function buildMemberResultLinkTarget($refId, $activeId)
	{
		global $ilCtrl;

		$link = $ilCtrl->getLinkTargetByClass(
			array('ilObjTestOverviewGUI', 'ilobjtestgui', 'iltestevaluationgui'), 'outParticipantsPassDetails'
		);
		
		$link = ilUtil::appendUrlParameterString($link, "ref_id=$refId");
		$link = ilUtil::appendUrlParameterString($link, "active_id=$activeId");
		
		return $link;
	}
       
}

