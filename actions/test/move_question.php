<?php

gatekeeper();

// Get input data
$testpost = get_input('testpost');
$index = get_input('index');
$action = get_input('ac');

$user_guid = elgg_get_logged_in_user_guid();

$test = get_entity($testpost);
$container = get_entity($test->container_guid);

if ($test->getSubtype() == "test" && $test->canEdit()) {

   $count_responses=$test->countAnnotations('all_responses');
   $count_responses_draft=$test->countAnnotations('all_responses_draft');
   $count_responses = $count_responses + $count_responses_draft;

   if ($count_responses>0){
      register_error(elgg_echo("test:structure"));
      forward("test/edit/$testpost");
   }

   $options = array('relationship' => 'test_question', 'relationship_guid' => $testpost,'inverse_relationship' => false, 'type' => 'object', 'subtype' => 'test_question','limit'=>0);
   $questions=elgg_get_entities_from_relationship($options);
   $num_questions=count($questions);

   $end=0;
   if (strcmp($action,"up")==0){
      foreach ($questions as $one_question){
         if ($one_question->index==$index){
	    $one_question->index=$index-1;
	    $end=$end+1;
         } else if ($one_question->index==($index-1)){
	    $one_question->index=$index;
	    $end=$end+1;
         }
	 if ($end==2)
	    break;
      }
   }
   if (strcmp($action,"down")==0){
      foreach ($questions as $one_question){
         if ($one_question->index==$index){
            $one_question->index=$index+1;
	    $end=$end+1;
         } else if ($one_question->index==($index+1)){
	    $one_question->index=$index;
            $end=$end+1;
         }
         if ($end==2)
	    break;
      }
   }

   if (strcmp($action,"top")==0){
      foreach ($questions as $one_question){
         if ($one_question->index==$index){
            $one_question->index=0;
         } else {
	    if ($one_question->index < $index)
               $one_question->index = $one_question->index +1;
         }
      }
   }

   if (strcmp($action,"bottom")==0){
      foreach ($questions as $one_question){
         if ($one_question->index==$index){
            $one_question->index=$num_questions-1;
         } else {
	    if ($one_question->index > $index)
               $one_question->index=$one_question->index -1;
         }
      }
   }

   // System message
   system_message(elgg_echo("test:updated"));

   //Forward
   forward("test/view/$testpost");
}

?>
