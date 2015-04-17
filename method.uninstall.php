<?php
#----------------------------------------------------------------------
# Module: MBVFaq - a simple FAQ module
# Method: uninstall
#----------------------------------------------------------------------
# See file MBVFaq.module.php for full details of copyright, licence, etc.
#----------------------------------------------------------------------

// remove database tables
$dict = NewDataDictionary($db);
$sqlarray = $dict->DropTableSQL($this->CatTable);
$dict->ExecuteSQLArray($sqlarray);
$sqlarray = $dict->DropTableSQL($this->ItemTable);
$dict->ExecuteSQLArray($sqlarray);
// remove sequences
$db->DropSequence($this->CatTable.'_seq');
$db->DropSequence($this->ItemTable.'_seq');
// remove permissions
$this->RemovePermission($this->PermAdminName);
$this->RemovePermission($this->PermAddName);
$this->RemovePermission($this->PermModName);
$this->RemovePermission($this->PermDelName);
$this->RemovePermission($this->PermSeeName);
// remove all preferences
$this->RemovePreference();

?>
