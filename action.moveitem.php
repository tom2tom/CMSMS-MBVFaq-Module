<?php
#----------------------------------------------------------------------
# Module: MBVFaq - a simple FAQ module
# Action: moveitem
# Re-order questions after DnD
#----------------------------------------------------------------------
# See file MBVFaq.module.php for full details of copyright, licence, etc.
#----------------------------------------------------------------------

if (!($this->_CheckAccess('modify') || $this->_CheckAccess('admin'))) {
	exit;
}

/* $params[] includes
  'droporder' => multi(at least 3)-part string e.g. '2,3,6,1' id's of displayed
	 questions (1st)before, at, and (last)after the dropped one(s). First or
	 last may be 'null' indicating drop at start or end of table
*/
$itemsdrop = explode(',', $params['droporder']);
if ($itemsdrop[0] == 'null' && $itemsdrop[2] == 'null') {
	exit;
}	//must be a single row, nothing to do

//extract the ones actually dropped
$dodrops = array_splice($itemsdrop, 1, -1);

$getters = $dodrops;
$first = ($itemsdrop[0] == 'null');
if (!$first) {
	array_unshift($getters, $itemsdrop[0]);
}
$last = ($itemsdrop[1] == 'null');
if (!$last) {
	$getters[] = $itemsdrop[1];
}
$count = count($getters);

$sql = "SELECT item_id,category_id,vieworder FROM $this->ItemTable
WHERE item_id IN (".str_repeat('?,', $count-1)."?)";
$rows = $db->GetAssoc($sql, $getters);
if ($rows == FALSE || count($rows) < $count) {
	exit;
}

//determine category(ies) that need re-ordering
$reorders = array();
foreach ($getters as $cid) {
	$reorders[$rows[$cid]['category_id']] = 1;
}

$funcs = new MBVForder();
//move dropper(s) to target/surrounding category
if ($last) {
	$funcs->ChangeItemCategory($this, $rows[$itemsdrop[0]]['category_id'], $dodrops);
} else { //first or middle
	$funcs->ChangeItemCategory($this, $rows[$itemsdrop[1]]['category_id'], $dodrops);
}

foreach ($reorders as $cid=>$val) {
	if (!$last && $cid == $rows[$itemsdrop[1]]['category_id']) {
		//reorder with $dodrops @ start or before target
		$before = $first ? 1 : $rows[$itemsdrop[1]]['vieworder'];
		$funcs->ReorderRows($this, $cid, $dodrops, $before);
	} elseif ($last && $cid == $rows[$itemsdrop[0]]['category_id']) { //last and !first can't both be TRUE
		//reorder with $dodrops @ end
		$max = $db->GetOne("SELECT MAX(vieworder) AS max FROM ".$this->ItemTable." WHERE category_id=".$cid);
		$funcs->ReorderRows($this, $cid, $dodrops, -1, intval($max));
	} else {
		//just reorder
		$funcs->ReorderRows($this, $cid);
	}
}

//re-create & echo contents of questions-table body
$funcs = new MBVFajax();
$funcs->CreateQuestionsBody($id, $returnid, $this, $smarty);

exit;
