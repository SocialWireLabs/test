<?php
	
gatekeeper();
if (is_callable('group_gatekeeper')) 
   group_gatekeeper();
	
$testpost = get_input('testpost');
$test = get_entity($testpost);
$index = get_input('index');

$container_guid = $test->container_guid;
$container = get_entity($container_guid);

elgg_set_page_owner_guid($container_guid);

elgg_push_breadcrumb($test->title, $test->getURL());

if ($test && $test->canEdit()){
   $title = elgg_echo('test:editquestionpost');
   $content = elgg_view("forms/test/edit_question", array('entity' => $test, 'index' => $index));
} 

$body = elgg_view_layout('content', array('filter' => '','content' => $content,'title' => $title));
echo elgg_view_page($title, $body);
		
?>