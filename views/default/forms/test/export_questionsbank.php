<?php
	
$testpost = $vars['entity']->getGUID();
$action = "test/export_questionsbank";
$user_guid = elgg_get_logged_in_user_guid();
$index = $vars['index'];
	
$questionsbanks = elgg_get_entities(array('type' => 'object', 'subtype' => 'questionsbank', 'limit' => false, 'owner_guid' => $user_guid));
$questionsbanks_titles = array();
foreach ($questionsbanks as $one_questionsbank){
   $questionsbanks_titles[$one_questionsbank->getGUID()] = $one_questionsbank->title; 
}

?>
<div class="contentWrapper">
<form action="<?php echo elgg_get_site_url(); ?>action/<?php echo $action; ?>" name="import_questionsbank_test" enctype="multipart/form-data" method="post">
<?php echo elgg_view('input/securitytoken'); ?>

<p> 
<b><?php echo elgg_echo('test:questionsbank_label'); ?> </b>
</p>
<p>
<?php echo elgg_view('input/dropdown',array('name'=>'questionsbankpost','options_values'=>$questionsbanks_titles)); ?>
</p>

<?php echo "<input type=\"hidden\" name=\"testpost\" value=\"$testpost\" />"; ?>

<?php echo "<input type=\"hidden\" name=\"index\" value=\"$index\" />"; ?>

<p>
<?php 
$submit_input = elgg_view('input/submit', array('name' => 'submit', 'value' => elgg_echo("test:export")));
echo $submit_input
?>
</p>

</form>
</div>



