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
	GetItem(&$mod,$item_id,$frontend = true)
	Returns an object containing data for the requested faq, or	containing
	blank data if requested $item_id is not found (incl. '-1').
	*/
	function GetItem(&$mod,$item_id,$frontend = true)
	{
		$item = new stdClass();

		$db = $mod->dbHandle;
		$sql = "SELECT I.*, U.first_name, U.last_name FROM $mod->ItemTable I
LEFT JOIN $mod->UserTable U ON I.owner = U.user_id WHERE item_id=?";
		$row = $db->GetRow($sql,array($item_id));
		if ($row)
		{
			if ($frontend)
				$smarty = cmsms()->GetSmarty();
			$item->item_id = $row['item_id'];
			$item->category_id = $row['category_id'];
			//get the last-found (should only be 1) category name with the appropriate id
			$sql = "SELECT name FROM $mod->CatTable WHERE category_id=?";
			$names = $db->GetCol($sql,array($item->category_id));
			$item->category = ($names) ? array_pop($names) : '';
			$item->question = $row['short_question'];
			$item->long_question = $row['long_question'];
			if ($row['short_answer'] != '')
			{
				$item->short_answer = ($frontend) ?
					$smarty->fetch('string:'.$row['short_answer']):
					$row['short_answer'];
			}
			else
				$item->short_answer = '';
			if ($row['long_answer'] != '')
			{
				$item->long_answer = ($frontend) ?
					$smarty->fetch('string:'.$row['long_answer']):
					$row['long_answer'];
			}
			else
				$item->long_answer = '';
			$item->owner = $row['owner'];
			$name = trim($row['first_name'].' '.$row['last_name']);
			if ($name == '') $name = '<'.$mod->Lang('noowner').'>';
			$item->ownername = $name;
			$item->create_date = $row['create_date'];
			$item->modified_date = $row['last_modified_date'];
			$item->active = $row['active'];
			$item->order= $row['vieworder'];
//TODO template could have e.g. {$item->prevquestion} and {$item->nextquestion}
//add some code to get the id for the next and prev questions
//			$item->prevquestion = '';
//			$item->nextquestion = '';
		}
		else
		{
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
	function GetLink(&$mod,$id,$returnid,$text='',$category='',$item_id='')
	{
		$ignore = $mod->GetPreference('ignore_click',true);
		$oparams = array();
		if (!$ignore)
		{
			if ($category!='')
				$oparams['category'] = $category;
			if ($item_id!='')
				$oparams['item_id'] = $item_id;
		}
		$brief = ($text=='');
		$config = cmsms()->GetConfig();
		if ($config['url_rewriting'] != 'none')
		{
			// handle canonical URL (must conform to RegisterRoute() calls elsewhere)
			$theurl = 'mbvfaq/';
			if (!$ignore)
			{
				if ($category!='')
					$theurl .= 'cat'.$category.'/';
				if ($item_id!='')
					$theurl .= 'faq'.$item_id.'/';
			}
			$theurl .= $returnid.'/';
			$outstring = $mod->CreateLink($id,'default',$returnid,
				$text,$oparams,'',$brief,false,'',false,$theurl);
		}
		else
		{
			$outstring = $mod->CreateLink($id,'default',$returnid,
				$text,$oparams,'',$brief,false);
		}
		return $outstring;
	}

	/**
	GetCategories(&$mod, $id=0, $returnid=0, $full=false, $anyowner=true)

	Create associative array of category-data, sorted by field 'vieworder',
	each array member's key is the category id, value is an object
	$id used in link, when $full is true
	$returnid ditto
	$full false	return category_id and name only
	$full true return all table 'raw' data for the category, plus a link TODO describe
	$anyowner true return all categories
	$anyowner false return categories whose owner is 0 or matches the current user
	*/
	function GetCategories(&$mod,$id=0,$returnid=0,$full=false,$anyowner=true)
	{
		$catarray = array();

		$db = $mod->dbHandle;
		if ($anyowner)
		{
			$sql = "SELECT category_id,name,vieworder FROM $mod->CatTable ORDER BY vieworder ASC";
			$rows = $db->GetAssoc($sql);
		}
		else
		{
			$sql = "SELECT category_id,name,vieworder FROM $mod->CatTable WHERE owner IN (0,?) ORDER BY vieworder ASC";
			$uid = get_userid(false);
			$rows = $db->GetAssoc($sql,array($uid));
		}

		if ($rows)
		{
			foreach ($rows as $cid=>$row)
			{
				$one = new stdClass();
				$one->category_id = $cid;
				$one->name = $row['name'];
				if ($full)
				{
					$one->order= $row['vieworder'];
					$one->category_link = self::GetLink($mod,$id,$returnid,$one->name,$one->name);
				}
				$catarray[$cid] = $one;
			}
		}
		return $catarray;
	}

	function StripTags($str, &$tags)
	{
		foreach($tags as $tag)
		{
			$str = preg_replace('#<'.$tag.'(>|\s[^>]*>)#is', '', $str);
			$str = preg_replace('#</'.$tag.'(>|\s[^>]*>)#is', '<br />', $str);
		}
		return $str;
	}
}

?>
