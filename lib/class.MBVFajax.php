<?php
#----------------------------------------------------------------------
# Module: MBVFaq - a simple FAQ module
# Library file: ajax
#----------------------------------------------------------------------
# See file MBVFaq.module.php for full details of copyright, licence, etc.
#----------------------------------------------------------------------

class MBVFajax
{
 /* Generates html for the tbody of the questions table on tab on module's admin page.
	It replicates some of action.defaultadmin.php, except for aspects managed by
	javascript e.g. hover-classing, up-down links, and assuming that the table
	content is editable */
	function CreateQuestionsBody ($id,$returnid,&$mod,&$smarty)
	{
		$padm = $mod->_CheckAccess('admin');
		$owned = $mod->GetPreference('owned_categories', false);
		$funcs = new MBVFshared();
		// get a simple list of available categories, ordered by fields category and vieworder
		$categories = $funcs->GetCategories($mod,0,0,false,($padm || !$owned));
		$wanted = implode (",", array_keys($categories));//no injection risk from categories keys array
		$sql = "SELECT I.*, U.first_name, U.last_name FROM $mod->ItemTable I
LEFT JOIN $mod->UserTable U ON I.owner = U.user_id
LEFT JOIN $mod->CatTable C ON I.category_id = C.category_id
WHERE I.category_id IN ($wanted) ORDER BY C.vieworder, I.vieworder ASC";
		$rs = $mod->dbHandle->Execute($sql);
		if ($rs)
		{
			$pdev = $mod->CheckPermission('Modify Any Page');
			if ($padm)
			{
				$pdel = true;
				$pmod = true;
			}
			else
			{
				$pdel = $mod->_CheckAccess('delete');
				$pmod = $mod->_CheckAccess('modify');
			}

			//theme-object not accessible in this context, so create one
			if ($mod->before111)
			{
				$config = cmsms()->GetConfig();
				include (cms_join_path($config['root_path'],'lib','classes','class.admintheme.inc.php'));
				//GetThemeObject() inits all relevant parameters, for CMSMS 1,6, 1.9, 1.10 at least
				$themeOb = new AdminTheme(null,0,''); //don't bother setting ($gCms,$userid,$themeName)
				if (isset($themeOb))
				{
					$theme = $themeOb->GetThemeObject();
					unset($themeOb);
				}
				else
				{
					echo '';
					return;
				}
			}
			else
				$theme = cms_utils::get_theme_object();

			$iconyes = $theme->DisplayImage('icons/system/true.gif', $mod->Lang('true'),'','','systemicon');
			$iconno = $theme->DisplayImage('icons/system/false.gif', $mod->Lang('false'),'','','systemicon');
			if ($mod)
				$iconopen = $theme->DisplayImage('icons/system/edit.gif', $mod->Lang('edititem'),'','','systemicon');
			else
				$iconopen = $theme->DisplayImage('icons/system/view.gif', $mod->Lang('viewitem'),'','','systemicon');
			if ($pdel)
				$icondel = $theme->DisplayImage('icons/system/delete.gif', $mod->Lang('deleteitem'),'','','systemicon');

			if ($mod->before111)
				unset ($theme);

			$items = array();

			while ($row = $rs->FetchRow())
			{
				$thisid 			= (int)$row['item_id'];
				$one = new stdClass();
				$one->item_id	= $thisid;
				$neat = $mod->ellipsize(strip_tags($row['short_question']), 40, 0.5);
				if ($pmod)
					$one->item		= $mod->CreateLink($id,'openitem',
						$returnid,$neat,array('item_id'=>$thisid));
				else
					$one->item		= $neat;
				$catid = (int)$row['category_id'];
				$one->group			= $categories[$catid]->name;
				$one->create_date	= $row['create_date'];
				$one->modify_date	= $row['last_modified_date'];
				if($padm || $owned)
				{
					$name = trim($row['first_name'].' '.$row['last_name']);
					if ($name == '') $name = '<'.$mod->Lang('noowner').'>';
					$one->ownername	= $name;
				}
				if ($pmod)
				{
					//$one->downlink = ; $one->uplink = ; NO NEED IF DND IS WORKING
					if ($row['active']) // it's active so create a deactivate-link
						$one->active = $mod->CreateLink($id,'toggleitem',$returnid,$iconyes,
							array('item_id'=>$thisid,'active'=>true));
					else // it's inactive so create an activate-link
						$one->active = $mod->CreateLink($id,'toggleitem',$returnid,$iconno,
							array('item_id'=>$thisid,'active'=>false));
				}
				else
					$one->active = ($row['active']) ? $iconyes : $iconno;

				//edit or view
				$one->editlink = $mod->CreateLink($id,'openitem',$returnid, $iconopen,
					array('item_id'=>$thisid));

				if ($pdel)
					$one->deletelink = $mod->CreateLink($id,'deleteitem',$returnid,$icondel,
						array('item_id'=>$thisid),
						$mod->Lang('delitm_confirm',$row['short_question']));
				else
					$one->deletelink = '';

				$one->selected = $mod->CreateInputCheckbox($id,'selitems[]',$thisid,-1);

				$items[] = $one;
			}
			$rs->Close();

			if (count($items) > 0)
			{
				$smarty->assign('items',$items);
				$smarty->assign('dev',$pdev);
				$smarty->assign('del',$pdel);
				$smarty->assign('own',$padm || $owned);
				echo $mod->ProcessTemplate('questions.tpl');
				return;
			}
		}
		echo '';
	}

}
