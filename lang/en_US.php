<?php
//$lang['accessdenied'] = 'Access denied. Please check your permissions.';
$lang['accessdenied2'] = 'Access denied. You don\'t have %s permission.';
$lang['accessdenied3'] = 'You don\'t have permission.';
$lang['backto_module'] = '&#171; Module Main Page';
$lang['friendlyname'] = 'FAQ';
$lang['fullname'] = 'FAQ Manager module';
$lang['installed'] = 'version %s installed';
$lang['moddescription'] = 'Create and manage questions, answers, and categories for FAQ\'s';
$lang['postinstall'] = 'Be sure to set "... FAQ" permission(s) to use this module!';
$lang['postuninstall'] = 'FAQ Manager uninstalled';
$lang['really_uninstall'] = 'You\'re sure you want to uninstall FAQ Manager?';
$lang['uninstalled'] = 'uninstalled';
$lang['upgraded'] = 'upgraded to version %s';

//NOTE Any ' in these js-parsed strings must be double-escaped
$lang['delgrp_confirm'] = 'Are you sure you want to delete category \\\'%s\\\'';
$lang['delitm_confirm'] = 'Are you sure you want to delete this question:\n %s';
$lang['delselgrp_confirm'] = 'Are you sure you want to delete selected categories?';
$lang['delselitm_confirm'] = 'Are you sure you want to delete selected questions?';

$lang['activate']		= 'Activate';
$lang['activateselitm']	= 'toggle activation of selected questions';
$lang['active']			= 'Active';
$lang['addcategory']	= 'Add new category';
$lang['additem']		= 'Add new question';
//$lang['allusers']	= 'Everyone Authorised';
$lang['answer']			= 'Answer';
$lang['apply']			= 'Apply';
$lang['cancel']			= 'Cancel';
$lang['catdefault']		= 'Uncategorised';
$lang['categories']		= 'Categories';
$lang['category']		= 'Category';
$lang['changed']		= 'Changed';
$lang['close']			= 'Close';
$lang['created']		= 'Created';
$lang['delete']			= 'Delete';
$lang['deleteitem']		= 'delete question';
$lang['deletecategory']	= 'delete category';
$lang['deleteselgrp']	= 'delete selected categories';
$lang['deleteselitm']	= 'delete selected questions';
$lang['down']			= 'move down';
$lang['edititem']		= 'edit question';
//$lang['error']			= 'Error';
$lang['error_server']	= 'Server Communication Error';
$lang['export_filename'] = '%s-Export-%s.csv';
$lang['export']			= 'Export';
$lang['exportselgrp']	= 'export selected categories';
$lang['exportselitm']	= 'export selected questions';
$lang['false']			= 'no';
$lang['order_number']	= 'Display Order';
$lang['item']			= 'Question';
$lang['items']			= 'Questions';
$lang['label_answerer'] = 'Answered by';
$lang['label_category'] = 'Category'; //see also ['category']
$lang['label_id']		= 'ID';
$lang['label_long_answer']	= 'Longform answer';
$lang['label_long_question']= 'Longform question';
$lang['label_nextquestion']	= 'Next';
$lang['label_order']	= 'Order';
$lang['label_prevquestion']	= 'Previous';
$lang['label_short_answer']	= 'Shortform answer';
$lang['label_short_question'] = 'Shortform question';
$lang['label_usage']	= 'displayed if not blank/empty';
$lang['last_modified']	= 'Last modified';
$lang['long_answer']	= 'Long answer';
$lang['long_question']	= 'Long question';
$lang['no']				= 'No';
$lang['nocategories']	= 'No category is recorded.';
$lang['none']			= 'None';
$lang['noitems']		= 'No question is recorded.';
$lang['noowner']		= 'missing owner name';
$lang['option_clear_cat'] = 'Delete questions in a category when the category itself is deleted';
$lang['option_ignore_click'] = 'Ignore frontend clicks on categories and questions e.g. if jquery is handling those';
$lang['option_short_answer'] = 'Display short-form answer if available, in preference to long-form';
$lang['option_short_question'] = 'Display short-form question if available, in preference to long-form';
$lang['option_use_jquery'] = 'Apply jquery to toggle frontend display of questions and answers upon click';
$lang['option_user_cats'] = 'Enable user-specific categories';
$lang['owner']			= 'Owner';
$lang['perm_add']		= 'Add FAQ';
$lang['perm_admin']		= 'Change FAQ Settings';
$lang['perm_delete']	= 'Delete FAQ';
$lang['perm_modify']	= 'Modify FAQ';
$lang['perm_some']		= 'some relevant';
$lang['perm_view']		= 'Inspect FAQ';
$lang['reorder']		= 'Reorder';
$lang['reset']			= 'Reset';
$lang['see']			= 'see';
$lang['settings']		= 'Settings';
$lang['short_answer']	= 'Short answer';
$lang['short_length']	= 'up to 255 chars';
$lang['short_question']	= 'Short question';
$lang['sort']			= 'Sort';
$lang['sortselected']	= 'sort selected categories alphabetically by name';
$lang['sortselitm']		= 'sort selected questions alphabetically by question text';
$lang['submit']			= 'Submit';
$lang['true']			= 'yes';
$lang['up']				= 'move up';
$lang['update']			= 'Update';
$lang['updateselected']	= 'update selected categories';
$lang['viewitem']		= 'view question';
$lang['yes']			= 'Yes';

$lang['help_dnd'] = 'You can change the order by dragging any row, or double-click on any number of rows before dragging them all.';
$lang['help_order_number']	= 'The questions within each category are ordered by this number.<br />You can enter -1 here to place this question first, leave blank to place last.';
$lang['help_use_smarty'] = 'Plugins and smarty variables are valid in the answer';

$lang['help_cat'] = 'Specify a category (by name or id-number), or a \';\'-separated series of categories (names and/or ids), to display';
$lang['help_category'] = 'An alternate name for the \'cat\' parameter';
$lang['help_faq'] = 'Specify the id-number of a particular question to display';
$lang['help_faq_id'] = 'An alternate name for the \'faq\' parameter';
$lang['help_pattern'] = 'Specify a wildcarded pattern which the text of questions to be displayed must match, or, if the first character of the pattern is \'!\', must not match';
$lang['help_regex']	= 'Specify a regular expression which the text of questions to be displayed must match, or, if the first character of the expression is \'!\', must not match';

$lang['help']			= <<<'EOS'
<h3>What Does It Do?</h3>
<p>It manages and displays questions and corresponding answers, individually and/or by category.
Any question and answer may have alternative short-form and long-form versions.
Any category may be assigned to an authorized admin user.</p>
<h3>How Do I Use It?</h3>
<p>In the CMSMS admin Content Menu, you should see a menu item called 'FAQ'.
Click on that. On the displayed page, there are (to the extent that you're suitably authorised)
links and inputs by which you can inspect, add or change any question, or category of questions,
or module setting.</p>
<p>Display wanted content by placing a smarty tag <code>{MBVFaq}</code> in a suitable page or template.</p>
<p>You can apply styling, by using some or all of the following in a relevant stylesheet:
<ul>
<li>.faqanswer{}  /* div containing an answer */</li>
<li>.faqcatblock{}/* div containing an entire category, with all its Q's and A's */</li>
<li>.faqcatlink{} /* span containing category-link */</li>
<li>.faqcatlist{} /* for ordered list of categories */</li>
<li>.faqlink{}	/* span containing question-link */</li>
<li>.faqlist{}	/* for ordered list of questions within a category */</li>
</ul>
or, when displaying a single question,
<ul>
<li>.faqanswer{}  /* div containing answer */</li>
<li>.faqquestion{}/* div containing question text */</li>
<li>.faqtitle	 /* paragraph containing title: "Question" or "Answer" */</li>
</ul>
</p>
<p>You might like to manage the display of questions and answers by applying your own javascript, instead of the built-in approach (which just toggles display of answer).</p>
<h3>Support</h3>
<p>This module is provided as-is. Please read the text of the license for the full disclaimer.</p>
<p>For help:<ul>
<li>discussion may be found in the <a href="http://forum.cmsmadesimple.org">CMS Made Simple Forums</a>; or</li>
<li>you may have some success emailing the author directly.</li>
</ul></p>
<p>For the latest version of the module, or to report a bug, visit the module's <a href="http://dev.cmsmadesimple.org/projects/faqsimple">forge-page</a>.</p>
<h3>Copyright and License</h3>
<p>Copyright &copy; 2011-2016 Tom Phane. All rights reserved.</p>
<p>This module has been released under version 3 of the <a href="http://www.gnu.org/licenses/licenses.html#AGPL">GNU Affero General Public License</a>. You must agree to this license before using the module.</p>
EOS;
