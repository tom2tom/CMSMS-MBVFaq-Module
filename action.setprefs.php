<?php
#----------------------------------------------------------------------
# Module: MBVFaq - a simple FAQ module
# Action: setprefs
#----------------------------------------------------------------------
# See file MBVFaq.module.php for full details of copyright, licence, etc.
#----------------------------------------------------------------------

if (isset($params['cancel'])) // if cancel was pressed don't do update
	$this->Redirect($id, 'defaultadmin');

$state = isset($params['mbvf_clear_category']);
$this->SetPreference('clear_category',$state);
$state = isset($params['mbvf_ignore_click']);
$this->SetPreference('ignore_click',$state);
$state = isset($params['mbvf_short_answer']);
$this->SetPreference('short_answer',$state);
$state = isset($params['mbvf_short_question']);
$this->SetPreference('short_question',$state);
$state = isset($params['mbvf_use_jquery']);
$this->SetPreference('use_jquery',$state);
$state = isset($params['mbvf_owned_categories']);
$this->SetPreference('owned_categories',$state);
// if you want a comprehensive audit log, write something, here

$this->Redirect($id, 'defaultadmin', '', array('showtab' => 2));

?>
