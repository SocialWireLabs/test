<?php

gatekeeper();

// Get input data
$testpost = get_input('testpost');
$index = get_input('index');	

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
  	   
   if (($test->random_questions)&&($test->num_random_questions<($num_questions-1))){
      $num_questions = $test->num_random_questions;
   } else {
      $num_questions = count($questions) - 1;
   }

   $already_deleted=false;
   foreach ($questions as $one_question){
      if ($one_question->index==$index){
         if(!$already_deleted){
            $already_deleted=true;
            $files = elgg_get_entities_from_relationship(array('relationship' => 'question_file_link','relationship_guid' => $one_question->getGUID(),'inverse_relationship' => false,'type' => 'object','limit'=>0));
	    foreach($files as $one_file){
	       $deleted=$one_file->delete();
	       if (!$deleted){
	          register_error(elgg_echo("test:filenotdeleted"));
	          forward("test/edit/$testpost");
	       }
            }	      
	    $deleted=$one_question->delete();
	    if (!$deleted){
	       register_error(elgg_echo("test:questionnotdeleted"));
               forward("test/edit/$testpost");
            }
	 }
      } else {

         if (strcmp($test->type_grading,'test_type_grading_marks')==0){
	    $one_question->grading = ($test->max_mark*1.0)/$num_questions;
         } else {
	    $one_question->grading = $test->question_max_game_points/$num_questions;
	 }
	 $previous_index = $one_question->index;
         if ($previous_index>$index){
	    $one_question->index = $previous_index-1;
         }
      }
   }

   // System message        
   system_message(elgg_echo("test:updated"));	   
   
   // Forward
   forward("test/view/$testpost");	
}
?>