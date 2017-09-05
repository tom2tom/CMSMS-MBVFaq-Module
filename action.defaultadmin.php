<?php
#----------------------------------------------------------------------
# Module: MBVFaq - a simple FAQ module
# Action: defaultadmin
#----------------------------------------------------------------------
# See file MBVFaq.module.php for full details of copyright,licence,etc.
#----------------------------------------------------------------------

$padm = $this->_CheckAccess('admin');
if ($padm) {
	$padd = TRUE;
	$pdel = TRUE;
	$pmod = TRUE;
} else {
	$padd = $this->_CheckAccess('add');
	$pdel = $this->_CheckAccess('delete');
	$pmod = $this->_CheckAccess('modify');
}
if (!($padm || $padd || $pdel || $pmod)) {
	exit;
}

$mod = $padm || $pmod;
$pdev = $this->CheckPermission('Modify Any Page');

$tplvars = array(
	'adm' => $padm,
	'add' => $padd,
	'del' => $pdel,
	'mod' => $mod, //not $pmod
	'dev' => $pdev
);

$theme = ($this->before20) ? cmsms()->get_variable('admintheme'):
	cms_utils::get_theme_object();
$iconyes = $theme->DisplayImage('icons/system/true.gif', $this->Lang('true'), '', '', 'systemicon');
$iconno = $theme->DisplayImage('icons/system/false.gif', $this->Lang('false'), '', '', 'systemicon');
if ($mod) {
	$iconup = $theme->DisplayImage('icons/system/arrow-u.gif', $this->Lang('up'), '', '', 'systemicon');
	$icondn = $theme->DisplayImage('icons/system/arrow-d.gif', $this->Lang('down'), '', '', 'systemicon');
	$iconopen = $theme->DisplayImage('icons/system/edit.gif', $this->Lang('edititem'), '', '', 'systemicon');
} else {
	$iconopen = $theme->DisplayImage('icons/system/view.gif', $this->Lang('viewitem'), '', '', 'systemicon');
}
if ($pdel) {
	$icondel = $theme->DisplayImage('icons/system/delete.gif', $this->Lang('deleteitem'), '', '', 'systemicon');
}

$owned = $this->GetPreference('owned_categories', FALSE);
$tplvars['grpown'] = $owned;
$allowners = $padm; //admin permission removes limit on category ownership
$showby = $allowners || $owned; //display answerer name even if no owned groups
$tplvars['itmown'] = $showby;

if (isset($params['message'])) {
	$tplvars['message'] = $params['message'];
}

if (isset($params['showtab'])) {
	$showtab = (int)$params['showtab'];
} else {
	$showtab = 0;
} //default
$seetab1 = ($showtab==1);
$seetab2 = ($showtab==2);

$tplvars['tabs_header'] = $this->StartTabHeaders().
 $this->SetTabHeader('items', $this->Lang('items')).
 $this->SetTabHeader('groups', $this->Lang('categories'), $seetab1).
 $this->SetTabHeader('settings', $this->Lang('settings'), $seetab2).
 $this->EndTabHeaders().$this->StartTabContent();

//workaround CMSMS2 crap 'auto-end', EndTab() & EndTabContent() before [1st] StartTab()
$tplvars['end_tab'] = $this->EndTab();
$tplvars['tabs_footer'] = $this->EndTabContent();

//QUESTIONS TAB
$tplvars['startform1'] = $this->CreateFormStart($id, 'processitems', $returnid);
$tplvars['endform'] = $this->CreateFormEnd(); //used for all forms
$tplvars['start_items_tab'] = $this->StartTab('items');

if ($padd) {
	$tplvars['additemlink'] =
	 $this->CreateLink($id, 'openitem', $returnid,
		 $theme->DisplayImage('icons/system/newobject.gif', $this->Lang('additem'), '', '', 'systemicon'),
		 array(), '', FALSE, FALSE, '')
	 .' '.
	 $this->CreateLink($id, 'openitem', $returnid,
		 $this->Lang('additem'),
		 array(), '', FALSE, FALSE, 'class="pageoptions"');
}

//NOTE: changes to the body of the questions table must be replicated
// (to the extent necessary) in MBVFajax::CreateQuestionsBody()

$jsfuncs = array();
$jsloads = array();
$items = array();

$catscope = ($mod) ? ($allowners || !$owned) : TRUE; //show all,when just viewing
// get a simple list of available categories
$funcs = new MBVFshared();
$groups = $funcs->GetCategories($this, 0, 0, FALSE, $catscope);
// get array of all questions in the category(ies),ordered by fields category and vieworder
$wanted = implode(',', array_keys($groups)); //no injection risk from categories keys array
$sql = "SELECT I.*,U.first_name,U.last_name FROM $this->ItemTable I
LEFT JOIN $this->UserTable U ON I.owner = U.user_id
LEFT JOIN $this->CatTable C ON I.category_id = C.category_id WHERE I.category_id IN ($wanted)
ORDER BY C.vieworder,I.vieworder ASC";

$rst = $db->Execute($sql);
if ($rst) {
	$count = 0;
	$previd = -10;

	while ($row = $rst->FetchRow()) {
		$thisid = (int)$row['item_id'];
		$one = new stdClass();

		$one->id = $thisid; //may be hidden
		$neat = $this->ellipsize(strip_tags($row['short_question']), 40, 0.5);
		if ($mod) {
			$one->item		= $this->CreateLink($id, 'openitem', $returnid, $neat,
				array('item_id'=>$thisid));
		} else {
			$one->item		= $neat;
		}
		$gid	 			= $row['category_id'];
		$one->group			= $groups[$gid]->name;
		$one->create_date	= $row['create_date'];
		$one->modify_date	= $row['last_modified_date'];
		if ($showby) {
			$name = trim($row['first_name'].' '.$row['last_name']);
			if ($name == '') {
				$name = '<'.$this->Lang('noowner').'>';
			}
			$one->ownername	= $name;
		}

		if ($mod) {
			if ($row['active']) { //it's active so create a deactivate-link
				$one->active = $this->CreateLink($id, 'toggleitem', $returnid, $iconyes,
					array('item_id'=>$thisid, 'active'=>TRUE));
			} else { //it's inactive so create an activate-link
				$one->active = $this->CreateLink($id, 'toggleitem', $returnid, $iconno,
					array('item_id'=>$thisid, 'active'=>FALSE));
			}

			$one->downlink = '';
			// now check if there is a previous item in the same category if so create the apropriate links
			if ($count && ($previd == $thisid)) {
				$one->uplink = $this->CreateLink($id, 'swapitems', $returnid, $iconup,
					array('item_id'=>$thisid, 'prev_item_id'=>$previd));
				$items[$count-1]->downlink = $this->CreateLink($id, 'swapitems', $returnid, $icondn,
					array('item_id'=>$previd, 'next_item_id'=>$thisid));
			} else {
				$one->uplink = '';
			}
			$previd = $thisid;
		} else {
			$one->active = ($row['active']) ? $iconyes : $iconno;
		}
		//view or edit
		$one->editlink = $this->CreateLink($id, 'openitem', $returnid, $iconopen,
			array('item_id'=>$thisid));

		if ($pdel) {
			$one->deletelink = $this->CreateLink($id, 'deleteitem', $returnid, $icondel,
				array('item_id'=>$thisid),
				$this->Lang('delitm_confirm', $row['short_question']));
		} else {
			$one->deletelink = '';
		}

		$one->selected = $this->CreateInputCheckbox($id, 'selitems[]', $thisid, -1);

		$items[] = $one;
		$count++;
	}
	$rst->Close();
}

if ($mod) {
	$tplvars['dndhelp'] = $this->Lang('help_dnd');
} //might be for items or groups or neither

$icnt = count($items);
$tplvars['icount'] = $icnt;
if ($icnt > 0) {
	$tplvars['items'] = $items;
//	$tplvars['numtext'] = $this->Lang('label_order');
	$tplvars['idtext'] = ($pdev) ? $this->Lang('label_id') : '';
	$tplvars['itemtext'] = $this->Lang('item');
	$tplvars['grptext'] = $this->Lang('category');
	$tplvars['postdatetext'] = $this->Lang('created');
	$tplvars['changedatetext'] = $this->Lang('changed');
	if ($showby) {
		$tplvars['answerertext'] = $this->Lang('label_answerer');
	}
	$tplvars['activetext'] = $this->Lang('active');
	$tplvars['movetext'] = $this->Lang('reorder');

	if ($icnt > 1) {
		$tplvars['selectall_items'] =
			$this->CreateInputCheckbox($id, 'item', TRUE, FALSE, 'onclick="select_all_items(this)"');
	}
	$tplvars['exportbtn1'] =
		$this->CreateInputSubmit($id, 'export', $this->Lang('export'),
		'title="'.$this->Lang('exportselitm').'" onclick="return confirm_selitm_count();"');
	if ($mod) {
		$tplvars['sortbtn1'] =
			$this->CreateInputSubmit($id, 'sort', $this->Lang('sort'),
			'title="'.$this->Lang('sortselitm').'" onclick="return confirm_selitm_count();"');
		$tplvars['ablebtn1'] =
			$this->CreateInputSubmit($id, 'activate', $this->Lang('activate'),
			'title="'.$this->Lang('activateselitm').'" onclick="return confirm_selitm_count();"');
	}
	if ($pdel) {
		$tplvars['deletebtn1'] =
			$this->CreateInputSubmit($id, 'delete', $this->Lang('delete'),
			'title="'.$this->Lang('deleteselitm').'" onclick="return confirm_delete_item();"');
	}

	$t = $this->Lang('delselitm_confirm');
	$jsfuncs[] = <<< EOS
function select_all_items(b)
{
 var st = $(b).attr('checked');
 if(! st) st = false;
 $('input[name="{$id}selitems[]"][type="checkbox"]').attr('checked', st);
}
function selitm_count()
{
 var cb = $('input[name="{$id}selitems[]"]:checked');
 return cb.length;
}
function confirm_selitm_count()
{
 return (selitm_count() > 0);
}
function confirm_delete_item()
{
 if (selitm_count() > 0)
  return confirm('{$t}');
 return false;
}

EOS;
} else {
	$tplvars['idtext'] = '';
	$tplvars['noitems'] = $this->Lang('noitems');
}

//CATEGORIES TAB
$tplvars['start_grps_tab'] = $this->StartTab('groups');
$tplvars['startform2'] = $this->CreateFormStart($id, 'processcats', $returnid);

if (isset($params['extracat'])) {
	$extracat = (int)$params['extracat'];
} else {
	$extracat = FALSE;
}
/*List categories,sorted by vieworder field,with links for move up/down
and optionally with owner pick-lists and optionally ($extracat=TRUE)
with an empty row added to the end	*/
$groups = array();
$uid = get_userid(FALSE); //current admin user

if ($mod) {
	if ($allowners || !$owned) {
		$sql = "SELECT * FROM $this->CatTable ORDER BY vieworder ASC";
	} else {
		$sql = "SELECT * FROM $this->CatTable WHERE owner IN (0,$uid) ORDER BY vieworder ASC";
	} //no injection risk from $uid
} else {
	$sql = "SELECT C.*,U.username,U.first_name,U.last_name FROM $this->CatTable C
	LEFT JOIN $this->UserTable U ON C.owner = U.user_id ORDER BY C.vieworder ASC";
}

$rst = $db->Execute($sql);
if ($rst && !$rst->EOF) {
	if ($mod && $owned) {
		//find all valid owners
		$owners = array('&lt;'.$this->Lang('none').'&gt;' => 0);
		//NOTE cmsms function check_permission() is buggy, always returns
		//FALSE for everyone other than the current user, so we replicate
		//its backend operation here
		$pref = cms_db_prefix();
		$sql = "SELECT DISTINCT U.user_id,U.username,U.first_name,U.last_name FROM $this->UserTable U
JOIN ".$pref."user_groups UG ON U.user_id = UG.user_id
JOIN ".$pref."group_perms GP ON GP.group_id = UG.group_id
JOIN ".$pref."permissions P ON P.permission_id = GP.permission_id
JOIN ".$pref."groups GR ON GR.group_id = UG.group_id
WHERE ";
		if (!$allowners) {
			$sql .= "U.user_id=$uid AND ";
		} //no injection risk
		$sql .= "U.admin_access=1 AND U.active=1 AND GR.active=1 AND
P.permission_name IN('$this->PermAddName','$this->PermAdminName','$this->PermModName')
ORDER BY U.last_name,U.first_name";

		$rst2 = $db->Execute($sql);
		if ($rst2) {
			while ($row = $rst2->FetchRow()) {
				$name = trim($row['first_name'].' '.$row['last_name']);
				if ($name == '') {
					$name = trim($row['username']);
				}
				$owners[$name] = (int)$row['user_id'];
			}
			$rst2->Close();
		}
	}

	$count = 0;
	$previd	= -10;

	while ($row = $rst->FetchRow()) {
		$thisid = (int)$row['category_id'];

		$one = new stdClass();
		$one->id = $thisid; //may be hidden

		$one->order = $row['vieworder'];
		if ($mod) {
			$one->input_name = $this->CreateInputText($id, 'category_names[]', $row['name'], 40);
			if ($owned) {
				$one->input_owner = $this->CreateInputDropdown($id, 'owner_ids[]', $owners, -1, $row['owner']);
			}
			$one->downlink = '';
			// if there is a previous item,create the appropriate links
			if ($count) {
				$one->uplink = $this->CreateLink($id, 'swapcats', $returnid,
					$theme->DisplayImage('icons/system/arrow-u.gif', $this->Lang('up'), '', '', 'systemicon'),
					array('category_id'=>$thisid, 'prev_category_id'=>$previd));
				$groups[($count-1)]->downlink = $this->CreateLink($id, 'swapcats', $returnid,
					$theme->DisplayImage('icons/system/arrow-d.gif', $this->Lang('down'), '', '', 'systemicon'),
					array('category_id'=>$previd, 'next_category_id'=>$thisid));
			} else {
				$one->uplink = '';
			}
			$previd = $thisid;

			if ($thisid > 0) { //preserve the default category
				$one->deletelink = $this->CreateLink($id, 'deletecategory', $returnid,
					$theme->DisplayImage('icons/system/delete.gif', $this->Lang('deletecategory'), '', '', 'systemicon'),
					array('category_id'=> $thisid),
					$this->Lang('delgrp_confirm', $row['name']));
			} else {
				$one->deletelink = '';
			}
		} else {
			$one->input_name = $row['name'];
			if ($row['owner'] == 0) { //anyone authorised
				$one->input_owner = '';
			} else {
				$name = trim($row['first_name'].' '.$row['last_name']);
				if ($name == '') {
					$name = trim($row['username']);
				}
				if ($name == '') {
					$name = '<'.$this->Lang('noowner').'>';
				}
				$one->input_owner = $name;
			}
		}
		$one->selected = $this->CreateInputCheckbox($id, 'selgrps[]', $thisid, -1);

		$groups[] = $one;
		$count++;
	}
	$rst->Close();
}

if ($mod && $extracat) {
	// append an empty row
	$one = new stdClass();

	$one->id		= -1;
	$one->order		= count($groups)+1;
	$one->name		= '';
	$one->input_name = $this->CreateInputText($id, 'category_names[]', '', 40);
	if ($owned) {
		$one->input_owner = $this->CreateInputDropdown($id, 'owner_id', $owners);
	} //,-1,$item->category_id));
	$one->selected	= $this->CreateInputCheckbox($id, 'selgrps[]', -1, 0);
	$one->downlink	= '';
	$one->uplink	= '';

	$groups[] = $one;
}

$gcnt = count($groups);
$tplvars['gcount'] = $gcnt;
if ($gcnt > 0) {
	$tplvars['grpitems'] = $groups;
	$tplvars['grpidtext'] = ($pdev) ? $this->Lang('label_id') : '';
	$tplvars['grptext'] = $this->Lang('category');
	$tplvars['ownertext'] = $this->Lang('owner');
	if ($gcnt > 1) {
		$tplvars['selectall_grps'] =
			$this->CreateInputCheckbox($id, 'cat', TRUE, FALSE, 'onclick="select_all_groups(this)"');
	}
	//buttons
	$tplvars['exportbtn2'] = $this->CreateInputSubmit($id, 'export',
		$this->Lang('export'),
		'title="'.$this->Lang('exportselgrp').'" onclick="return confirm_selgrp_count();"');
	if ($pmod) {
		$tplvars['sortbtn2'] = $this->CreateInputSubmit($id, 'sort',
			$this->Lang('sort'),
			'title="'.$this->Lang('sortselected').'" onclick="return confirm_selgrp_count();"');
		$tplvars['submitbtn2'] = $this->CreateInputSubmit($id, 'update',
			$this->Lang('update'),
			'title="'.$this->Lang('updateselected').'" onclick="return confirm_selgrp_count();"');
	}
	if ($pdel) {
		$tplvars['deletebtn2'] = $this->CreateInputSubmit($id, 'delete',
			$this->Lang('delete'),
			'title="'.$this->Lang('deleteselgrp').'" onclick="return confirm_delete_grp();"');
	}

	$t = $this->Lang('delselgrp_confirm');
	$jsfuncs[] = <<< EOS
function select_all_groups(b)
{
 var st = $(b).attr('checked');
 if(! st) st = false;
 $('input[name="{$id}selgrps[]"][type="checkbox"]').attr('checked', st);
}
function selgrp_count()
{
 var cb = $('input[name="{$id}selgrps[]"]:checked');
 return cb.length;
}
function confirm_selgrp_count()
{
 return (selgrp_count() > 0);
}
function confirm_delete_grp()
{
 if (selgrp_count() > 0)
  return confirm('{$t}');
 return false;
}

EOS;
} else {
	$tplvars['grpidtext'] = '';
	$tplvars['nogroups'] = $this->Lang('nocategories');
}

if ($padd) {
	$t = $this->Lang('addcategory');
	$tplvars['addgrplink'] = $this->CreateLink($id, 'addcategory', $returnid,
		$theme->DisplayImage('icons/system/newobject.gif', $t, '', '', 'systemicon'),
			array(), '', FALSE, FALSE, '')
		.' '.
		$this->CreateLink($id, 'addcategory', $returnid, $t,
			array(), '', FALSE, FALSE, 'class="pageoptions"');
}

if ($mod) {
	//another button
	$tplvars['cancel'] = $this->CreateInputSubmit($id, 'cancel', $this->Lang('cancel'));
}

//SETTINGS TAB
$tplvars['start_settings_tab'] = $this->StartTab('settings');
if ($padm) {
	$tplvars['startform3'] = $this->CreateFormStart($id, 'setprefs', $returnid);
	// preference controls (added in display-order)
	$settings = array();

	$one = new stdClass();
	$one->title = $this->Lang('option_clear_cat');
	$one->input = $this->CreateInputCheckbox($id, 'mbvf_clear_category', TRUE,
		$this->GetPreference('clear_category', FALSE), '');
	$settings[] = $one;

	$one = new stdClass();
	$one->title = $this->Lang('option_user_cats');
	$one->input = $this->CreateInputCheckbox($id, 'mbvf_owned_categories', TRUE,
		$this->GetPreference('owned_categories', FALSE), '');
	$settings[] = $one;

	$one = new stdClass();
	$one->title = $this->Lang('option_short_question');
	$one->input = $this->CreateInputCheckbox($id, 'mbvf_short_question', TRUE,
		$this->GetPreference('short_question', TRUE), '');
	$settings[] = $one;

	$one = new stdClass();
	$one->title = $this->Lang('option_short_answer');
	$one->input = $this->CreateInputCheckbox($id, 'mbvf_short_answer', TRUE,
		$this->GetPreference('short_answer', TRUE), '');

	$one = new stdClass();
	$one->title = $this->Lang('option_use_jquery');
	$one->input = $this->CreateInputCheckbox($id, 'mbvf_use_jquery', TRUE,
		$this->GetPreference('use_jquery', TRUE), '');
	$settings[] = $one;

	$one = new stdClass();
	$one->title = $this->Lang('option_ignore_click');
	$one->input = $this->CreateInputCheckbox($id, 'mbvf_ignore_click', TRUE,
		$this->GetPreference('ignore_click', TRUE), '');
	$settings[] = $one;

	$tplvars['settings'] = $settings;
	//buttons (also 'cancel')
	$tplvars['submitbtn3'] =
		$this->CreateInputSubmit($id, 'submit', $this->Lang('apply'));
} else {
	$tplvars['nopermission'] = $this->Lang('accessdenied3');
}

$idc = ($pdev) ? 'seeid' : 'hideid';
$tplvars['idclass'] = $idc;

if ($icnt > 0 || $gcnt > 0) {
	$t = $this->Lang('error_server');
	$u = $this->create_url($id, '|X|', '', array('droporder'=>''));
	$offs = strpos($u, '?mact=');
	$u = str_replace('&amp;', '&', substr($u, $offs+1));
	$up = explode('|X|', $u);
	$jsfuncs[] = <<< EOS
function dropresponse(data,status)
{
 if (status=='success') {
  if (data != '') {
   $('#items > tbody').html(data);
  }
 } else {
  $('#page_tabs').prepend('<p style="font-weight:bold;color:red;">{$t}!</p><br />');
 }
}

EOS;
	$jsloads[] = <<< EOS
 $('.updown').hide();
 $('.dndhelp').css('display','block');
 $('.table_drag').tableDnD({
  onDragClass: 'row1hover',
  onDrop: function(table, droprows){
   var name;
   var odd = true;
   var oddclass = 'row1';
   var evenclass = 'row2';
   var droprow = $(droprows)[0];
   $(table).find('tbody tr').each(function(){
	name = odd ? oddclass : evenclass;
	if (this === droprow){
	 name = name+'hover';
	}
	$(this).removeClass().addClass(name);
	odd = !odd;
   });

   var act = (table.id=='items') ? 'moveitem':'movecategory';
   var allrows = $(droprow.parentNode).children();
   var curr = droprow.rowIndex - 2;
   var droporder = (curr < 0) ? 'null' : $(allrows[curr]).find('> td.{$idc}:first').html();
   curr++;
   var dropcount = droprows.length;
   while (dropcount > 0){
	droporder = droporder+','+$(allrows[curr]).find('> td.{$idc}:first').html();
	curr++;
	dropcount--;
   }
   droporder = droporder+','+$(allrows[curr]).find('> td.{$idc}:first').html(); //'target' may be 'null'

   $.ajax({
	type: 'POST',
	url: 'moduleinterface.php',
	data: '{$up[0]}'+act+'{$up[1]}'+droporder,
	success: dropresponse,
	dataType: 'html'
   });
  }
 }).find('tbody tr').removeAttr('onmouseover').removeAttr('onmouseout')
   .mouseover(function(){
  var now = $(this).attr('class');
  $(this).attr('class', now+'hover');
 }).mouseout(function() {
  var now = $(this).attr('class');
  var to = now.indexOf('hover');
  $(this).attr('class', now.substring(0,to));
 });

EOS;

	$tplvars['jsincs'] =
		'<script type="text/javascript" src="'.$this->GetModuleURLPath().'/lib/js/jquery.tablednd.min.js"></script>';
}

if ($jsloads) {
	$jsfuncs[] = '$(document).ready(function() {
';
	$jsfuncs = array_merge($jsfuncs, $jsloads);
	$jsfuncs[] = '});
';
}
$tplvars['jsfuncs'] = $jsfuncs;

$funcs->ProcessTemplate($this, 'adminpanel.tpl', $tplvars);
