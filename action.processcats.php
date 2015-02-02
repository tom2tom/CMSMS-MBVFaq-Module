<?php
#----------------------------------------------------------------------
# Module: MBVFaq - a simple FAQ module
# Action: updateitem
# Modify or delete or export selected categories
#----------------------------------------------------------------------
# See file MBVFaq.module.php for full details of copyright, licence, etc.
#----------------------------------------------------------------------

if (isset($params['cancel'])) // if cancel was pressed don't do anything
	$this->Redirect($id, 'defaultadmin');
if (!isset($params['selgrps']))
	$this->Redirect($id, 'defaultadmin');

if (isset($params['update']))//update selected categories
	$tasktype = 1;
elseif (isset($params['delete']))//delete selected categories
	$tasktype = 2;
elseif (isset($params['sort'])) //sort selected categories
{
	$funcs = new MBVForder();
	$funcs->SortCategories($this, $selected);
	$this->Redirect($id, 'defaultadmin', '', array('showtab' => 1));
}
elseif (isset($params['export']))//export selected categories
{
	$tasktype = 3;
	$data = '';
	$csvfuncs = new MBVFcsv();
}
else
	$this->Redirect($id, 'defaultadmin');

$names = $params['category_names'];
if (isset($params['owner_ids']))
	$owners = $params['owner_ids'];
else
	$owners = null;
/* $params['selgrps'] array contains the category_ids of rows with
selected checkboxes, but with keys that are no use here, so we get
another array with keys matching those in $params['category_ids'] */
$selected = array_intersect ($params['category_ids'], $params['selgrps']);

foreach ($selected as $k => $category_id)
{
	$category_name = $names[$k];
	if ($category_name != '')
	{
//deprecated PHP $category_name = $db->quote($category_name, get_magic_quotes_runtime());
		switch ($tasktype)
		{
		case 1: //update
			if($category_id > -1)
			{
				if ($owners != null)
				{
					$sql = "UPDATE $this->CatTable SET name=?, owner=? WHERE category_id=?";
					$db->Execute($sql, array($category_name,$owners[$k],$category_id));
				}
				else
				{
					$sql = "UPDATE $this->CatTable SET name=? WHERE category_id=?";
					$db->Execute($sql, array($category_name,$category_id));
				}
			}
			else //new category
			{
				if ($owners != null)
				{
					$sql = "INSERT INTO $this->CatTable (category_id, name, vieworder, owner) VALUES (?,?,?,?)";
					$category_id = $db->GenID($this->CatTable.'_seq');
					$db->Execute($sql, array($category_id,$category_name,$category_id,$owners[$k]));
				}
				else
				{
					$sql = "INSERT INTO $this->CatTable (category_id, name, vieworder) VALUES (?,?,?)";
					$category_id = $db->GenID($this->CatTable.'_seq');
					$db->Execute($sql, array($category_id,$category_name,$category_id));
				}
			}
			break;
		case 2: //delete
			$this->_DeleteCategory($category_id);
			break;
		case 3: //export
			$data .= $csvfuncs->ListCategory($this, $category_id);
			break;
		}
	}
}

if ($tasktype != 3)
	$this->Redirect($id, 'defaultadmin', '', array('showtab' => 1));

$csvfuncs->Save($this, $data);

?>
