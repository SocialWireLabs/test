<?php
	
gatekeeper();
if (is_callable('group_gatekeeper')) 
   group_gatekeeper();
	
$testpost = get_input('testpost');
$test = get_entity($testpost);
$questionsbankpost = get_input('questionsbankpost');
$questionsbank = get_entity($questionsbankpost);
$index = get_input('index');

$container_guid = $test->container_guid;
$container = get_entity($container_guid);

elgg_set_page_owner_guid($container_guid);

//elgg_push_breadcrumb($test->title, $test->getURL());

if ($questionsbank){
   $title = elgg_echo('test:showquestionpost');
   $content = elgg_view("forms/test/show_question_questionsbank", array('entity' => $test, 'questionsbankpost' => $questionsbankpost, 'index' => $index));
} 

$body = elgg_view_layout('content', array('filter' => '','content' => $content,'title' => $title));
echo elgg_view_page($title, $body);
		
?>