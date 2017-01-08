<?php
#----------------------------------------------------------------------
# Module: MBVFaq - a simple FAQ module
# Action: movecategory
# Re-order categories after DnD
#----------------------------------------------------------------------
# See file MBVFaq.module.php for full details of copyright, licence, etc.
#----------------------------------------------------------------------

if (!($this->_CheckAccess('modify') || $this->_CheckAccess('admin'))) {
	exit;
}

/* $params[] includes
  'droporder' => multi(at least 3)-part string e.g. '2,3,6,1' id's of displayed
	 categories (1st)before, at, and (last)after the dropped one(s). First or
	 last may be 'NULL' indicating drop at start or end of table
*/

$catsdrop = explode(',', $params['droporder']);
if ($catsdrop[0] == 'NULL' && $catsdrop[2] == 'NULL') {
	exit;
}	//a single row, nothing to do

$cats = $db->GetCol("SELECT category_id FROM ".$this->CatTable." ORDER BY vieworder ASC");
if ($cats == FALSE || count($cats) == 0) {
	exit;
}	//nothing to do

//extract the ones actually dropped
$dodrops = array_splice($catsdrop, 1, -1);
//walk the categories, incrementing order
$cnum = 1;
$sql = "UPDATE $this->CatTable SET vieworder=? WHERE category_id=?";
foreach ($cats as $cid) {
	if ($cid == $catsdrop[0]) { //the one before the dropper(s)
		$db->Execute($sql, array($cnum, $cid));
		$cnum++;
		foreach ($dodrops as $id) {
			$db->Execute($sql, array($cnum, $id));
			$cnum++;
		}
	} elseif ($cid == $catsdrop[1] && $catsdrop[0] == 'NULL') { //the one after the dropper(s)
		foreach ($dodrops as $id) {
			$db->Execute($sql, array($cnum, $id));
			$cnum++;
		}
		$db->Execute($sql, array($cnum, $cid));
		$cnum++;
	} elseif (!in_array($cid, $dodrops)) {
		$db->Execute($sql, array($cnum, $cid));
		$cnum++;
	}
}
//re-create & echo contents of questions-table body
$funcs = new MBVFajax();
$funcs->CreateQuestionsBody($id, $returnid, $this, $smarty);

exit;
