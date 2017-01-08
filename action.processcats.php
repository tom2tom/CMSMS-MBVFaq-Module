<?php
#----------------------------------------------------------------------
# Module: MBVFaq - a simple FAQ module
# Action: updateitem
# Modify or delete or export selected categories
#----------------------------------------------------------------------
# See file MBVFaq.module.php for full details of copyright, licence, etc.
#----------------------------------------------------------------------

if (isset($params['cancel'])) { // if cancel was pressed don't do anything
	$this->Redirect($id, 'defaultadmin');
}
if (!isset($params['selgrps'])) {
	$this->Redirect($id, 'defaultadmin');
}

if (isset($params['update'])) {
	//update selected categories
	$tasktype = 1;
} elseif (isset($params['delete'])) {
	//delete selected categories
	$tasktype = 2;
} elseif (isset($params['sort'])) { //sort selected categories
	$funcs = new MBVForder();
	$funcs->SortCategories($this, $selected);
	$this->Redirect($id, 'defaultadmin', '', array('showtab' => 1));
} elseif (isset($params['export'])) {//export selected categories
	$tasktype = 3;
	$data = '';
	$csvfuncs = new MBVFcsv();
} else {
	$this->Redirect($id, 'defaultadmin');
}

$names = $params['category_names'];
if (isset($params['owner_ids'])) {
	$owners = $params['owner_ids'];
} else {
	$owners = NULL;
}
	
foreach ($params['selgrps'] as $k=>$category_id) {
	$thisname = $names[$k];
	if ($thisname != '') {
		$thisid = (int)$category_id;
//deprecated PHP $thisname = $db->quote($thisname, get_magic_quotes_runtime());
		switch ($tasktype) {
		case 1: //update
			if ($thisid > -1) {
				if ($owners != NULL) {
					$sql = "UPDATE $this->CatTable SET name=?, owner=? WHERE category_id=?";
					$db->Execute($sql, array($thisname, $owners[$k], $thisid));
				} else {
					$sql = "UPDATE $this->CatTable SET name=? WHERE category_id=?";
					$db->Execute($sql, array($thisname, $thisid));
				}
			} else { //new category
				if ($owners != NULL) {
					$sql = "INSERT INTO $this->CatTable (category_id, name, vieworder, owner) VALUES (?,?,?,?)";
					$category_id = $db->GenID($this->CatTable.'_seq');
					$db->Execute($sql, array($thisid, $thisname, $thisid, $owners[$k]));
				} else {
					$sql = "INSERT INTO $this->CatTable (category_id, name, vieworder) VALUES (?,?,?)";
					$category_id = $db->GenID($this->CatTable.'_seq');
					$db->Execute($sql, array($thisid, $thisname, $thisid));
				}
			}
			break;
		case 2: //delete
			$this->_DeleteCategory($thisid);
			break;
		case 3: //export
			$data .= $csvfuncs->ListCategory($this, $thisid);
			break;
		}
	}
}

if ($tasktype != 3) {
	$this->Redirect($id, 'defaultadmin', '', array('showtab' => 1));
}

$csvfuncs->Save($this, $data);
