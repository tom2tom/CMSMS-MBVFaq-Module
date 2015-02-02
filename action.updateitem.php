<?php
#----------------------------------------------------------------------
# Module: MBVFaq - a simple FAQ module
# Action: updateitem
#----------------------------------------------------------------------
# See file MBVFaq.module.php for full details of copyright, licence, etc.
#----------------------------------------------------------------------

if (isset($params['cancel'])) // if cancel was pressed don't do update
	$this->Redirect($id, 'defaultadmin');

$querydata = array();
if (isset($params['category']) && $params['category'] != '')
	$category = $params['category'];
else
	$category = 0; //default (first) category
$querydata[] = $category;

if (isset($params['short_question']))
	$querydata[] = $params['short_question'];
else
	$querydata[] = '';	//here we need something, no question >> no post!

if (isset($params['long_question']))
	$querydata[] = $params['long_question'];
else
	$querydata[] = '';

if (isset($params['short_answer']))
	$querydata[] = $params['short_answer'];
else
	$querydata[] = ''; // ok as long as there is a long answer
	//if not, it won't be shown whether it's active or not

if (isset($params['long_answer']))
	$querydata[] = $params['long_answer'];
else
	$querydata[] = ''; // ok as long as there is a short answer,
				//if not, it won't be shown whether it is active or not

if (isset($params['owner']) && $params['owner'] !='')
	$querydata[] = $params['owner'];
else
	$querydata[] = get_userid(false); //set current user as owner

$now = date("Y-m-d H:i:s");
if (isset($params['create_date']) && $params['create_date'] != '')
	$querydata[] = date('Y-m-d H:i:s', strtotime($params['create_date']));
else
	$querydata[] = $now;

$querydata[] = $now; //modified now

if (isset($params['active']) && $params['active'] !='')
	$querydata[] = $params['active'];
else
	$querydata[] = 0; // default is inactive

$item_id = $params['item_id'];
if ($item_id == '-1')
{
	$add = true;
	$item_id = $db->GenID($this->ItemTable.'_seq');
	$oldcategory = false;
}
else
{
	$add = false;
	$sql = "SELECT category_id FROM $this->ItemTable WHERE item_id=?";
	$oldcategory = $db->GetOne($sql,array($item_id));
}

if (!empty($params['vieworder']))
	$order = $params['vieworder'];
else
	$order = 9999999999; //PHP_INT_MAX - 10000; default to last in category

//check if there's already another in the category and with the specified order-number
$sql = "SELECT item_id FROM $this->ItemTable WHERE category_id=? AND vieworder=?";
$check = $db->GetOne($sql,array($category,$order));
if($check !== false && $check != $item_id)
{
	$before = true;	//need insert-before
	$querydata[] = -1; //order will be updated afterwards
}
else
{
	$before = false;
	$querydata[] = $order;
}

$querydata[] = $item_id;

if ($add) //it's a new post
	$sql = "INSERT INTO $this->ItemTable (category_id, short_question, long_question, short_answer, long_answer, owner, create_date, last_modified_date, active, vieworder, item_id) VALUES(?,?,?,?,?,?,?,?,?,?,?)";
else
	$sql = "UPDATE $this->ItemTable SET category_id=?, short_question=?, long_question=?, short_answer=?, long_answer=?, owner=?, create_date=?, last_modified_date=?, active=?, vieworder=? WHERE item_id=?";

$db->Execute($sql,$querydata);

$funcs = new MBVForder();
//re-sequence affected vieworder fields
if ($before)
	$funcs->ReorderRows($this, $category, array($item_id), $order);
else
	$funcs->ReorderRows($this, $category);
if (!($oldcategory === false || $oldcategory == $category))
	$funcs->ReorderRows($this, $oldcategory);

$this->Redirect($id, 'defaultadmin');

?>
