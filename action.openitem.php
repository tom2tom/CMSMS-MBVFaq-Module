<?php
#----------------------------------------------------------------------
# Module: MBVFaq - a simple FAQ module
# Action: openitem
# Open the specified question ($params['item_id']) in a edit/view page
# Also used for adding a question
#----------------------------------------------------------------------
# See file MBVFaq.module.php for full details of copyright, licence, etc.
#----------------------------------------------------------------------

$item_id = (isset($params['item_id'])?$params['item_id']:'-1');

$funcs = new MBVFshared();
// get the item with the passed-in id, or an empty one if that id not found
$item = $funcs->GetItem($this, $item_id, false);

$pmod = $this->_CheckAccess('admin') || $this->_CheckAccess('modify')
	|| ($item_id == -1 && $this->_CheckAccess('add'));
$sq = $this->GetPreference('short_question', true);
$sa = $this->GetPreference('short_answer', true);
// setup variables for the window
$smarty->assign('mod', $pmod);

$smarty->assign('backtomod_nav', $this->CreateLink($id, 'defaultadmin', '', $this->Lang('backto_module'), array()));

$smarty->assign('startform', $this->CreateFormStart($id, 'updateitem', $returnid));
$smarty->assign('endform', $this->CreateFormEnd());

$p = $this->Lang('label_short_question').' ('.$this->Lang('short_length');
if ($sq) $p .= ', '.$this->Lang('label_usage');
$p .= ')';
$smarty->assign('short_question_text', $p);
$p = $this->Lang('label_long_question');
if (!$sq) $p .= ' ('.$this->Lang('label_usage').')';
$smarty->assign('long_question_text', $p);

$smarty->assign('category_text', $this->Lang('category'));
$smarty->assign('order_number_text', $this->Lang('order_number'));
$smarty->assign('help_order_number', $this->Lang('help_order_number'));
$smarty->assign('active_text', $this->Lang('active'));

$p = $this->Lang('label_short_answer').' ('.$this->Lang('short_length');
if ($sa) $p .= ', '.$this->Lang('label_usage');
$p .= ')';
$smarty->assign('short_answer_text', $p);
$p = $this->Lang('label_long_answer');
if (!$sa) $p .= ' ('.$this->Lang('label_usage').')';
$smarty->assign('long_answer_text', $p);

if ($pmod)
{
	$all = !$this->GetPreference('owned_categories', false);
	if (!$all) $all = $this->_CheckAccess('admin'); //admin permission removes limit on category ownership
	$categories = array();
	// we only need the owner-specific category names and id's, so extract those
	foreach($funcs->GetCategories($this,0,0,false,$all) as $category)
	{
		if ($category->name != '')
			$categories[$category->name] = $category->category_id;
	}
	$smarty->assign('input_category', $this->CreateInputDropdown($id, 'category', $categories, -1, $item->category_id));
	$smarty->assign('input_order_number', $this->CreateInputText($id, 'order', $item->order, 3, 5));
	$smarty->assign('input_short_question', $this->CreateTextArea(false,$id,$item->question,'short_question', '', '', '', '', '80', '5'));
	$smarty->assign('input_long_question', $this->CreateTextArea(false,$id,$item->long_question, 'long_question', '', '', '', '', '80', '7'));
	$smarty->assign('input_short_answer', $this->CreateTextArea(true, $id, $item->short_answer, 'short_answer', '', '', '', '', '80', '5'));
	$smarty->assign('input_long_answer', $this->CreateTextArea(true, $id, $item->long_answer, 'long_answer', '', '', '', '', '80', '15'));

	$smarty->assign('help_use_smarty',$this->Lang('help_use_smarty'));
	$smarty->assign('input_active', $this->CreateInputCheckbox($id, 'active', '1', $item->active, 'class="pagecheckbox"'));
	$smarty->assign('create_date', $item->create_date);
	$smarty->assign('hidden', $this->CreateInputHidden($id, 'item_id', $item_id).
		$this->CreateInputHidden($id, 'create_date', $item->create_date));
	$smarty->assign('submit', $this->CreateInputSubmit($id, 'submit', $this->Lang('submit')));
	$smarty->assign('cancel', $this->CreateInputSubmit($id, 'cancel', $this->Lang('cancel')));
}
else
{
	$smarty->assign('input_category', $item->category);
	$smarty->assign('input_order_number', $item->order);
	$smarty->assign('input_short_question', $item->question);
	$smarty->assign('input_long_question', $item->long_question);
	$cleartypes = array('p');
	$smarty->assign('input_short_answer', $funcs->StripTags($item->short_answer,$cleartypes));
	$smarty->assign('input_long_answer', $funcs->StripTags($item->long_answer,$cleartypes));
	$p = ($item->active) ? $this->Lang('yes'):$this->Lang('no');
	$smarty->assign('input_active', $p);
	$smarty->assign('create_date', $item->create_date);
	$smarty->assign('cancel', $this->CreateInputSubmit($id, 'cancel', $this->Lang('close')));
}

if ($item->create_date)
	$smarty->assign('create_date_text', $this->Lang('created'));

echo $this->ProcessTemplate('editfaq.tpl');

?>
