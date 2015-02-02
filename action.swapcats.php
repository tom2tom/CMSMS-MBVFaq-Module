<?php
#----------------------------------------------------------------------
# Module: MBVFaq - a simple FAQ module
# Action: swapcats
# Swap the order-numbers of two categories, specified in $params['item_id'] etc
#----------------------------------------------------------------------
# See file MBVFaq.module.php for full details of copyright, licence, etc.
#----------------------------------------------------------------------

if (!isset($params['category_id']))
	$this->Redirect($id, 'defaultadmin', '', array ('showtab' => 1, 'message'=>'Parameter error')); //TODO error message

$othercat = array();
if (isset($params['next_category_id']))
	$othercat[] = $params['next_category_id'];
elseif (isset($params['prev_category_id']))
	$othercat[] = $params['prev_category_id'];
else
	$this->Redirect($id, 'defaultadmin', '', array ('showtab' => 1));

$sql = "SELECT vieworder FROM $this->CatTable WHERE category_id=?";
$num2 = $db->GetOne($sql,$othercat);
if ($num2 === false)
	$this->Redirect($id, 'defaultadmin', '', array ('showtab' => 1, 'message'=>'Parameter error')); //TODO error message

$thiscat = array($params['category_id']);
$num1 = $db->GetOne($sql,$thiscat);
if ($num1 === false)
	$this->Redirect($id, 'defaultadmin', '', array ('showtab' => 1, 'message'=>'Parameter error')); //TODO error message

array_unshift($thiscat,$num2);
array_unshift($othercat,$num1);

$sql = "UPDATE $this->CatTable SET vieworder=? WHERE category_id=?";
$db->Execute($sql,$thiscat);
$db->Execute($sql,$othercat);

$this->Redirect($id, 'defaultadmin','',array ('showtab' => 1));

?>
