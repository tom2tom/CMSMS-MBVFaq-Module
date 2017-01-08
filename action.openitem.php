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
$item = $funcs->GetItem($this, $item_id, FALSE);

$pmod = $this->_CheckAccess('admin') || $this->_CheckAccess('modify')
	|| ($item_id == -1 && $this->_CheckAccess('add'));
$sq = $this->GetPreference('short_question', TRUE);
$sa = $this->GetPreference('short_answer', TRUE);
// setup variables for the window
$tplvars = array(
	'mod' =>  $pmod,

	'backtomod_nav' =>  $this->CreateLink($id, 'defaultadmin', '', $this->Lang('backto_module'), array()),
	'startform' =>  $this->CreateFormStart($id, 'updateitem', $returnid),
	'endform' =>  $this->CreateFormEnd()
);

$p = $this->Lang('label_short_question').' ('.$this->Lang('short_length');
if ($sq) {
	$p .= ', '.$this->Lang('label_usage');
}
$p .= ')';
$tplvars['short_question_text'] = $p;
$p = $this->Lang('label_long_question');
if (!$sq) {
	$p .= ' ('.$this->Lang('label_usage').')';
}
$tplvars['long_question_text'] = $p;

$tplvars += array(
	'category_text' => $this->Lang('category'),
	'order_number_text' => $this->Lang('order_number'),
	'help_order_number' => $this->Lang('help_order_number'),
	'active_text' => $this->Lang('active')
);

$p = $this->Lang('label_short_answer').' ('.$this->Lang('short_length');
if ($sa) {
	$p .= ', '.$this->Lang('label_usage');
}
$p .= ')';
$tplvars['short_answer_text'] = $p;
$p = $this->Lang('label_long_answer');
if (!$sa) {
	$p .= ' ('.$this->Lang('label_usage').')';
}
$tplvars['long_answer_text'] = $p;

if ($pmod) {
	$all = !$this->GetPreference('owned_categories', FALSE);
	if (!$all) {
		$all = $this->_CheckAccess('admin');
	} //admin permission removes limit on category ownership
	$categories = array();
	// we only need the owner-specific category names and id's, so extract those
	foreach ($funcs->GetCategories($this, 0, 0, FALSE, $all) as $category) {
		if ($category->name != '') {
			$categories[$category->name] = $category->category_id;
		}
	}
	$tplvars += array(
		'input_category' => $this->CreateInputDropdown($id, 'category', $categories, -1, $item->category_id),
		'input_order_number' => $this->CreateInputText($id, 'order', $item->order, 3, 5),
		'input_short_question' => $this->CreateTextArea(FALSE, $id, $item->question, 'short_question', '', '', '', '', '80', '5'),
		'input_long_question' => $this->CreateTextArea(FALSE, $id, $item->long_question, 'long_question', '', '', '', '', '80', '7'),
		'input_short_answer' => $this->CreateTextArea(TRUE, $id, $item->short_answer, 'short_answer', '', '', '', '', '80', '5'),
		'input_long_answer' => $this->CreateTextArea(TRUE, $id, $item->long_answer, 'long_answer', '', '', '', '', '80', '15'),

		'help_use_smarty' => $this->Lang('help_use_smarty'),
		'input_active' => $this->CreateInputCheckbox($id, 'active', '1', $item->active, 'class="pagecheckbox"'),
		'create_date' => $item->create_date,
		'hidden' => $this->CreateInputHidden($id, 'item_id', $item_id).
			$this->CreateInputHidden($id, 'create_date', $item->create_date),
		'submit' => $this->CreateInputSubmit($id, 'submit', $this->Lang('submit')),
		'cancel' => $this->CreateInputSubmit($id, 'cancel', $this->Lang('cancel'))
	);
} else {
	$tplvars += array(
		'input_category' => $item->category,
		'input_order_number' => $item->order,
		'input_short_question' => $item->question,
		'input_long_question' => $item->long_question
	);
	$cleartypes = array('p');
	$tplvars['input_short_answer'] = $funcs->StripTags($item->short_answer, $cleartypes);
	$tplvars['input_long_answer'] = $funcs->StripTags($item->long_answer, $cleartypes);
	$p = ($item->active) ? $this->Lang('yes'):$this->Lang('no');
	$tplvars += array(
		'input_active' => $p,
		'create_date' => $item->create_date,
		'cancel' => $this->CreateInputSubmit($id, 'cancel', $this->Lang('close'))
	);
}

if ($item->create_date) {
	$tplvars['create_date_text'] = $this->Lang('created');
}

$funcs->ProcessTemplate($this, 'editfaq.tpl', $tplvars);
