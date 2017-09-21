<?php
#----------------------------------------------------------------------
# Module: MBVFaq - a simple FAQ module
# Library file: order
#----------------------------------------------------------------------
# See file MBVFaq.module.php for full details of copyright, licence, etc.
#----------------------------------------------------------------------

class MBVForder
{
	public function ReorderRows(&$mod, $catid, $dropids = FALSE, $beforenum = -100, $afternum = -100)
	{
		$sql = "SELECT vieworder,item_id FROM $mod->ItemTable WHERE category_id=? ORDER BY vieworder";
		$rows = $mod->dbHandle->GetAssoc($sql, array($catid));
		if ($rows == FALSE || count($rows) == 0) {
			return;
		}
		//walk the category, incrementing order
		$qnum = 1;
		$sql = "UPDATE $mod->ItemTable SET vieworder=? WHERE item_id=?";
		foreach ($rows as $num=>$qid) {
			if ($num == $beforenum) {
				if ($dropids) {
					foreach ($dropids as $id) {
						$mod->dbHandle->Execute($sql, array($qnum, $id));
						++$qnum;
					}
				}
				$mod->dbHandle->Execute($sql, array($qnum, $qid));
				++$qnum;
			} elseif ($num == $afternum) {
				$mod->dbHandle->Execute($sql, array($qnum, $qid));
				++$qnum;
				if ($dropids) {
					foreach ($dropids as $id) {
						$mod->dbHandle->Execute($sql, array($qnum, $id));
						++$qnum;
					}
				}
			} elseif (!$dropids || !in_array($qid, $dropids)) {
				$mod->dbHandle->Execute($sql, array($qnum, $qid));
				++$qnum;
			}
		}
	}

	/*selectedids is array of question ID's like
	  0 => string '6'
	  1 => string '4'
	  2 => string '12'
	  ...
	  whose current positions and sort-orders may be anything, probably non-contiguous
	*/
	public function SortItems(&$mod, &$selectedids)
	{
		$sql = "SELECT item_id,vieworder FROM $mod->ItemTable
WHERE item_id IN (".
		str_repeat('?,', count($selectedids)-1)."?) ORDER BY category_id,short_question,long_question";
		$res = $mod->dbHandle->GetAssoc($sql, $selectedids);
		$orders = array_values($res);
		sort($orders, SORT_NUMERIC);
		$i = 0; //TODO CRAP if not all items are being sorted
		$sql = "UPDATE $mod->ItemTable SET vieworder=? WHERE item_id=?";
		foreach ($res as $id=>&$order) {
			$mod->dbHandle->Execute($sql, array($orders[$i], $id));
			++$i;
		}
		unset($order);
	}

	/*selectedids is array of category ID's like
	  0 => string '6'
	  1 => string '4'
	  2 => string '12'
	  ...
	*/
	public function SortCategories(&$mod, &$selectedids)
	{
		$sql = "SELECT category_id,vieworder FROM $mod->CatTable
WHERE category_id IN (".
		str_repeat('?,', count($selectedids)-1)."?) ORDER BY name,vieworder";
		$res = $mod->dbHandle->GetAssoc($sql, $selectedids);
		$orders = array_map(function ($element) {
			return intval($element[0]);
		}, $res);
		sort($orders, SORT_NUMERIC);
		$i = 0;
		$sql = "UPDATE $mod->CatTable SET vieworder=? WHERE category_id=?";
		foreach ($res as $id=>&$order) {
			$mod->dbHandle->Execute($sql, array($orders[$i], $id));
			++$i;
		}
		unset($order);
	}

	public function ChangeItemCategory(&$mod, $tocid, &$dropids)
	{
		$sql = "UPDATE $mod->ItemTable SET category_id=?,vieworder=-1 WHERE item_id IN (".
		str_repeat('?,', count($dropids)-1)."?)";
		$qargs = $dropids;
		array_unshift($qargs, $tocid);
		$mod->dbHandle->Execute($sql, $qargs); //no viable success check after UPDATE
	}
}
