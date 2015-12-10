<?php
#----------------------------------------------------------------------
# Module: MBVFaq - a simple FAQ module
# Action: defaultadmin
#----------------------------------------------------------------------
# See file MBVFaq.module.php for full details of copyright,licence,etc.
#----------------------------------------------------------------------

$pdev = $this->CheckPermission('Modify Any Page');

$padm = $this->_CheckAccess('admin');
if ($padm)
{
	$padd = true;
	$pdel = true;
	$pmod = true;
}
else
{
	$padd = $this->_CheckAccess('add');
	$pdel = $this->_CheckAccess('delete');
	$pmod = $this->_CheckAccess('modify');
}

$mod = $padm || $pmod;

$smarty->assign('dev',$pdev);
$smarty->assign('adm',$padm);
$smarty->assign('add',$padd);
$smarty->assign('del',$pdel);
$smarty->assign('mod',$mod); //not $pmod

$theme = ($this->before20) ? cmsms()->variables['admintheme']:
	cms_utils::get_theme_object();
$iconyes = $theme->DisplayImage('icons/system/true.gif',$this->Lang('true'),'','','systemicon');
$iconno = $theme->DisplayImage('icons/system/false.gif',$this->Lang('false'),'','','systemicon');
if ($mod)
{
	$iconup = $theme->DisplayImage('icons/system/arrow-u.gif',$this->Lang('up'),'','','systemicon');
	$icondn = $theme->DisplayImage('icons/system/arrow-d.gif',$this->Lang('down'),'','','systemicon');
	$iconopen = $theme->DisplayImage('icons/system/edit.gif',$this->Lang('edititem'),'','','systemicon');
}
else
	$iconopen = $theme->DisplayImage('icons/system/view.gif',$this->Lang('viewitem'),'','','systemicon');
if ($pdel)
	$icondel = $theme->DisplayImage('icons/system/delete.gif',$this->Lang('deleteitem'),'','','systemicon');

$owned = $this->GetPreference('owned_categories',false);
$smarty->assign('grpown',$owned);
$allowners = $padm; //admin permission removes limit on category ownership
$showby = $allowners || $owned; //display answerer name even if no owned groups
$smarty->assign('itmown',$showby);

if(isset($params['message']))
	$smarty->assign('message',$params['message']);

if(isset($params['showtab']))
	$showtab = (int)$params['showtab'];
else
	$showtab = 0; //default
$seetab1 = ($showtab==1);
$seetab2 = ($showtab==2);

$smarty->assign('tab_headers',$this->StartTabHeaders().
 $this->SetTabHeader('items',$this->Lang('items')).
 $this->SetTabHeader('groups',$this->Lang('categories'),$seetab1).
 $this->SetTabHeader('settings',$this->Lang('settings'),$seetab2).
 $this->EndTabHeaders().$this->StartTabContent());
$smarty->assign('tab_footers',$this->EndTabContent());
$smarty->assign('end_tab',$this->EndTab());

//QUESTIONS TAB
$smarty->assign('startform1',$this->CreateFormStart($id,'processitems',$returnid));
$smarty->assign('endform',$this->CreateFormEnd()); //used for all forms
$smarty->assign('start_items_tab',$this->StartTab('items'));

if ($padd)
{
	$smarty->assign('additemlink',
	 $this->CreateLink($id,'openitem',$returnid,
		 $theme->DisplayImage('icons/system/newobject.gif',$this->Lang('additem'),'','','systemicon'),
		 array(),'',false,false,'')
	 .' '.
	 $this->CreateLink($id,'openitem',$returnid,
		 $this->Lang('additem'),
		 array(),'',false,false,'class="pageoptions"'));
}

//NOTE: changes to the body of the questions table must be replicated
// (to the extent necessary) in MBVFajax::CreateQuestionsBody()

$jsfuncs = array();
$items = array();

$catscope = ($mod) ? ($allowners || !$owned) : true; //show all,when just viewing
// get a simple list of available categories
$funcs = new MBVFshared();
$groups = $funcs->GetCategories($this,0,0,false,$catscope);
// get array of all questions in the category(ies),ordered by fields category and vieworder
$wanted = implode (',',array_keys($groups)); //no injection risk from categories keys array
$sql = "SELECT I.*,U.first_name,U.last_name FROM $this->ItemTable I
LEFT JOIN $this->UserTable U ON I.owner = U.user_id
LEFT JOIN $this->CatTable C ON I.category_id = C.category_id WHERE I.category_id IN ($wanted)
ORDER BY C.vieworder,I.vieworder ASC";

$rs = $db->Execute($sql);
if ($rs)
{
	$count = 0;
	$previd = -10;
 
	while ($row = $rs->FetchRow())
	{
		$thisid 			= (int)$row['item_id'];
		$one = new stdClass();

		$one->id		= $thisid; //may be hidden
		$neat = $this->ellipsize(strip_tags($row['short_question']),40,0.5);
		if ($mod)
			$one->item		= $this->CreateLink($id,'openitem',$returnid,$neat,
				array('item_id'=>$thisid));
		else
			$one->item		= $neat;
		$gid	 			= $row['category_id'];
		$one->group			= $groups[$gid]->name;
		$one->create_date	= $row['create_date'];
		$one->modify_date	= $row['last_modified_date'];
		if($showby)
		{
			$name = trim($row['first_name'].' '.$row['last_name']);
			if ($name == '') $name = '<'.$this->Lang('noowner').'>';
			$one->ownername	= $name;
		}
		
		if ($mod)
		{
			if ($row['active']) //it's active so create a deactivate-link
				$one->active = $this->CreateLink($id,'toggleitem',$returnid,$iconyes,
					array('item_id'=>$thisid,'active'=>true));
			else //it's inactive so create an activate-link
				$one->active = $this->CreateLink($id,'toggleitem',$returnid,$iconno,
					array('item_id'=>$thisid,'active'=>false));

			$one->downlink = '';
			// now check if there is a previous item in the same category if so create the apropriate links
			if ($count && ($previd == $thisid))
			{
				$one->uplink = $this->CreateLink($id,'swapitems',$returnid,$iconup,
					array('item_id'=>$thisid,'prev_item_id'=>$previd));
				$items[$count-1]->downlink = $this->CreateLink($id,'swapitems',$returnid,$icondn,
					array('item_id'=>$previd,'next_item_id'=>$thisid));
			}
			else
				$one->uplink = '';
			$previd = $thisid;
		}
		else
		{
			$one->active = ($row['active']) ? $iconyes : $iconno;
		}
		//view or edit
		$one->editlink = $this->CreateLink($id,'openitem',$returnid,$iconopen,
			array('item_id'=>$thisid));

		if ($pdel)
			$one->deletelink = $this->CreateLink($id,'deleteitem',$returnid,$icondel,
				array('item_id'=>$thisid),
				$this->Lang('delitm_confirm',$row['short_question']));
		else
			$one->deletelink = '';

		$one->selected = $this->CreateInputCheckbox($id,'selitems[]',$thisid,-1);

		$items[] = $one;
		$count++;
	}
	$rs->Close();
}

if ($mod)
	$smarty->assign('dndhelp',$this->Lang('help_dnd'));

$icnt = count($items);
$smarty->assign('icount',$icnt);
if ($icnt > 0)
{
	$smarty->assign('items',$items);
	//$smarty->assign('numtext',$this->Lang('label_order'));
	$smarty->assign('idtext',($pdev) ? $this->Lang('label_id') : '');
	$smarty->assign('itemtext',$this->Lang('item'));
	$smarty->assign('grptext',$this->Lang('category'));
	$smarty->assign('postdatetext',$this->Lang('created'));
	$smarty->assign('changedatetext',$this->Lang('changed'));
	if($showby)
		$smarty->assign('answerertext',$this->Lang('label_answerer'));
	$smarty->assign('activetext',$this->Lang('active'));
	if($icnt > 1)
		$smarty->assign('selectall_items',
			$this->CreateInputCheckbox($id,'item',true,false,'onclick="select_all_items(this)"'));
	$smarty->assign('exportbtn1',
		$this->CreateInputSubmit($id,'export',$this->Lang('export'),
		'title="'.$this->Lang('exportselitm').'" onclick="return confirm_selitm_count();"'));
	if ($mod)
	{
		$smarty->assign('sortbtn1',
			$this->CreateInputSubmit($id,'sort',$this->Lang('sort'),
			'title="'.$this->Lang('sortselitm').'" onclick="return confirm_selitm_count();"'));
		$smarty->assign('ablebtn1',
			$this->CreateInputSubmit($id,'activate',$this->Lang('activate'),
			'title="'.$this->Lang('activateselitm').'" onclick="return confirm_selitm_count();"'));
	}
	if ($pdel)
		$smarty->assign('deletebtn1',
			$this->CreateInputSubmit($id,'delete',$this->Lang('delete'),
			'title="'.$this->Lang('deleteselitm').'" onclick="return confirm_delete_item();"'));

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
}
else
{
	$smarty->assign('idtext','');
	$smarty->assign('noitems',$this->Lang('noitems'));
}

//CATEGORIES TAB
$smarty->assign('start_grps_tab',$this->StartTab('groups'));
$smarty->assign('startform2',$this->CreateFormStart($id,'processcats',$returnid));

if(isset($params['extracat']))
	$extracat = (int)$params['extracat'];
else
	$extracat = false;
/*List categories,sorted by vieworder field,with links for move up/down
and optionally with owner pick-lists and optionally ($extracat=true)
with an empty row added to the end	*/
$groups = array();
$uid = get_userid(false); //current admin user

if ($mod)
{
	if ($allowners || !$owned)
		$sql = "SELECT * FROM $this->CatTable ORDER BY vieworder ASC";
	else
		$sql = "SELECT * FROM $this->CatTable WHERE owner IN (0,$uid) ORDER BY vieworder ASC"; //no injection risk from $uid
}
else
{
	$sql = "SELECT C.*,U.username,U.first_name,U.last_name FROM $this->CatTable C
	LEFT JOIN $this->UserTable U ON C.owner = U.user_id ORDER BY C.vieworder ASC";
}

$rs = $db->Execute($sql);
if ($rs)
{
	if ($mod && $owned)
	{
		//find all valid owners
		$owners = array('&lt;'.$this->Lang('none').'&gt;' => 0);
		//NOTE cmsms function check_permission() is buggy,always returns
		//false for everyone other than the current user, so we replicate
		//its backend operation here
		$pref = cms_db_prefix();
		$sql = "SELECT DISTINCT U.user_id,U.username,U.first_name,U.last_name FROM $this->UserTable U
INNER JOIN ".$pref."user_groups UG ON U.user_id = UG.user_id
INNER JOIN ".$pref."group_perms GP ON GP.group_id = UG.group_id
INNER JOIN ".$pref."permissions P ON P.permission_id = GP.permission_id
INNER JOIN ".$pref."groups GR ON GR.group_id = UG.group_id
WHERE ";
		if (!$allowners)
			$sql .= "U.user_id=$uid AND "; //no injection risk
		$sql .= "U.admin_access=1 AND U.active=1 AND GR.active=1 AND
P.permission_name IN('$this->PermAddName','$this->PermAdminName','$this->PermModName')
ORDER BY U.last_name,U.first_name";

		$rs2 = $db->Execute($sql);
		if ($rs2)
		{
			while ($row = $rs2->FetchRow())
			{
				$name = trim($row['first_name'].' '.$row['last_name']);
				if ($name == '')
					$name = trim($row['username']);
				$owners[$name] = (int)$row['user_id'];
			}
			$rs2->Close();
		}
	}

	$count = 0;
	$previd	= -10;

	while ($row = $rs->FetchRow())
	{
		$thisid = (int)$row['category_id'];

		$one = new stdClass();
		$one->id = $thisid; //may be hidden

		$one->order = $row['vieworder'];
		if ($mod)
		{
			$one->input_name = $this->CreateInputText($id,'category_names[]',$row['name'],40);
			if ($owned)
				$one->input_owner = $this->CreateInputDropdown($id,'owner_ids[]',$owners,-1,$row['owner']);
			$one->downlink = '';
			// if there is a previous item,create the appropriate links
			if ($count)
			{
				$one->uplink = $this->CreateLink($id,'swapcats',$returnid,
					$theme->DisplayImage('icons/system/arrow-u.gif',$this->Lang('up'),'','','systemicon'),
					array('category_id'=>$thisid,'prev_category_id'=>$previd));
				$groups[($count-1)]->downlink = $this->CreateLink($id,'swapcats',$returnid,
					$theme->DisplayImage('icons/system/arrow-d.gif',$this->Lang('down'),'','','systemicon'),
					array('category_id'=>$previd,'next_category_id'=>$thisid));
			}
			else
				$one->uplink = '';
			$previd = $thisid;

			if ($thisid > 0) //preserve the default category
				$one->deletelink = $this->CreateLink($id,'deletecategory',$returnid,
					$theme->DisplayImage('icons/system/delete.gif',$this->Lang('deletecategory'),'','','systemicon'),
					array('category_id'=> $thisid),
					$this->Lang('delgrp_confirm',$row['name']));
			else
				$one->deletelink = '';
		}
		else
		{
			$one->input_name = $row['name'];
			if ($row['owner'] == 0) //anyone authorised
				$one->input_owner = '';
			else
			{
				$name = trim($row['first_name'].' '.$row['last_name']);
				if ($name == '') $name = trim($row['username']);
				if ($name == '') $name = '<'.$this->Lang('noowner').'>';
				$one->input_owner = $name;
			}
		}
		$one->selected = $this->CreateInputCheckbox($id,'selgrps[]',$thisid,-1);

		$groups[] = $one;
		$count++;
	}
	$rs->Close();
}

if ($mod && $extracat)
{
	// append an empty row
	$one = new stdClass();
	
	$one->id		= -1;
	$one->order		= count($groups)+1;
	$one->name		= '';
	$one->input_name = $this->CreateInputText($id,'category_names[]','',40);
	if ($owned)
		$one->input_owner = $this->CreateInputDropdown($id,'owner_id',$owners); //,-1,$item->category_id));
	$one->selected	= $this->CreateInputCheckbox($id,'selgrps[]',-1,0);
	$one->downlink	= '';
	$one->uplink	= '';

	$groups[] = $one;
}

$gcnt = count($groups);
$smarty->assign('gcount',$gcnt);
if ($gcnt > 0)
{
	$smarty->assign('grpitems',$groups);
	$smarty->assign('grpidtext',($pdev) ? $this->Lang('label_id') : '');
	$smarty->assign('grptext',$this->Lang('category'));
	$smarty->assign('ownertext',$this->Lang('owner'));
	if ($gcnt > 1)
		$smarty->assign('selectall_grps',
			$this->CreateInputCheckbox($id,'cat',true,false,'onclick="select_all_groups(this)"'));
	//buttons
	$smarty->assign('exportbtn2',$this->CreateInputSubmit($id,'export',
		$this->Lang('export'),
		'title="'.$this->Lang('exportselgrp').'" onclick="return confirm_selgrp_count();"'));
	if ($pmod)
	{
		$smarty->assign('sortbtn2',$this->CreateInputSubmit($id,'sort',
			$this->Lang('sort'),
			'title="'.$this->Lang('sortselected').'" onclick="return confirm_selgrp_count();"'));
		$smarty->assign('submitbtn2',$this->CreateInputSubmit($id,'update',
			$this->Lang('update'),
			'title="'.$this->Lang('updateselected').'" onclick="return confirm_selgrp_count();"'));
	}
	if ($pdel)
		$smarty->assign('deletebtn2',$this->CreateInputSubmit($id,'delete',
			$this->Lang('delete'),
			'title="'.$this->Lang('deleteselgrp').'" onclick="return confirm_delete_grp();"'));
			
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
}
else
{
	$smarty->assign('grpidtext','');
	$smarty->assign('nogroups',$this->Lang('nocategories'));
}

if ($padd)
{
	$smarty->assign('addgrplink',$this->CreateLink($id,'addcategory',$returnid,
		$theme->DisplayImage('icons/system/newobject.gif',$this->Lang('addcategory'),'','','systemicon'),
			array(),'',false,false,'')
		.' '.
		$this->CreateLink($id,'addcategory',$returnid,
			$this->Lang('addcategory'),
			array(),'',false,false,'class="pageoptions"'));
}

if ($mod)
	//another button
	$smarty->assign('cancel',$this->CreateInputSubmit($id,'cancel',$this->Lang('cancel')));

//SETTINGS TAB
$smarty->assign('start_settings_tab',$this->StartTab('settings'));
if ($padm)
{
	$smarty->assign('startform3',$this->CreateFormStart($id,'setprefs',$returnid));
	// preference controls (added in display-order)
	$settings = array();

	$one = new stdClass();
	$one->title = $this->Lang('option_clear_cat');
	$one->input = $this->CreateInputCheckbox($id,'mbvf_clear_category',true,
		$this->GetPreference('clear_category',false),'');
	$settings[] = $one;

	$one = new stdClass();
	$one->title = $this->Lang('option_user_cats');
	$one->input = $this->CreateInputCheckbox($id,'mbvf_owned_categories',true,
		$this->GetPreference('owned_categories',false),'');
	$settings[] = $one;

	$one = new stdClass();
	$one->title = $this->Lang('option_short_question');
	$one->input = $this->CreateInputCheckbox($id,'mbvf_short_question',true,
		$this->GetPreference('short_question',true),'');
	$settings[] = $one;

	$one = new stdClass();
	$one->title = $this->Lang('option_short_answer');
	$one->input = $this->CreateInputCheckbox($id,'mbvf_short_answer',true,
		$this->GetPreference('short_answer',true),'');

	$one = new stdClass();
	$one->title = $this->Lang('option_use_jquery');
	$one->input = $this->CreateInputCheckbox($id,'mbvf_use_jquery',true,
		$this->GetPreference('use_jquery',true),'');
	$settings[] = $one;

	$one = new stdClass();
	$one->title = $this->Lang('option_ignore_click');
	$one->input = $this->CreateInputCheckbox($id,'mbvf_ignore_click',true,
		$this->GetPreference('ignore_click',true),'');
	$settings[] = $one;

	$smarty->assign('settings',$settings);
	//buttons (also 'cancel')
	$smarty->assign('submitbtn3',
		$this->CreateInputSubmit($id,'submit',$this->Lang('apply')));
}
else
{
	$smarty->assign('nopermission',$this->Lang('accessdenied3'));
}

$idc = ($pdev) ? 'seeid' : 'hideid';
$smarty->assign('idclass',$idc);

if($icnt > 0 || $gcnt > 0)
{
	$t = $this->Lang('error_server');
	$u = $this->create_url($id,'|X|','',array('droporder'=>''));
	$offs = strpos($u,'?mact=');
	$u = str_replace('&amp;','&',substr($u,$offs+1));
	$up = explode('|X|',$u);
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
	
$(function() {
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
 $('.updown').hide();
 $('.dndhelp').css('display','block');
});

EOS;
	$smarty->assign('plugins',
		'<script type="text/javascript" src="'.$this->GetModuleURLPath().'/include/jquery.tablednd.min.js"></script>');
	$smarty->assign('jsfuncs',$jsfuncs);
}

echo $this->ProcessTemplate('adminpanel.tpl');

?>
