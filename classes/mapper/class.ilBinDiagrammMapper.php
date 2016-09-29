<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */


/*
 *
 *
 */
require_once 'Customizing/global/plugins/Services/Repository/RepositoryObject/TestOverview/classes/GUI/class.ilTestOverviewTableGUI.php';
require_once 'Customizing/global/plugins/Services/Repository/RepositoryObject/TestOverview/classes/mapper/class.ilOverviewMapper.php';
class BinDiagrammMapper 
    extends ilTestOverviewTableGUI{
    
    public function data(){
        $this-> setMapper(new ilOverviewMapper)
			  ->populate();
        global $lng, $ilCtrl, $ilUser;
					
		$data = $this->getData();
		
		// fill rows
		

			$this->tpl->addBlockFile("TBL_CONTENT", "tbl_content", 'tpl.test_overview_rows.html',
				$this->row_template_dir);
	
             
			foreach($data as $set)
			{
				$this->fillRow($set);
				$this->tpl->setCurrentBlock("tbl_content");
				$this->tpl->parseCurrentBlock();
			}
               
		//return $this->render();
                        return $this->render();
    }

    
	function render()
	{
		global $lng, $ilCtrl;

		return $this->tpl->get();
	}
        
        function getRow(){
            return $this->row_template;
        }
        function getService(){
            return $this->row_template_dir;
        }


        private function renderFilter()
	{
		global $lng, $tpl;
		
		$filter = $this->getFilterItems();
		$opt_filter = $this->getFilterItems(true);

		$tpl->addJavascript("./Services/Table/js/ServiceTable.js");
		
		if (count($filter) == 0 && count($opt_filter) == 0)
		{
			return;
		}

		include_once("./Services/YUI/classes/class.ilYuiUtil.php");
		ilYuiUtil::initConnection();

		$ccnt = 0;

		// render standard filter
		if (count($filter) > 0)
		{
			foreach ($filter as $item)
			{
				if ($ccnt >= $this->getFilterCols())
				{
					$this->tpl->setCurrentBlock("filter_row");
					$this->tpl->parseCurrentBlock();
					$ccnt = 0;
				}
				$this->tpl->setCurrentBlock("filter_item");
				$this->tpl->setVariable("OPTION_NAME",
					$item->getTitle());
				$this->tpl->setVariable("F_INPUT_ID",
					$item->getFieldId());
				$this->tpl->setVariable("INPUT_HTML",
					$item->getTableFilterHTML());
				$this->tpl->parseCurrentBlock();
				$ccnt++;
			}
		}
		
		// render optional filter
		if (count($opt_filter) > 0)
		{
			$this->determineSelectedFilters();

			foreach ($opt_filter as $item)
			{
				if($this->isFilterSelected($item->getPostVar()))
				{
					if ($ccnt >= $this->getFilterCols())
					{
						$this->tpl->setCurrentBlock("filter_row");
						$this->tpl->parseCurrentBlock();
						$ccnt = 0;
					}
					$this->tpl->setCurrentBlock("filter_item");
					$this->tpl->setVariable("OPTION_NAME",
						$item->getTitle());
					$this->tpl->setVariable("F_INPUT_ID",
						$item->getFieldId());
					$this->tpl->setVariable("INPUT_HTML",
						$item->getTableFilterHTML());
					$this->tpl->parseCurrentBlock();
					$ccnt++;
				}
			}
		
			// filter selection
			$items = array();
			foreach ($opt_filter as $item)
			{
				$k = $item->getPostVar();
				$items[$k] = array("txt" => $item->getTitle(),
					"selected" => $this->isFilterSelected($k));
			}

			include_once("./Services/UIComponent/CheckboxListOverlay/classes/class.ilCheckboxListOverlayGUI.php");
			$cb_over = new ilCheckboxListOverlayGUI("tbl_filters_".$this->getId());
			$cb_over->setLinkTitle($lng->txt("optional_filters"));
			$cb_over->setItems($items);

			$cb_over->setFormCmd($this->getParentCmd());
			$cb_over->setFieldVar("tblff".$this->getId());
			$cb_over->setHiddenVar("tblfsf".$this->getId());

			$cb_over->setSelectionHeaderClass("ilTableMenuItem");
			$this->tpl->setCurrentBlock("filter_select");

			// apply should be the first submit because of enter/return, inserting hidden submit
			$this->tpl->setVariable("HIDDEN_CMD_APPLY", $this->filter_cmd);

			$this->tpl->setVariable("FILTER_SELECTOR", $cb_over->getHTML());
		    $this->tpl->parseCurrentBlock();
		}

		// if any filter
		if($ccnt > 0 || count($opt_filter) > 0)
		{
			$this->tpl->setVariable("TXT_FILTER", $lng->txt("filter"));
			
			if($ccnt > 0)
			{
				if ($ccnt < $this->getFilterCols())
				{
					for($i = $ccnt; $i<=$this->getFilterCols(); $i++)
					{
						$this->tpl->touchBlock("filter_empty_cell");
					}
				}
				$this->tpl->setCurrentBlock("filter_row");
				$this->tpl->parseCurrentBlock();

				$this->tpl->setCurrentBlock("filter_buttons");				
				$this->tpl->setVariable("CMD_APPLY", $this->filter_cmd);
				$this->tpl->setVariable("TXT_APPLY", $this->filter_cmd_txt 
					? $this->filter_cmd_txt
					: $lng->txt("apply_filter"));
				$this->tpl->setVariable("CMD_RESET", $this->reset_cmd);
				$this->tpl->setVariable("TXT_RESET", $this->reset_cmd_txt 
					? $this->reset_cmd_txt 
					: $lng->txt("reset_filter"));
			}
			else if(count($opt_filter) > 0)
			{
				$this->tpl->setCurrentBlock("optional_filter_hint");
				$this->tpl->setVariable('TXT_OPT_HINT', $lng->txt('optional_filter_hint'));
				$this->tpl->parseCurrentBlock();
			}
			
			$this->tpl->setCurrentBlock("filter_section");
			$this->tpl->setVariable("FIL_ID", $this->getId());
			$this->tpl->parseCurrentBlock();
			
			// (keep) filter hidden?
			if ($this->loadProperty("filter") != 1)
			{
				if (!$this->getDisableFilterHiding())
				{
					$this->tpl->setCurrentBlock("filter_hidden");
					$this->tpl->setVariable("FI_ID", $this->getId());
					$this->tpl->parseCurrentBlock();
				}
			}
		}
	}

}

?>
