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
		$canadmin = $mod->_CheckAccess('admin');
		$owned = $mod->GetPreference('owned_categories', false);
		$funcs = new MBVFshared();
		// get a simple list of available categories, ordered by fields category and vieworder
		$categories = $funcs->GetCategories($mod,0,0,false,($canadmin || !$owned));
		$wanted = implode (",", array_keys($categories));//no injection risk from categories keys array
		$sql = "SELECT I.*, U.first_name, U.last_name FROM $mod->ItemTable I
LEFT JOIN $mod->UserTable U ON I.owner = U.user_id
LEFT JOIN $mod->CatTable C ON I.category_id = C.category_id
WHERE I.category_id IN ($wanted) ORDER BY C.vieworder, I.vieworder ASC";
		$rs = $mod->dbHandle->Execute($sql);
		if ($rs)
		{
			$canmod = $canadmin;
			if ($canmod)
				$candel = true;
			else
			{
				$candel = $mod->_CheckAccess('delete');
				$canmod = $mod->_CheckAccess('modify');
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
			if ($candel)
				$icondel = $theme->DisplayImage('icons/system/delete.gif', $mod->Lang('deleteitem'),'','','systemicon');

			if ($mod->before111)
				unset ($theme);

			$items = array();

			while ($row = $rs->FetchRow())
			{
				$one = new stdClass();
				$one->item_id		= $row['item_id'];
				$neat = $mod->ellipsize(strip_tags($row['short_question']), 40, 0.5);
				if ($canmod)
					$one->question	= $mod->CreateLink($id,'openitem',
						$returnid, $neat, array('item_id'=>$row['item_id']));
				else
					$one->question	= $neat;
				$one->category		= $categories[$row['category_id']]->name;
				$one->category_id	= $row['category_id'];
				$one->create_date	= $row['create_date'];
				$one->modified_date	= $row['last_modified_date'];
				$name = trim($row['first_name'].' '.$row['last_name']);
				if ($name == '') $name = '<'.$mod->Lang('noowner').'>';
				$one->ownername		= $name;
				if ($canmod)
				{
					if ($row['active']) // it's active so create a deactivate-link
						$one->active = $mod->CreateLink($id,'toggleitem',$returnid, $iconyes,
							array('item_id'=>$one->item_id,'active'=>true));
					else // it's inactive so create an activate-link
						$one->active = $mod->CreateLink($id,'toggleitem',$returnid, $iconno,
							array('item_id'=>$one->item_id,'active'=>false));
				}
				else
					$one->active = ($row['active']) ? $iconyes : $iconno;

				//edit or view
				$one->editlink = $mod->CreateLink($id,'openitem',$returnid, $iconopen,
					array('item_id'=>$one->item_id));

				if ($candel)
					$one->deletelink = $mod->CreateLink($id,'deleteitem',$returnid, $icondel,
						array('item_id'=>$one->item_id),
						$mod->Lang('item_confirm',$row['short_question']));
				else
					$one->deletelink = '';

				$one->selected = $mod->CreateInputCheckbox($id,'selitems[]',$one->item_id,-1,'class="pagecheckbox"');

				$items[] = $one;
			}
			$rs->Close();

			if (count($items) > 0)
			{
				$smarty->assign('items',$items);
//UNUSED in tempate	$smarty->assign('mod',$canmod);
				$smarty->assign('del',$candel);
				echo $mod->ProcessTemplate('questions.tpl');
				return;
			}
		}
		echo '';
	}

}
