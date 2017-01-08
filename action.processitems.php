<?php
#----------------------------------------------------------------------
# Module: MBVFaq - a simple FAQ module
# Action: processitems
#----------------------------------------------------------------------
# See file MBVFaq.module.php for full details of copyright, licence, etc.
#----------------------------------------------------------------------

if (isset($params['cancel'])) {
	$this->Redirect($id, 'defaultadmin');
}

if (!isset($params['selitems'])) {
	exit;
}

if (isset($params['delete'])) {
	foreach ($params['selitems'] as $item_id) {
		$this->_DeleteItem($item_id);
	}
	$this->Redirect($id, 'defaultadmin');
} elseif (isset($params['activate'])) {
	$qm = array();
	foreach ($params['selitems'] as $k=>$item_id) {
		$params['selitems'][$k] = (int)$item_id;
		$qm[] = '?';
	}
	$seps = implode(',', $qm);
	$sql = "SELECT COUNT(item_id) AS num FROM $this->ItemTable WHERE item_id IN ($seps) AND active = FALSE";
	$inact = $db->GetOne($sql, $params['selitems']);
	if ($inact !== FALSE && (int)$inact == 0) {
		$sql = "UPDATE $this->ItemTable SET active=FALSE WHERE item_id IN ($seps)";
	} else {
		$sql = "UPDATE $this->ItemTable SET active=TRUE WHERE item_id IN ($seps)";
	}
	$db->Execute($sql, $params['selitems']);
	$this->Redirect($id, 'defaultadmin');
} elseif (isset($params['sort'])) {
	if (count($params['selitems']) > 1) {
		$funcs = new MBVForder();
		$funcs->SortItems($this, $params['selitems']);
	}
	$this->Redirect($id, 'defaultadmin');
} elseif (isset($params['export'])) {
	$data = '';
	$funcs = new MBVFcsv();
	foreach ($params['selitems'] as $item_id) {
		$data .= $funcs->ListQuestion($this, $item_id);
	}
	$funcs->Save($this, $data);
	exit;
}
