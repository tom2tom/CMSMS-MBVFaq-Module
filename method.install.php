<?php
#----------------------------------------------------------------------
# Module: MBVFaq - a simple FAQ module
# Method: install
#----------------------------------------------------------------------
# See file MBVFaq.module.php for full details of copyright, licence, etc.
#----------------------------------------------------------------------

$taboptarray = array('mysql' => 'ENGINE MyISAM CHARACTER SET utf8 COLLATE utf8_general_ci',
 'mysqli' => 'ENGINE MyISAM CHARACTER SET utf8 COLLATE utf8_general_ci');
$dict = NewDataDictionary($db);
/*
 questions table schema:
 'vieworder' is used for display-order, since 'order' is a reserved SQL word
 'owner' is uid of the answerer of the question
*/
$fields = "
	item_id I(6) KEY,
	category_id I(4),
	short_question C(255),
	long_question X,
	short_answer C(255),
	long_answer X,
	create_date ".CMS_ADODB_DT.",
	last_modified_date ".CMS_ADODB_DT.",
	active L NOTNULL DEFAULT 0,
	vieworder I(6),
	owner I(4)
";
$sqlarray = $dict->CreateTableSQL($this->ItemTable, $fields, $taboptarray);
if ($sqlarray == false) return false;
$res = $dict->ExecuteSQLArray($sqlarray, false);
if ($res != 2) return false;
// create a sequence
$db->CreateSequence($this->ItemTable.'_seq');
/*
 categories table schema:
 'owner' is the uid of the category's assigned owner, or 0 if there's no such assignment
*/
$fields = "
	category_id I(4) KEY,
	name C(255),
	vieworder I(6),
	owner I(4) NOTNULL DEFAULT 0
";
$sqlarray = $dict->CreateTableSQL($this->CatTable, $fields, $taboptarray);
if ($sqlarray == false) return false;
$res = $dict->ExecuteSQLArray($sqlarray, false);
if ($res != 2) return false;
// create a sequence
$db->CreateSequence($this->CatTable.'_seq');
// add a default category 0, usable by everyone
$sql = "INSERT INTO $this->CatTable (category_id, name, vieworder) VALUES (0,?,0)";
$db->Execute($sql,array($this->Lang('catdefault')));

// create permissions
$this->CreatePermission($this->PermAdminName, $this->Lang('perm_admin'));
$this->CreatePermission($this->PermAddName, $this->Lang('perm_add'));
$this->CreatePermission($this->PermModName, $this->Lang('perm_modify'));
$this->CreatePermission($this->PermDelName, $this->Lang('perm_delete'));
$this->CreatePermission($this->PermSeeName, $this->Lang('perm_view'));

// create preferences
$this->SetPreference('clear_category', false);	//delete questions in category when category is deleted (admin)
$this->SetPreference('owned_categories',false);	//enable user-specific categories
$this->SetPreference('short_answer', true);		//front-end display short answer if it exists, in preference to long form
$this->SetPreference('short_question', true);	//front-end display short question if it exists, in preference to long form
$this->SetPreference('use_jquery',true);		//add custom jquery code to frontend page, for handling clicks
$this->SetPreference('ignore_click', true);		//don't process front-end link-clicks (e.g. when using js to process them)

// put mention into the admin log
$this->Audit(0, $this->Lang('fullname'), $this->Lang('installed',$this->GetVersion()));

?>
