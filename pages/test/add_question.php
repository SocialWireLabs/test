<?php
	
gatekeeper();
if (is_callable('group_gatekeeper')) 
   group_gatekeeper();
	
$testpost = get_input('testpost');
$test = get_entity($testpost);
$question_type = get_input('question_type');
if (empty($question_type))
   $question_type = "simple";

$container_guid = $test->container_guid;
$container = get_entity($container_guid);

elgg_set_page_owner_guid($container_guid);

elgg_push_breadcrumb($test->title, $test->getURL());

if ($test && $test->canEdit()){
   $title = elgg_echo('test:addquestionpost');
   $content = elgg_view("forms/test/add_question", array('entity' => $test, 'question_type' => $question_type));
} 

$body = elgg_view_layout('content', array('filter' => '','content' => $content,'title' => $title));
echo elgg_view_page($title, $body);
		
?>