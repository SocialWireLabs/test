<?php
	
gatekeeper();
if (is_callable('group_gatekeeper')) 
   group_gatekeeper();
	
$testpost = get_input('testpost');
$test = get_entity($testpost);
$questionsbankpost = get_input('questionsbankpost');
$tags = get_input('tags');
$question_types = get_input('question_types');
$response_types = get_input('response_types');
$questions_selection_type = get_input('questions_selection_type');
$num_questions_import = get_input('num_questions_import');

$container_guid = $test->container_guid;
$container = get_entity($container_guid);

elgg_set_page_owner_guid($container_guid);

elgg_push_breadcrumb($test->title, $test->getURL());

if ($test && $test->canEdit()){
   $title = elgg_echo('test:importquestionsbankpost');
   $content = elgg_view("forms/test/select_questions_questionsbank", array('entity' => $test, 'questionsbankpost' => $questionsbankpost, 'tags' => $tags, 'question_types' => $question_types, 'response_types' => $response_types,'questions_selection_type' => $questions_selection_type, 'num_questions_import' => $num_questions_import));
} 

$body = elgg_view_layout('content', array('filter' => '','content' => $content,'title' => $title));
echo elgg_view_page($title, $body);
		
?>