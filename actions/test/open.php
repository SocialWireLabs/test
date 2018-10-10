<?php

gatekeeper();

$testpost = get_input('testpost');
$test = get_entity($testpost);

if ($test->getSubtype() == "test" && $test->canEdit()) {
   
   $options = array('relationship' => 'test_question', 'relationship_guid' => $testpost,'inverse_relationship' => false, 'type' => 'object', 'subtype' => 'test_question','limit'=>0);
   $questions=elgg_get_entities_from_relationship($options);
   if (empty($questions)) {
      $num_questions = 0;
   } else {
      $num_questions = count($questions);
   }	

   //Not questions
   if ($num_questions==0){
      register_error(elgg_echo("test:not_questions"));
       forward($_SERVER['HTTP_REFERER']);
   }

   $test->option_activate_value = 'test_activate_now';
   $test->opened = true;
   $test->action = true;

   //Event using the event_manager plugin if it is active
   if (elgg_is_active_plugin('event_manager') && strcmp($test->option_close_value,'test_not_close')!=0){

      $event_guid = $test->event_guid;
      if (!($event=get_entity($event_guid))){
         $event = new Event();
      } 

      $event->title = sprintf(elgg_echo("test:event_manager_title"),$test->title);
      $event->description = $test->input_question_html;
      $event->container_guid = $test->container_guid;
      $event->access_id = $test->access_id;
      $event->save();
      $event->tags = string_to_tag_array($tags);
      $event->comments_on = 0;
      $event->registration_ended = 1;
      $event->show_attendees = 0;
      $event->max_attendees = "";
      $event->start_day = $test->close_date;
      $event->start_time = $test->close_time;
      $event->end_ts = $test->close_time +1;
      $event->organizer = elgg_get_logged_in_user_entity()->getDisplayName();
      $event->setAccessToOwningObjects($access_id);

      // Save it, if it is new
      if (!get_entity($event_guid)){         
         if ($event->save()){
            $event_guid = $event->getGUID();
            $test->event_guid = $event_guid;
         }
         else
            register_error(elgg_echo("test:event_manager_error_save"));
      }
   }

   //System message 
   system_message(elgg_echo("test:opened_listing"));
   //Forward
   forward($_SERVER['HTTP_REFERER']);
}
		
?>
