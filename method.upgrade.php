<?php
#----------------------------------------------------------------------
# Module: MBVFaq - a simple FAQ module
# Method: upgrade
#----------------------------------------------------------------------
# See file MBVFaq.module.php for full details of copyright, licence, etc.
#----------------------------------------------------------------------

switch($oldversion)
{
case "0.1.0":
	//convert and add db fields
	$dict = NewDataDictionary($db);
	$fields = "
category_id I(4) KEY,
number I(6),
owner I(4) NOTNULL DEFAULT 0
";
	$sqlarray = $dict->AlterColumnSQL($this->CatTable,$fields);
	$dict->ExecuteSQLArray($sqlarray, false);

	$fields = "
item_id I(6) KEY,
category_id I(4),
owner I(4),
create_date ".CMS_ADODB_DT.",
last_modified_date ".CMS_ADODB_DT.",
active L NOTNULL DEFAULT 0,
number I(6)
";
	$sqlarray = $dict->AlterColumnSQL($this->ItemTable,$fields);
	$dict->ExecuteSQLArray($sqlarray, false);

	//add new preferences
	$this->SetPreference('mbvf_clear_category', false);
	$this->SetPreference('mbvf_user_categories',false);
	$this->SetPreference('mbvf_short_answer', true);
	$this->SetPreference('mbvf_short_question', true);
	$this->SetPreference('mbvf_use_jquery', true);
	$this->SetPreference('mbvf_ignore_click', true);

	//add new permissions
	$this->CreatePermission($this->PermAddName, $this->Lang('perm_add'));
	$this->CreatePermission($this->PermModName, $this->Lang('perm_modify'));
	$this->CreatePermission($this->PermDelName, $this->Lang('perm_delete'));
	$this->CreatePermission($this->PermSeeName, $this->Lang('perm_view'));
case "0.3.0":
	//remove files now renamed
	$files = glob(cms_join_path (dirname(__FILE__),'lib','MBVF*.php'));
	foreach ($files as $file)
	{
		if(is_file($file)) unlink($file);
	}
	$files = glob(cms_join_path (dirname(__FILE__),'templates','mbvfaq*.tpl'));
	foreach ($files as $file)
	{
		if(is_file($file)) unlink($file);
	}
	//remove files mistakenly in 0.3.0 .xml release
	$files = array('MBVFaq.prj','MBVFaq.pws','.tm_project.cache');
	foreach ($files as $name)
	{
		$file = cms_join_path (dirname(__FILE__), $name);
		if(is_file($file)) unlink($file);
	}
case "0.4.0":
case "0.4.1":
case "0.4.2":
	//rename db fields
	if (!isset($dict))
		$dict = NewDataDictionary($db);
	$fields = "number I(6)"; //field type needed only for MySQL
	$sqlarray = $dict->RenameColumnSQL($this->CatTable,'number','vieworder', $fields);
	$dict->ExecuteSQLArray($sqlarray, false);
	$sqlarray = $dict->RenameColumnSQL($this->ItemTable,'number','vieworder', $fields);
	$dict->ExecuteSQLArray($sqlarray, false);
	$fields = "question C(255)";
	$sqlarray = $dict->RenameColumnSQL($this->ItemTable,'question','short_question', $fields);
	$dict->ExecuteSQLArray($sqlarray, false);
	//rename preferences
	$a = $this->GetPreference('mbvf_clear_category', false);
	$b = $this->GetPreference('mbvf_user_categories', false);
	$c = $this->GetPreference('mbvf_short_answer', true);
	$d = $this->GetPreference('mbvf_short_question', true);
	$e = $this->GetPreference('mbvf_use_jquery', true);
	$f = $this->GetPreference('mbvf_ignore_click', true);
	$this->RemovePreference();
	$this->SetPreference('clear_category', $a);
	$this->SetPreference('owned_categories', $b);
	$this->SetPreference('short_answer', $c);
	$this->SetPreference('short_question', $d);
	$this->SetPreference('use_jquery', $e);
	$this->SetPreference('ignore_click', $f);
}
// put mention into the admin log
$this->Audit(0, $this->Lang('fullname'), $this->Lang('upgraded',$newversion));

?>
