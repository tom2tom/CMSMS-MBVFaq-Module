<?php
#----------------------------------------------------------------------
# Module: MBVFaq - a simple FAQ module
# Action: swapitems
# Swap the order-numbers of two questions, specified in $params['item_id'] etc
#----------------------------------------------------------------------
# See file MBVFaq.module.php for full details of copyright, licence, etc.
#----------------------------------------------------------------------

if (!isset($params['item_id'])) {
	$this->Redirect($id, 'defaultadmin', '', array('message'=>'Parameter error'));
} //TODO error message

$otheritem = array();
if (isset($params['next_item_id'])) {
	$otheritem[] = $params['next_item_id'];
} elseif (isset($params['prev_item_id'])) {
	$otheritem[] = $params['prev_item_id'];
} else {
	$this->Redirect($id, 'defaultadmin');
}

$sql = "SELECT vieworder FROM $this->ItemTable WHERE item_id=?";
$num2 = $db->GetOne($sql, $otheritem);
if ($num2 === FALSE) {
	$this->Redirect($id, 'defaultadmin', '', array('message'=>'Parameter error'));
} //TODO error message

$thisitem = array($params['item_id']);
$num1 = $db->GetOne($sql, $thisitem);
if ($num1 === FALSE) {
	$this->Redirect($id, 'defaultadmin', '', array('message'=>'Parameter error'));
} //TODO error message

array_unshift($thisitem, $num2);
array_unshift($otheritem, $num1);

$sql = "UPDATE $this->ItemTable SET vieworder=? WHERE item_id=?";
$db->Execute($sql, $thisitem);
$db->Execute($sql, $otheritem);

$this->Redirect($id, 'defaultadmin');
