<?php
#----------------------------------------------------------------------
# Module: MBVFaq - a simple FAQ module
# Library file: shared
#----------------------------------------------------------------------
# See file MBVFaq.module.php for full details of copyright, licence, etc.
#----------------------------------------------------------------------

class MBVFshared
{
	/**
	GetItem(&$mod,$item_id,$frontend = TRUE)
	Returns an object containing data for the requested faq, or	containing
	blank data if requested $item_id is not found (incl. '-1').
	*/
	public function GetItem(&$mod, $item_id, $frontend = TRUE)
	{
		$item = new stdClass();

		$db = $mod->dbHandle;
		$sql = "SELECT I.*, U.first_name, U.last_name FROM $mod->ItemTable I
LEFT JOIN $mod->UserTable U ON I.owner = U.user_id WHERE item_id=?";
		$row = $db->GetRow($sql, array($item_id));
		if ($row) {
			if ($frontend) {
				if ($mod->before20) {
					global $smarty;
				} else {
					$smarty = $mod->GetActionTemplateObject();
				}
			}
			$item->item_id = $row['item_id'];
			$item->category_id = $row['category_id'];
			//get the last-found (should only be 1) category name with the appropriate id
			$sql = "SELECT name FROM $mod->CatTable WHERE category_id=?";
			$names = $db->GetCol($sql, array($item->category_id));
			$item->category = ($names) ? array_pop($names) : '';
			$item->question = $row['short_question'];
			$item->long_question = $row['long_question'];
			if ($row['short_answer'] != '') {
				$item->short_answer = ($frontend) ?
					$smarty->fetch('string:'.$row['short_answer']):
					$row['short_answer'];
			} else {
				$item->short_answer = '';
			}
			if ($row['long_answer'] != '') {
				$item->long_answer = ($frontend) ?
					$smarty->fetch('string:'.$row['long_answer']):
					$row['long_answer'];
			} else {
				$item->long_answer = '';
			}
			$item->owner = $row['owner'];
			$name = trim($row['first_name'].' '.$row['last_name']);
			if ($name == '') {
				$name = '<'.$mod->Lang('noowner').'>';
			}
			$item->ownername = $name;
			$item->create_date = $row['create_date'];
			$item->modified_date = $row['last_modified_date'];
			$item->active = $row['active'];
			$item->order= $row['vieworder'];
//TODO template could have e.g. {$item->prevquestion} and {$item->nextquestion}
//add some code to get the id for the next and prev questions
//			$item->prevquestion = '';
//			$item->nextquestion = '';
		} else {
			$item->item_id		= -1;
			$item->category_id	= 0; //default category
			$item->category		= '';
			$item->question		= '';
			$item->long_question = '';
			$item->short_answer	= '';
			$item->long_answer	= '';
			$item->owner		= '';
			$item->ownername	= '';
			$item->create_date	= '';
			$item->modified_date = '';
			$item->active		= 0;
			$item->order		= ''; //sort at end of category
//see above
//			$item->prevquestion	= '';
//			$item->nextquestion	= '';
		}
		return $item;
	}

	/**
	GetLink(&$mod,$id,$returnid,$text='',$category='',$item_id='')
	creates a string representing a link to a faq page, specified by
	$category and/or $item_id
	$text is the text to display in the link, or if blank, only the url will be returned

	If $category is not blank the link will point to a page containing all
	questions and answers for that category.

	If $item_id is not blank the link will point to a page showing all the data
	for the relevant question.

	If both $category and $item_id are blank, the link will go to the default
	handler function with just a $returnid
	*/
	public function GetLink(&$mod, $id, $returnid, $text='', $category='', $item_id='')
	{
		$ignore = $mod->GetPreference('ignore_click', TRUE);
		$oparams = array();
		if (!$ignore) {
			if ($category!='') {
				$oparams['category'] = $category;
			}
			if ($item_id!='') {
				$oparams['item_id'] = $item_id;
			}
		}
		$brief = ($text=='');
		$config = cmsms()->GetConfig();
		if ($config['url_rewriting'] != 'none') {
			// handle canonical URL (must conform to RegisterRoute() calls elsewhere)
			$theurl = 'mbvfaq/';
			if (!$ignore) {
				if ($category!='') {
					$theurl .= 'cat'.$category.'/';
				}
				if ($item_id!='') {
					$theurl .= 'faq'.$item_id.'/';
				}
			}
			$theurl .= $returnid.'/';
			$outstring = $mod->CreateLink($id, 'default', $returnid,
				$text, $oparams, '', $brief, FALSE, '', FALSE, $theurl);
		} else {
			$outstring = $mod->CreateLink($id, 'default', $returnid,
				$text, $oparams, '', $brief, FALSE);
		}
		return $outstring;
	}

	/**
	GetCategories(&$mod, $id=0, $returnid=0, $full=FALSE, $anyowner=TRUE)

	Create associative array of category-data, sorted by field 'vieworder',
	each array member's key is the category id, value is an object
	$id used in link, when $full is TRUE
	$returnid ditto
	$full FALSE	return category_id and name only
	$full TRUE return all table 'raw' data for the category, plus a link TODO describe
	$anyowner TRUE return all categories
	$anyowner FALSE return categories whose owner is 0 or matches the current user
	*/
	public function GetCategories(&$mod, $id=0, $returnid=0, $full=FALSE, $anyowner=TRUE)
	{
		$catarray = array();

		$db = $mod->dbHandle;
		if ($anyowner) {
			$sql = "SELECT category_id,name,vieworder FROM $mod->CatTable ORDER BY vieworder ASC";
			$rows = $db->GetAssoc($sql);
		} else {
			$sql = "SELECT category_id,name,vieworder FROM $mod->CatTable WHERE owner IN (0,?) ORDER BY vieworder ASC";
			$uid = get_userid(FALSE);
			$rows = $db->GetAssoc($sql, array($uid));
		}

		if ($rows) {
			foreach ($rows as $cid=>$row) {
				$one = new stdClass();
				$one->category_id = $cid;
				$one->name = $row['name'];
				if ($full) {
					$one->order= $row['vieworder'];
					$one->category_link = self::GetLink($mod, $id, $returnid, $one->name, $one->name);
				}
				$catarray[$cid] = $one;
			}
		}
		return $catarray;
	}

	public function StripTags($str, &$tags)
	{
		foreach ($tags as $tag) {
			$str = preg_replace('#<'.$tag.'(>|\s[^>]*>)#is', '', $str);
			$str = preg_replace('#</'.$tag.'(>|\s[^>]*>)#is', '<br />', $str);
		}
		return $str;
	}

	/**
	ProcessTemplate:
	@mod: reference to current MBVFaq module object
	@tplname: template identifier
	@tplvars: associative array of template variables
	@cache: optional boolean, default TRUE
	Returns: string, processed template
	*/
	public function ProcessTemplate(&$mod, $tplname, $tplvars, $cache=TRUE)
	{
		if ($mod->before20) {
			global $smarty;
		} else {
			$smarty = $mod->GetActionTemplateObject();
			if (!$smarty) {
				global $smarty;
			}
		}
		$smarty->assign($tplvars);
		if ($mod->oldtemplates) {
			echo $mod->ProcessTemplate($tplname);
		} else {
			if ($cache) {
				$cache_id = md5('mbvf'.$tplname.serialize(array_keys($tplvars)));
				$lang = CmsNlsOperations::get_current_language();
				$compile_id = md5('mbvf'.$tplname.$lang);
				$tpl = $smarty->CreateTemplate($mod->GetFileResource($tplname), $cache_id, $compile_id, $smarty);
				if (!$tpl->isCached()) {
					$tpl->assign($tplvars);
				}
			} else {
				$tpl = $smarty->CreateTemplate($mod->GetFileResource($tplname), NULL, NULL, $smarty, $tplvars);
			}
			$tpl->display();
		}
	}

	/**
	ProcessTemplateFromData:
	@mod: reference to current MBVFaq module object
	@data: string
	@tplvars: associative array of template variables
	No cacheing.
	Returns: string, processed template
	*/
	public function ProcessTemplateFromData(&$mod, $data, $tplvars)
	{
		if ($mod->before20) {
			global $smarty;
		} else {
			$smarty = $mod->GetActionTemplateObject();
			if (!$smarty) {
				global $smarty;
			}
		}
		$smarty->assign($tplvars);
		if ($mod->oldtemplates) {
			return $mod->ProcessTemplateFromData($data);
		} else {
			$tpl = $smarty->CreateTemplate('eval:'.$data, NULL, NULL, $smarty, $tplvars);
			return $tpl->fetch();
		}
	}
}
