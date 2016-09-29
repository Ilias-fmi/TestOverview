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

		if($this->getExportMode())
		{
			$this->exportData($this->getExportMode(), true);
		}
		
		$this->prepareOutput();
		
		if (is_object($ilCtrl) && $this->getId() == "")
		{
			$ilCtrl->saveParameter($this->getParentObject(), $this->getNavParameter());
		}
				
		if(!$this->getPrintMode())
		{
			// set form action
			if ($this->form_action != "" && $this->getOpenFormTag())
			{
				$hash = "";
				if (is_object($ilUser) && $ilUser->getPref("screen_reader_optimization"))
				{
					$hash = "#".$this->getTopAnchor();
				}

				if((bool)$this->form_multipart)
				{
					$this->tpl->touchBlock("form_multipart_bl");
				}

				if($this->getPreventDoubleSubmission())
				{
					$this->tpl->touchBlock("pdfs");
				}

				$this->tpl->setCurrentBlock("tbl_form_header");
				$this->tpl->setVariable("FORMACTION", $this->getFormAction().$hash);
				$this->tpl->setVariable("FORMNAME", $this->getFormName());				
				$this->tpl->parseCurrentBlock();
			}

			if ($this->form_action != "" && $this->getCloseFormTag())
			{
				$this->tpl->touchBlock("tbl_form_footer");
			}
		}
		
		if(!$this->enabled['content'])
		{
			return $this->render();
		}

		if (!$this->getExternalSegmentation())
		{
			$this->setMaxCount(count($this->row_data));
		}

		$this->determineOffsetAndOrder();
		
		$this->setFooter("tblfooter",$this->lng->txt("previous"),$this->lng->txt("next"));
		
		$data = $this->getData();
		if($this->dataExists())
		{
			// sort
			if (!$this->getExternalSorting() && $this->enabled["sort"])
			{
				$data = ilUtil::sortArray($data, $this->getOrderField(),
					$this->getOrderDirection(), $this->numericOrdering($this->getOrderField()));
			}

			// slice
			if (!$this->getExternalSegmentation())
			{
				$data = array_slice($data, $this->getOffset(), $this->getLimit());
			}
		}
		
		// fill rows
		if($this->dataExists())
		{
			if($this->getPrintMode())
			{
				ilDatePresentation::setUseRelativeDates(false);
			}

			$this->tpl->addBlockFile("TBL_CONTENT", "tbl_content", $this->row_template,
				$this->row_template_dir);
	
			foreach($data as $set)
			{
				$this->tpl->setCurrentBlock("tbl_content");
				$this->css_row = ($this->css_row != "tblrow1")
					? "tblrow1"
					: "tblrow2";
				$this->tpl->setVariable("CSS_ROW", $this->css_row);

				$this->fillRow($set);
				$this->tpl->setCurrentBlock("tbl_content");
				$this->tpl->parseCurrentBlock();
			}
		}
		else
		{
			// add standard no items text (please tell me, if it messes something up, alex, 29.8.2008)
			$no_items_text = (trim($this->getNoEntriesText()) != '')
				? $this->getNoEntriesText()
				: $lng->txt("no_items");

			$this->css_row = ($this->css_row != "tblrow1")
					? "tblrow1"
					: "tblrow2";				
			
			$this->tpl->setCurrentBlock("tbl_no_entries");
			$this->tpl->setVariable('TBL_NO_ENTRY_CSS_ROW', $this->css_row);
			$this->tpl->setVariable('TBL_NO_ENTRY_COLUMN_COUNT', $this->column_count);
			$this->tpl->setVariable('TBL_NO_ENTRY_TEXT', trim($no_items_text));
			$this->tpl->parseCurrentBlock();			
		}


		if(!$this->getPrintMode())
		{
			$this->fillFooter();

			$this->fillHiddenRow();

			$this->fillActionRow();

			$this->storeNavParameter();
		}
               
		return $this->render();
    }

   

}

?>
