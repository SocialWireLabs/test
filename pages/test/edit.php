<?php
	
gatekeeper();
if (is_callable('group_gatekeeper')) 
   group_gatekeeper();
	
$testpost = get_input('testpost');
$test = get_entity($testpost);
$user_guid = elgg_get_logged_in_user_guid();
$user = get_entity($user_guid);

$container_guid = $test->container_guid;
$container = get_entity($container_guid);

elgg_set_page_owner_guid($container_guid);

elgg_push_breadcrumb($test->title, $test->getURL());
elgg_push_breadcrumb(elgg_echo('edit'));

if ($test && $test->canEdit()){
   $title = elgg_echo('test:editpost');
   $content = elgg_view("forms/test/edit", array('entity' => $test));
} 

$body = elgg_view_layout('content', array('filter' => '','content' => $content,'title' => $title));
echo elgg_view_page($title, $body);
		
?>