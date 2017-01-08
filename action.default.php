<?php
#----------------------------------------------------------------------
# Module: MBVFaq - a simple FAQ module
# Action: default
# Default frontend action for the module
#----------------------------------------------------------------------
# See file MBVFaq.module.php for full details of copyright, licence, etc.
#----------------------------------------------------------------------

$funcs = new MBVFshared();

//convert deprecated formats
if (isset($params['faq_id'])) {
	$params['faq'] = $params['faq_id'];
}
if (isset($params['category'])) {
	$params['cat'] = $params['category'];
}

// are we displaying a single item ?
if (!isset($params['cat']) && isset($params['faq']) && $params['faq'] != '') {
	// get data for the matching faq
	$item = $funcs->GetItem($this, $params['faq']);
	if ($this->GetPreference('short_question', TRUE)) {
		if ($item->question == '') {
			$item->question = $item->long_question;
		}
	} elseif ($item->long_question != '') {
		$item->question = $item->long_question;
	}

	if ($this->GetPreference('short_answer', TRUE)) {
		if ($item->short_answer != '') {
			$item->answer = $item->short_answer;
		} else {
			$item->answer = $item->long_answer;
		}
	} elseif ($item->long_answer != '') {
		$item->answer = $item->long_answer;
	} else {
		$item->answer = $item->short_answer;
	}

	$tplvars = array(
	'item' => $item,
	'styles_root' => $this->GetModuleURLPath().'/css',
	'label_question' => $this->Lang('item'),
	'label_answer' => $this->Lang('answer')
	);

	$funcs->ProcessTemplate($this, 'detail.tpl', $tplvars);
} else {
	if (isset($config['default_encoding']) && $config['default_encoding'] != '') {
		$enc = strtoupper($config['default_encoding']);
	} else {
		$enc = FALSE;
	}

	$categories = array();

	$thiscat = '';
	if (isset($params['cat']) && $params['cat'] != '') { // is cat= parameter present?
		$thiscat = $params['cat'];
	}
	if ($thiscat == '') {	// [re]list all categories
		$categories = $funcs->GetCategories($this, $id, $returnid, TRUE, TRUE);
	} else {
		$thiscat = ($enc) ?
			html_entity_decode($thiscat, ENT_QUOTES, $enc):
			html_entity_decode($thiscat, ENT_QUOTES);
	 /* Convert string (having a name or id, or possibly ;-separated names
		and/or ids) into an array of category data, each member an array
		of some of the tabled data for the corresponding category */
		$wanted = explode(";", $thiscat);
		// now we have an array of categories to display
		foreach ($wanted as $choice) {
			if (choice != '') {
				$sql = "SELECT * FROM $this->CatTable WHERE ";
				// find out if we have an id or a name
				if ((intval($choice)==0) && ($choice<>"0")) {	// it's a string, so assume it's a name
					$sql .= "name=?";
				} else {	// not a string so assume it's an id
					$sql .= "category_id=?";
				}
				$queryvars = array(trim($choice));

				$rs = $db->Execute($sql, $queryvars);
				if ($rs) {
					while ($row = $rs->FetchRow()) {
						$one = new stdClass();
						$one->category_id = $row['category_id'];
						$one->name = $row['name'];
						$one->order= $row['vieworder'];
						$one->category_link = $funcs->GetLink($this, $id, $returnid, $one->name, $one->name);
						$categories[$one->category_id] = $one; //lose any previous category with the same index
					}
					$rs->Close();
				}
			}
		}
	}

	if (isset($params['pattern'])) {
		$pattern = ($enc) ?
			html_entity_decode($params['pattern'], ENT_QUOTES, $enc):
			html_entity_decode($params['pattern'], ENT_QUOTES);
		if (substr($pattern, 0, 1)=='!') {
			$neg = TRUE;
			$pattern = substr($pattern, 1);
		} else {
			$neg = FALSE;
		}

		if (!function_exists('fnmatch')) {
			function fnmatch($pattern, $string, $flags)
			{
				return @preg_match(
				'/^' . strtr(addcslashes($pattern, '/\\.+^$(){}=!<>|'),
				array('*' => '.*', '?' => '.?')) . '$/i', $string);
			}
		}
	} else {
		$pattern = FALSE;
	}

	if (isset($params['regex'])) {
		$regex = ($enc) ?
			html_entity_decode($params['regex'], ENT_QUOTES, $enc):
			html_entity_decode($params['regex'], ENT_QUOTES);
		if (substr($regex, 0, 1)=='!') {
			$negr = TRUE;
			$regex = substr($regex, 1);
		} else {
			$negr = FALSE;
		}
	} else {
		$regex= FALSE;
	}

	$tplvars = array();
	$sq = $this->GetPreference('short_question', TRUE);
	$sa = $this->GetPreference('short_answer', TRUE);
	$multi = (count($categories) > 1);
	$cc = 1; //div id counter

	// populate questions for the categories
	foreach ($categories as $indx => $category) {
		/* Construct an array of objects, one member for each of the active
		questions in the specified category, and set the array as the
		->items parameter of $category object. Each non-empty category
		will then have the following parameters:
			category_id	// the internal id used to identify the category
			name		// the name of the category
			order		// the sort-order number of the category
			category_link // link that will display the category on a page by itself
			items		// an array of objects for all published questions in the category
		Each member of the items array is an object with the
		following parameters:
			item_id
			category_id
			category
			question
			answer
			itemlink
			divid	//id for answer div, N or C.N
		*/
		$sql = "SELECT * FROM $this->ItemTable WHERE category_id=? AND active=1 ORDER BY vieworder ASC";
		$rs = $db->Execute($sql, array($category->category_id));
		if ($rs) {
			$qc = 1; //div id counter
			$items = array();
			while ($row = $rs->FetchRow()) {
				if ($sq) {
					$s = $row['short_question'];
					if ($s == '') {
						$s = $row['long_question'];
					}
				} else {
					$s = $row['long_question'];
					if ($s == '') {
						$s = $row['short_question'];
					}
				}

				if ($pattern) {
					if (fnmatch($pattern, $s, FNM_NOESCAPE | FNM_PATHNAME | FNM_PERIOD) != FALSE) {
						if ($neg) {
							continue;
						}
					} elseif (!$neg) {
						continue;
					}
				}

				if ($regex) {
					if (preg_match('/'.$regex.'/', $s) !== FALSE) {
						if ($negr) {
							continue;
						}
					} elseif (!$negr) {
						continue;
					}
				}

				$one = new stdClass();
				$one->divid = ($multi) ? $cc.'-'.$qc : $qc; //jQuery can't cope with period in id name
				$qc++;

				$one->item_id = $row['item_id'];
				$one->category_id = $row['category_id'];
				$one->category = $category->name;
				$one->question = $s;

				if ($sa) {
					$s = $row['short_answer'];
					if ($s == '') {
						$s = $row['long_answer'];
					}
				} else {
					$s = $row['long_answer'];
					if ($s == '') {
						$s = $row['short_answer'];
					}
				}
				$one->answer = $funcs->ProcessTemplateFromData($this, $s, $tplvars);
				$one->itemlink = $funcs->GetLink($this, $id, $returnid, strip_tags($row['short_question']), '', $row['item_id']);
				$items[] = $one;
			}
			$rs->Close();

			if (count($items) > 0) {
				$category->items = $items;
				$cc++;
			} else { //if category is empty, ignore it
				unset($categories[$indx]);
			}
		}
	}

	$tplvars['cats'] = $categories;
	$numcat = count($categories);
	$tplvars['catcount'] = $numcat;

	if ($numcat > 0 && $this->GetPreference('use_jquery', TRUE)) {
		if ($numcat == 1) {
			//don't want 1-X for a single category
			$first = reset($categories);
			$qc = 1; //div id counter
			foreach ($first->items as &$one) {
				$one->divid = $qc;
				$qc++;
			}
			unset($one);
		}
	
		$fn = cms_join_path(dirname(__FILE__), 'include', 'jquery-faq.js');
		$jq = ''.@file_get_contents($fn);
		//generalise jquery lib from the default "jquery.min.js"
		$wpat = cms_join_path('lib', 'jquery', 'js', 'jquery*.min.js');
		$pat = cms_join_path('lib', 'jquery', 'js', 'jquery.min.js');
		foreach (glob($wpat) as $filepath) {
			$jq = str_replace($pat, $filepath, $jq);
			break;
		}
		$tplvars['jquery'] = $jq;
	} else {
		$tplvars['noitems'] = $this->Lang('noitems');
	}

	// Display the populated template
	$funcs->ProcessTemplate($this, 'overview.tpl', $tplvars);
}
