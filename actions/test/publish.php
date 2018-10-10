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


   //Set times
   $now=time();
   $test->save_action = false;
   if ((strcmp($test->option_activate_value ,'test_activate_date')==0)&&(strcmp($test->option_close_value,'test_close_date')==0)){
      if (($now>=$test->activate_time)&&($now<$test->close_time)){
         $test->opened=true;
      } else {
         $test->opened=false;
      }
   } elseif (strcmp($test->option_activate_value,'test_activate_date')==0){
      if ($now>=$test->$activate_time) {
         $test->opened=true;
      } else {
         $test->opened=false;
      }
   } elseif (strcmp($test->option_close_value,'test_close_date')==0){
      if ($now<$test->$close_time) {
         $test->opened=true;
      } else {
         $test->opened=false;
      } 
   } else {
      $test->opened=true;
   }

   $test->created = true;

   $user_guid = elgg_get_logged_in_user_guid();
   $container_guid = $test->container_guid;
   $user = get_entity($user_guid);
   $container = get_entity($container_guid);

   // Add to river
   elgg_create_river_item(array(
            'view'=>'river/object/test/update',
            'action_type'=>'update',
            'subject_guid'=>$user_guid,
            'object_guid'=>$testpost,
         ));

   //Nofity 
   if ($test->access_id!=0) {
      $username = $user->name;
      $site_guid = elgg_get_config('site_guid');
      $site = get_entity($site_guid);
      $sitename = $site->name;
      $group = $container;
      $groupname = $container->name;
      $link = $test->getURL();
      $subject = sprintf(elgg_echo('test:create:group:email:subject'),$username,$sitename,$groupname);
      $group_members = $group->getMembers(array('limit'=>false));
      foreach ($group_members as $member){
         $member_guid = $member->getGUID();
         if ($member_guid != $test->owner_guid){
            $body = sprintf(elgg_echo('test:create:group:email:body'),$member->name,$username,$sitename,$groupname,$title,$link);
            notify_user($member_guid,$test->owner_guid,$subject,$body, array('action'=>'create', 'object'=>$test));
         }
      }
   }

   //Event using the event_manager plugin if it is active
   if (elgg_is_active_plugin('event_manager') && strcmp($test->option_close_value,'test_not_close')!=0){

      $event = new Event();
      $event->title = sprintf(elgg_echo("test:event_manager_title"),$test->title);
      $event->description = $test->input_question_html;
      $event->container_guid = $test->container_guid;
      $event->access_id = $test->access_id;
      $event->save();
      $event->tags = string_to_tag_array($test->tags);         
      $event->comments_on = 0;
      $event->registration_ended = 1;
      $event->show_attendees = 0;
      $event->max_attendees = "";
      $event->start_day = $test->close_date;
      $event->start_time = $test->close_time;
      $event->end_ts = $test->close_time+1;
      $event->organizer = $user->getDisplayName();
      $event->setAccessToOwningObjects($test->access_id);

      // added because we need an update event
      if ($event->save()){
         $event_guid = $event->getGUID();
         $test->event_guid = $event_guid;
      }
      else
         register_error(elgg_echo("test:event_manager_error_save"));
   }

   //System message 
   system_message(elgg_echo("test:created"));

   //Forward
   forward($_SERVER['HTTP_REFERER']);
}
		
?>
