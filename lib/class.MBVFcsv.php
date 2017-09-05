<?php
#----------------------------------------------------------------------
# Module: MBVFaq - a simple FAQ module
# Library file: csv
#----------------------------------------------------------------------
# See file MBVFaq.module.php for full details of copyright, licence, etc.
#----------------------------------------------------------------------

class MBVFcsv
{
	/**
	_ListTitlesCSV(&$mod)
	Strip commas from all headings, then join them
	*/
	private function _ListTitlesCSV(&$mod)
	{
		$s = ',';
		$r = '&#44;';
		$titles =   ''.str_replace($s, $r, $mod->Lang('category'));
		$titles .= ','.str_replace($s, $r, $mod->Lang('short_question'));
		$titles .= ','.str_replace($s, $r, $mod->Lang('long_question'));
		$titles .= ','.str_replace($s, $r, $mod->Lang('short_answer'));
		$titles .= ','.str_replace($s, $r, $mod->Lang('long_answer'));
		$titles .= ','.str_replace($s, $r, $mod->Lang('owner'));
		$titles .= ','.str_replace($s, $r, $mod->Lang('created'));
		$titles .= ','.str_replace($s, $r, $mod->Lang('last_modified'));
		$titles .= ','.str_replace($s, $r, $mod->Lang('active'))."\n";
		return $titles;
	}

	/**
	Save(&$mod, &$data)
	*/
	public function Save(&$mod, &$data)
	{
		$config = cmsms()->GetConfig();
		$charset = $config['default_encoding'];
		$sname = preg_replace('/\W/', '_', $mod->GetFriendlyName());
		$fn = $mod->Lang('export_filename', $sname, date('Y-m-d'));
		$content = self::_ListTitlesCSV($mod).$data;
		$len = strlen($content);
		$IE = (strstr($_SERVER['HTTP_USER_AGENT'], 'MSIE')!=FALSE);
		ob_clean();
		ob_start();
//		echo $content; //WHY here also?
		ob_end_flush();
		if ($IE) {
			header('Content-type: application/force-download');
		} else {
			header('Content-Type: text/csv; charset='.$charset);
		} //CSV standard, believe it or not !?
		header('Content-Disposition: attachment; filename="'.$fn.'"');
		header('Content-Description: File Transfer');
		header('Content-Transfer-Encoding: binary');
		header('Content-Length: '.$len);
		header('Expires: 0');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
	//	header('Cache-Control: private',FALSE);
		header('Pragma: public');
		if (!$IE) {
			header('Cache-Control: no-cache');
			header('Pragma: no-cache');
		}
	/*	@ob_clean();
		header('Pragma: public');
		header('Expires: 0');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Cache-Control: private',FALSE);
		header('Content-Description: File Transfer');
		header('Content-Type: text/csv; charset=utf-8'); // CSV standard, believe it or not !?
		header('Content-Length: ' . strlen($content));
		header('Content-Disposition: attachment; filename=' . $fn);
	*/
		echo $content;
	}

	public function ListQuestion(&$mod, $item_id)
	{
		$outstr = '';
		$sql = "SELECT I.*, C.name, U.first_name, U.last_name FROM $mod->ItemTable I
LEFT JOIN $mod->CatTable C ON I.category_id = C.category_id
LEFT JOIN $mod->UserTable U ON I.owner = U.user_id
WHERE I.item_id=? ORDER BY I.vieworder ASC";
		$rst = $mod->dbHandle->Execute($sql, array($item_id));
		if ($rst && !$rst->EOF) {	//writing .csv file, so strip commas from all content
			$s = ',';
			$r = '&#44;';
			$row = $rst->FetchRow();
			$outstr .= str_replace($s, $r, $row['name']);
			$outstr .= ','.str_replace($s, $r, $row['short_question']);
			$outstr .= ','.str_replace($s, $r, $row['long_question']);
			$outstr .= ','.str_replace($s, $r, $row['short_answer']);
			$outstr .= ','.str_replace($s, $r, $row['long_answer']);
			$nm = trim($row['first_name'].' '.$row['last_name']);
			if ($nm == '') {
				$nm = '<'.$this->Lang('noowner').'>';
			}
			$outstr .= ','.str_replace($s, $r, $nm);
			$outstr .= ','.str_replace($s, $r, $row['create_date']);
			$outstr .= ','.str_replace($s, $r, $row['last_modified_date']);
			$outstr .= ($row['active'] > 0) ? ',true':',false';
			$outstr .= "\n"; //TODO conform newline to browser platform

			$rst->Close();
		}
		return $outstr;
	}

	public function ListCategory(&$mod, $category_id)
	{
		$outstr = '';
		$sql = "SELECT I.*, C.name, U.first_name, U.last_name FROM $mod->ItemTable I
LEFT JOIN $mod->CatTable C ON I.category_id = C.category_id
LEFT JOIN $mod->UserTable U ON I.owner = U.user_id
WHERE I.category_id=? ORDER BY I.vieworder ASC";
		$rst = $mod->dbHandle->Execute($sql, array($category_id));
		if ($rst && !$rst->EOF) {
			// the db-query was successful
			//writing .csv file, so strip commas from all content
			$s = ',';
			$r = '&#44;';
			while ($row = $rst->FetchRow()) {
				$outstr .= str_replace($s, $r, $row['name']);
				$outstr .= ','.str_replace($s, $r, $row['short_question']);
				$outstr .= ','.str_replace($s, $r, $row['long_question']);
				$outstr .= ','.str_replace($s, $r, $row['short_answer']);
				$outstr .= ','.str_replace($s, $r, $row['long_answer']);
				$nm = trim($row['first_name'].' '.$row['last_name']);
				if ($nm == '') {
					$nm = '<'.$this->Lang('noowner').'>';
				}
				$outstr .= ','.str_replace($s, $r, $nm);
				$outstr .= ','.str_replace($s, $r, $row['create_date']);
				$outstr .= ','.str_replace($s, $r, $row['last_modified_date']);
				$outstr .= ($row['active'] > 0) ? ',true':',false';
				$outstr .= "\r\n"; //TODO conform to browser platform
			}
			$rst->Close();
		}
		return $outstr;
	}
}
