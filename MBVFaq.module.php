<?php
#------------------------------------------------------------------------
# Module: MBVFaq - a simple FAQ handling module for CMS Made Simple
# Mostly copyright (C) 2011-2012 Tom Phane <@>
# Derived from beta release, copyright (C) 2005 Martin B. Vestergaard (mbvdk) <mbv@nospam.dk>
# Version: 0.5.0
# This project's forge-page is: http://dev.cmsmadesimple.org/projects/faqsimple
#
# This module is free software; you can redistribute it and/or modify it under
# the terms of the GNU General Public License as published by the Free Software
# Foundation; either version 3 of the License, or (at your option) any later
# version.
#
# This module is distributed in the hope that it will be useful, but
# WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License (www.gnu.org/licenses/licenses.html#GPL)
# for more details
#-----------------------------------------------------------------------

/**
Class declaration
*/
class MBVFaq extends CMSModule
{
	public $dbHandle;
	public $CatTable;
	public $ItemTable;
	public $UserTable;
	protected $PermAdminName = 'MBVFaq Admin';
	protected $PermAddName = 'MBVFaq Add';
	protected $PermModName = 'MBVFaq Modify';
	protected $PermDelName = 'MBVFaq Delete';
	protected $PermSeeName = 'MBVFaq View';
	public $before111;

	function __construct()
	{
		parent::__construct();

		$this->RegisterModulePlugin();

		$this->dbHandle = cmsms()->GetDb();
		$pre = cms_db_prefix();
		$this->CatTable = $pre.'module_MBVFaq_category';
		$this->ItemTable = $pre.'module_MBVFaq_question';
		$this->UserTable = $pre.'users';

		global $CMS_VERSION;
		$this->before111 = (version_compare ($CMS_VERSION, '1.11') < 0);
	}

	function GetName()
	{
		return 'MBVFaq';
	}

	function GetFriendlyName()
	{
		return $this->Lang('friendlyname');
	}

	function GetVersion()
	{
		return '1.0';
	}

	function MinimumCMSVersion()
	{
		return '1.9';
	}

	function MaximumCMSVersion()
	{
		return '1.19.99';
	}

	function GetHelp()
	{
		return $this->Lang('help');
	}

	function GetAuthor()
	{
		return 'tomphantoo';
	}

	function GetAuthorEmail()
	{
		return 'tpgww@onepost.net';
	}

	function GetChangeLog()
	{
		$fn = cms_join_path(dirname(__FILE__),'include','changelog.inc');
		return @file_get_contents($fn);
	}

	function IsPluginModule()
	{
		return true;
	}

	function HasAdmin()
	{
		return true;
	}

	/**
	LazyLoadAdmin:
	For 1.10+
	*/
	function LazyLoadAdmin()
	{
		return false; //need immediately, for admin menu
	}

	function GetAdminSection()
	{
		return 'content';
	}

	function GetAdminDescription()
	{
		return $this->Lang('moddescription');
	}

	function AdminStyle()
	{
		$fn = cms_join_path(dirname(__FILE__),'css','admin.css');
		$output = ''.@file_get_contents($fn);
		return $output;
	}

	function VisibleToAdminUser()
	{
        return $this->_CheckAccess();
	}

	function GetHeaderHTML()
	{
		return '<link rel="stylesheet" type="text/css" href="'.$this->GetModuleURLPath().'/css/admin.css" />';
	}

	function SuppressAdminOutput(&$request)
	{
		//prevent output of general admin content when doing an export,
		//and when updating the database via an ajax call
		if (isset($request['mact']))
		{
			if (strpos($request['mact'], 'moveitem', 6)) return true;
			if (strpos($request['mact'], 'movecategory', 6)) return true;
			if (strpos($request['mact'], 'export', 6)) return true;
		}
		if (isset($request['m1_export'])) return true;
		return false;
	}

	function GetDependencies()
	{
		return array();
	}

	/**
	AllowSmartyCaching:
	For 1.11+
	*/
	function AllowSmartyCaching()
	{
		return true;
	}

	/**
	LazyLoadFrontend:
	For 1.10+
	*/
	function LazyLoadFrontend()
	{
		return false; //needed to support route-registration
	}

	function InstallPostMessage()
	{
		return $this->Lang('postinstall');
	}

	function UninstallPreMessage()
	{
		return $this->Lang('really_uninstall');
	}

	function UninstallPostMessage()
	{
		return $this->Lang('postuninstall');
	}

	/**
	SetParameters:
	For pre-1.10
	*/
	function SetParameters()
	{
		$this->InitializeAdmin();
		$this->InitializeFrontend();
	}

	/**
	InitializeFrontend:
	Partial setup for 1.10
	*/
	function InitializeFrontend()
	{
		$this->RestrictUnknownParams();
		$this->SetParameterType('cat',CLEAN_STRING);
		$this->SetParameterType('category',CLEAN_STRING);
		$this->SetParameterType('faq',CLEAN_INT);
		$this->SetParameterType('faq_id',CLEAN_INT);
		$this->SetParameterType('pattern',CLEAN_STRING);
		$this->SetParameterType('regex',CLEAN_STRING);

		/* register 'routes' to use for pretty url parsing
		these regexes translate url-parameter(s) to $param[](s) be supplied
		to the specified actions (default calls ->DisplayModuleOutput())
		so the routes need to conform to parameter-usage in handler-func(s)
		(?P<name>regex) captures the text matched by "regex" into the group "name",
		which can contain letters and numbers but must start with a letter.
		*/
		// for showing the contents of a specific category
		$this->RegisterRoute('/[mM][bB][vV][fF]aq\/cat(egory)?(?P<cat>.*?)\/(?P<returnid>[0-9]+)$/',array('action'=>'default'));
		// for showing all the details for a specific question
		$this->RegisterRoute('/[mM][bB][vV][fF]aq\/faq(_id)?(?P<faq>[0-9]+)\/(?P<returnid>[0-9]+)$/',array('action'=>'default'));
		// for doing nothing i.e. ignored links
		$this->RegisterRoute('/[mM][bB][vV][fF]aq\/(?P<returnid>[0-9]+)$/',array('action'=>'default'));
	}

	/**
	InitializeAdmin:
	Partial setup for 1.10
	*/
	function InitializeAdmin()
	{
		$this->CreateParameter('cat','',$this->Lang('help_cat'));
		$this->CreateParameter('category','',$this->Lang('help_category'));
		$this->CreateParameter('faq','',$this->Lang('help_faq'));
		$this->CreateParameter('faq_id','',$this->Lang('help_faq_id'));
		$this->CreateParameter('pattern','',$this->Lang('help_pattern'));
		$this->CreateParameter('regex','',$this->Lang('help_regex'));
	}

	/*
	DoAction:
	No permission-checks are done here or in related action files, as capabilities
	are governed by which actionable widgets are displayed
	- and those are permission-checked before creation
	*/
	function DoAction($action, $id, $params, $returnid=-1)
	{
		switch ($action)
		{
		case 'default':
		case 'defaultadmin':
		case 'movecategory': //process reorder by DnD
		case 'swapcats':
		case 'processcats': //update, delete, sort, export cats
		case 'openitem': //initiate an edit or add
		case 'updateitem': //submit item changes
		case 'moveitem': //process reorder by DnD
		case 'swapitems':
		case 'processitems': //export, sort, activate, delete Q's
		case 'setprefs':
			break;
		case 'deleteitem':
			if (isset($params['item_id']) && ($params['item_id'] > -1))
				$this->_DeleteItem($params['item_id']); //trivial, several uses, don't bother with separate action file
			$action = 'defaultadmin';
			$params = array();
			break;
		case 'toggleitem': //[de]activate
			$this->_ActivateItem($id, $params, $returnid); //trivial func, don't bother with separate action file
			$action = 'defaultadmin';
			$params = array();
			break;
		case 'addcategory':
			$action = 'defaultadmin';
			$params = array('showtab' => 1,'extracat' => true);
			break;
		case 'deletecategory':
			if (isset($params['category_id']) && ($params['category_id'] > 0))
				$this->_DeleteCategory($params['category_id']); //several uses, don't bother with separate action file
			$action = 'defaultadmin';
			$params = array('showtab' => 1);
			break;
		default:
			return;
		}
		parent::DoAction($action,$id,$params,$returnid);
	}

	/**
	_CheckAccess:
	@permission: string specifying what to check, default=''
	@warn: whether to show a warning message if permission not valid, default=false
		NOT PART OF THE MODULE API
	*/
	function _CheckAccess($permission='',$warn=false)
	{
		switch ($permission)
		{
		case '': //anything relevant
			$name = '';
			$ok = $this->CheckPermission($this->PermSeeName);
			if (!$ok) $ok = $this->CheckPermission($this->PermAddName);
			if (!$ok) $ok = $this->CheckPermission($this->PermDelName);
			if (!$ok) $ok = $this->CheckPermission($this->PermModName);
			if (!$ok) $ok = $this->CheckPermission($this->PermAdminName);
			break;
		case 'admin':
			$name = $this->PermAdminName;
			$ok = $this->CheckPermission($name);
			break;
		case 'add':
			$name = $this->PermAddName;
			$ok = $this->CheckPermission($name);
			break;
		case 'modify':
			$name = $this->PermModName;
			$ok = $this->CheckPermission($name);
			break;
		case 'delete':
			$name = $this->PermDelName;
			$ok = $this->CheckPermission($name);
			break;
		default:
			$name = '';
			$ok = false;
		}
		if (!$ok && $warn)
		{
			if ($name == '') $name = $this->Lang('perm_some');
			echo '<p class="error">'.$this->Lang('accessdenied2',$name).'</p>';
		}
		return $ok;
	}

	/**
	_DeleteCategory:
	@category_id: 
	Delete a category, either with all its questions, or after setting all its
	questions to category 0
	Confirmation upstream, not here
	*/
	function _DeleteCategory($category_id)
	{
		if ($category_id > 0) //no deleting the default category
		{
			$db = $this->dbHandle;
			$all = $this->GetPreference('clear_category', false);
			if ($all) // first delete the contents
				$sql = "DELETE FROM $this->ItemTable WHERE category_id=?";
			else // first set the category_id of the affected contents to 0
				$sql = "UPDATE $this->ItemTable SET category_id=0 WHERE category_id=?";
			$db->Execute($sql,array($category_id));
			$sql = "DELETE FROM $this->CatTable WHERE category_id=?";
			$db->Execute($sql,array($category_id));
		}
	}

	/**
	_ActivateItem:
	@id:
	@params:
	@returnid:
	[de]activate the item passed in @params
	*/
	function _ActivateItem($id, &$params, $returnid)
	{
		if (isset($params['item_id']))
		{
			$querydata = array();
			if (isset($params['active']))
			{
				if ($params['active'])
					$querydata[] = 0;
				else
					$querydata[] = 1;
			}
			else
			{
				$querydata[] = 0;
			}
			$querydata[] = $params['item_id'];

			$sql = "UPDATE $this->ItemTable SET active=? WHERE item_id=?";
	    	$this->dbHandle->Execute($sql, $querydata);
		}
	}

	/**
	_DeleteItem:
	@$item_id:
	Delete from the db the question passed in @item_id
	*/
	function _DeleteItem($item_id)
	{
		$sql = "DELETE FROM $this->ItemTable WHERE item_id=?";
		$this->dbHandle->Execute($sql, array($item_id));
	}

	/**
	 ellipsize:
	 @str: string to ellipsize
	 @max_length: max length of @str
	 @position: int (1|0) or float, .5, .2, etc for position to split Default 1
	 @ellipsis: string for ellipsis Default '...'
	 Split @str at its max_length and ellipsize
	 Returns: ellipsized string
	 */
	function ellipsize($str, $max_length, $position = 1, $ellipsis = '&hellip;')
	{
		$str = trim($str);
		// Is the string long enough to ellipsize?
		if (strlen($str) <= $max_length)
			return $str;

		if ($position > 1) $position = 1;
		else if ($position < 0) $position = 0;
		$beg = substr($str, 0, floor($max_length * $position));

		if ($position === 1)
			$end = substr($str, 0, -($max_length - strlen($beg)));
		else
			$end = substr($str, -($max_length - strlen($beg)));

		return $beg.$ellipsis.$end;
	}

}

?>
