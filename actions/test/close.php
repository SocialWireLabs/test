<?php

gatekeeper();

$testpost = get_input('testpost');
$test = get_entity($testpost);
$edit = get_input('edit');

if ($test->getSubtype() == "test" && $test->canEdit()) {

   $test->option_close_value = 'test_not_close';   
   $test->opened = false;
   $test->action = true;

   //Delete answers draft
   $container_guid = $test->container_guid;
   $options = array('relationship' => 'test_answer_draft', 'relationship_guid' => $testpost,'inverse_relationship' => false, 'type' => 'object', 'subtype' => 'test_answer_draft','limit'=>0);
   $users_responses_draft=elgg_get_entities_from_relationship($options);
  
   foreach($users_responses_draft as $one_response_draft){
      $deleted=$one_response_draft->delete();
      if (!$deleted){
         register_error(elgg_echo("test:answernotdeleted"));
         forward(elgg_get_site_url() . 'test/group/' . $container_guid);
      }
   }

   //Delete event
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

   //System message 
   system_message(elgg_echo("test:closed_listing"));
   //Forward
   if (strcmp($edit,'no')==0) {
      forward($_SERVER['HTTP_REFERER']);
   } else {
      forward("test/edit/$testpost");
   }
}
		
?>
