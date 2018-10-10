<?php

gatekeeper();

$testpost = get_input('guid');
$test = get_entity($testpost);
	
if ($test->getSubtype() == "test" && $test->canEdit()) {

   $container_guid = $test->container_guid;
   $container = get_entity($container_guid);
   $owner = get_entity($test->getOwnerGUID());
   $owner_guid = $owner->getGUID();

   //Delete questions (and files)
   $options = array('relationship' => 'test_question', 'relationship_guid' => $testpost,'inverse_relationship' => false, 'type' => 'object', 'subtype' => 'test_question','limit'=>0);
   $questions=elgg_get_entities_from_relationship($options);
 
   foreach ($questions as $one_question){
      $files = elgg_get_entities_from_relationship(array('relationship' => 'question_file_link','relationship_guid' => $one_question->getGUID(),'inverse_relationship' => false,'type' => 'object','limit'=>0));
      foreach($files as $one_file){
         $deleted=$one_file->delete();
	 if (!$deleted){
	    register_error(elgg_echo("test:filenotdeleted"));
	    forward(elgg_get_site_url() . 'test/group/' . $container_guid);
	 }
      }
      $deleted=$one_question->delete();
      if (!$deleted){
         register_error(elgg_echo("test:questionnotdeleted"));
	 forward(elgg_get_site_url() . 'test/group/' . $container_guid);
      }
   }
   //Delete answers
   $options = array('relationship' => 'test_answer', 'relationship_guid' => $testpost,'inverse_relationship' => false, 'type' => 'object', 'subtype' => 'test_answer','limit'=>0);
   $users_responses=elgg_get_entities_from_relationship($options);

   foreach($users_responses as $one_response){
      if (strcmp($test->type_grading,'test_type_grading_game_points')){
         $access = elgg_set_ignore_access(true);
         $game_points = gamepoints_get_entity($one_response->getGUID());
         if ($game_points) {
            $deleted=$game_points->delete();
            if (!$deleted){
               register_error(elgg_echo("test:gamepointsnotdeleted"));
	       forward(elgg_get_site_url() . 'test/group/' . $container_guid);
            }
         }  
	 elgg_set_ignore_access($access);   
      }
      $deleted=$one_response->delete();
      if (!$deleted){
         register_error(elgg_echo("test:answernotdeleted"));
	 forward(elgg_get_site_url() . 'test/group/' . $container_guid);
      }
   }
   //Delete answers draft
   $options = array('relationship' => 'test_answer_draft', 'relationship_guid' => $testpost,'inverse_relationship' => false, 'type' => 'object', 'subtype' => 'test_answer_draft','limit'=>0);
   $users_responses_draft=elgg_get_entities_from_relationship($options);
  
   foreach($users_responses_draft as $one_response_draft){
      $deleted=$one_response_draft->delete();
      if (!$deleted){
         register_error(elgg_echo("test:answernotdeleted"));
	 forward(elgg_get_site_url() . 'test/group/' . $container_guid);
      }
   }

   // Delete the event created with the test (if event_manager plugin)
   if (elgg_is_active_plugin('event_manager')){
      $event_guid=$test->event_guid;
      if ($event=get_entity($event_guid)){
         $deleted=$event->delete();
         if (!$deleted){
            register_error(elgg_echo("test:eventmanagernotdeleted"));
            forward(elgg_get_site_url() . 'test/group/' . $container_guid);
         }
      }
   }

   if (strcmp($test->type_grading,'test_type_grading_marks')){
      $access = elgg_set_ignore_access(true);
      $marks = socialwire_marks_get_marks(null,null,$testpost);
      foreach ($marks as $mark) {
         $deleted=$mark->delete();
         if (!$deleted){
            register_error(elgg_echo("test:marknotdeleted"));
            forward(elgg_get_site_url() . 'test/group/' . $container_guid);
         }
      }   
      elgg_set_ignore_access($access); 
   } 

   // Delete it!
   $deleted = $test->delete();
   if ($deleted > 0) {
      system_message(elgg_echo("test:deleted"));
   } else {
      register_error(elgg_echo("test:notdeleted"));
   }
   forward(elgg_get_site_url() . 'test/group/' . $container_guid);
}
	
?>