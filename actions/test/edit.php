<?php

gatekeeper();

$testpost = get_input('testpost');
$test = get_entity($testpost);
$close_test = get_input('close_test');
   
if (strcmp($close_test,"yes")==0){
   $test->option_close_value = 'test_not_close';
   $test->opened=false;	
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
   forward("test/edit/$testpost");  
} 

if ($test->getSubtype() == "test" && $test->canEdit()) {
     
   $user_guid = elgg_get_logged_in_user_guid();
   $user = get_entity($user_guid);

   $title = get_input('title');
   $description = get_input('description');
   $option_activate_value = get_input('option_activate_value');
   $option_close_value = get_input('option_close_value');
   if (strcmp($option_activate_value,'test_activate_date')==0){
      $opendate = get_input('opendate');
      $opentime = get_input('opentime');
   }
   if (strcmp($option_close_value,'test_close_date')==0){
      $closedate = get_input('closedate');
      $closetime = get_input('closetime');
   }

   $assessable = get_input('assessable');
   $feedback = get_input('feedback');
   $tags = get_input('testtags');
   $access_id = get_input('access_id');
   $selected_action = get_input('submit');

   $container_guid = $test->container_guid;
   $container = get_entity($container_guid);

   $count_responses=$test->countAnnotations('all_responses');
   $count_responses_draft=$test->countAnnotations('all_responses_draft');
   $count_responses = $count_responses + $count_responses_draft;

   if ($count_responses==0){
      $max_duration_minutes = get_input('max_duration_minutes');
      $all_in_checkbox = get_input('all_in_checkbox');
      $num_cancel_questions = get_input('num_cancel_questions');
      $penalty_not_response = get_input('penalty_not_response');
      $random_questions = get_input('random_questions');
      if (strcmp($random_questions,"on")==0){
         $num_random_questions = get_input('num_random_questions');
      }
      $type_grading = get_input('type_grading');
      if (strcmp($type_grading,'test_type_grading_marks')==0){
         $max_mark = get_input('max_mark');
         $type_mark = get_input('type_mark');
      } else {
         $question_max_game_points = get_input('question_max_game_points'); 
      }
      $type_max_attempts = get_input('type_max_attempts');
      if (strcmp($type_max_attempts,'test_limited_attempts')==0){
         $max_attempts = get_input('max_attempts');
      }
      $correct_responses_visibility = get_input('correct_responses_visibility');
      $subgroups = get_input('subgroups');
   } else {
       $type_grading = $test->type_grading;
   }

   if (strcmp($type_grading,'test_type_grading_marks')==0){
      $public_global_marks = get_input('public_global_marks');
      $mark_weight = get_input('mark_weight');
   }

   if ((strcmp($assessable,"on")==0)&&(strcmp($type_grading,'test_type_grading_marks')==0)) {
      $not_response_is_zero = get_input('not_response_is_zero');
   }

   $test_save=elgg_echo('test:save');

   $now=time();

   // Cache to the session
   elgg_make_sticky_form('edit_test');
   
   if ($count_responses == 0) {
      if (strcmp($type_grading,'test_type_grading_marks')==0){
         if (strcmp($type_mark,'')==0) {
            register_error(elgg_echo("test:empty_type_mark"));
            forward("test/edit/$testpost");
         }
         if (strcmp($type_mark,'test_type_mark_numerical')==0){
            if (strcmp($max_mark,'')==0) { 
               register_error(elgg_echo("test:empty_max_mark"));
	       forward("test/edit/$testpost");
            }
         }  
      }
   }

   $options = array('relationship' => 'test_question', 'relationship_guid' => $testpost,'inverse_relationship' => false, 'type' => 'object', 'subtype' => 'test_question','limit'=>0);
   $questions=elgg_get_entities_from_relationship($options);
   if (empty($questions)) {
      $num_questions = 0;
   } else {
      $num_questions = count($questions);
   }	
   
   //////////////////////////////////////////////////////////////////////////
		
   //Times
   if (strcmp($option_activate_value,'test_activate_date')==0){
      $mask_time="[0-2][0-9]:[0-5][0-9]";
      if (!ereg($mask_time,$opentime,$same)){
         register_error(elgg_echo("test:bad_times"));
	 forward("test/edit/$testpost");
      }
   }
   if (strcmp($option_close_value,'test_close_date')==0){
      $mask_time="[0-2][0-9]:[0-5][0-9]";
      if (!ereg($mask_time,$closetime,$same)){
         register_error(elgg_echo("test:bad_times"));
	 forward("test/edit/$testpost");
      }
   }
   if (strcmp($option_activate_value,'test_activate_now')==0){
      $activate_time=$now;
   } else {
      $opentime_array = explode(':',$opentime);
      $opentime_h = trim($opentime_array[0]);
      $opentime_m = trim($opentime_array[1]);
      $opendate_text = date("Y-m-d",$opendate);
      $opendate = strtotime($opendate_text." ".date_default_timezone_get());
      $opendate_array = explode('-',$opendate_text);
      $opendate_y = trim($opendate_array[0]);
      $opendate_m = trim($opendate_array[1]);
      $opendate_d = trim($opendate_array[2]);
      $activate_date = mktime(0,0,0,$opendate_m,$opendate_d,$opendate_y);
      $activate_time = mktime($opentime_h,$opentime_m,0,$opendate_m,$opendate_d,$opendate_y);

      if ($activate_time < 1){
         register_error(elgg_echo("test:bad_times"));
         forward("test/edit/$testpost");
      }
   }
   if (strcmp($option_close_value,'test_not_close')==0){
      $close_time=$now+1;
   } else {
      $closetime_array = explode(':',$closetime);
      $closetime_h = trim($closetime_array[0]);
      $closetime_m = trim($closetime_array[1]);
      $closedate_text = date("Y-m-d",$closedate);
      $closedate = strtotime($closedate_text." ".date_default_timezone_get());
      $closedate_array = explode('-',$closedate_text);
      $closedate_y = trim($closedate_array[0]);
      $closedate_m = trim($closedate_array[1]);
      $closedate_d = trim($closedate_array[2]);
      $close_date = mktime(0,0,0,$closedate_m,$closedate_d,$closedate_y);
      $close_time = mktime($closetime_h,$closetime_m,0,$closedate_m,$closedate_d,$closedate_y);

      if ($close_time < 1){
         register_error(elgg_echo("test:bad_times"));
         forward("test/edit/$testpost");
      }
   }

   if ($activate_time>=$close_time) {
      register_error(elgg_echo("test:error_times"));
      forward("test/edit/$testpost");
   }
	
   //////////////////////////////////////////////////////////////////////////

   if (strcmp($type_grading,'test_type_grading_marks')==0){

      //Integer mark_weight (0<mark_weight<100)
      /*$is_integer=true;
      $mask_integer='^([[:digit:]]+)$';                           
      if (ereg($mask_integer,$mark_weight,$same)){
         if ((substr($same[1],0,1)==0)&&(strlen($same[1])!=1)){
            $is_integer=false;
         }
      } else {
         $is_integer=false;
      }
      if (!$is_integer){
         register_error(elgg_echo("test:bad_mark_weight"));
         forward("test/edit/$testpost");
      }*/
      $is_number=is_numeric($mark_weight);
      if (!$is_number){
         register_error(elgg_echo("test:bad_mark_weight"));
         forward("test/edit/$testpost");
      }
      if ($mark_weight>100){
         register_error(elgg_echo("test:bad_mark_weight"));
         forward("test/edit/$testpost");
      }
   } 

   if ($count_responses==0){
	
      //////////////////////////////////////////////////////////////////////////

      //Relationship all_in_checkbox with num_cancel_questions 

      if ((strcmp($all_in_checkbox,"proporcional")==0)&&(strcmp($num_cancel_questions,"0")!=0)){
         register_error(elgg_echo("test:bad_relationship_all_in_checkbox_num_cancel_questions"));
         forward("test/edit/$testpost");
      }
      //////////////////////////////////////////////////////////////////////////      

      //Integer max duration minutes
      $is_integer=true;
      $mask_integer='^([[:digit:]]+)$';                           
      if (ereg($mask_integer,$max_duration_minutes,$same)){
         if ((substr($same[1],0,1)==0)&&(strlen($same[1])!=1)){
            $is_integer=false;
         }
      } else {
         $is_integer=false;
      }
      if (!$is_integer){
         register_error(elgg_echo("test:bad_max_duration"));
         forward("test/edit/$testpost");
      }

      //////////////////////////////////////////////////////////////////////////

      //Integer num_random_questions (num_random_questions>0)
      if (strcmp($random_questions,"on")==0){
         if (strcmp($num_random_questions,"")==0){
            register_error(elgg_echo("test:bad_num_random_questions"));
	    forward("test/edit/$testpost");
         }
   
         $is_integer=true;
         $mask_integer='^([[:digit:]]+)$';                           
         if (ereg($mask_integer,$num_random_questions,$same)){
            if ((substr($same[1],0,1)==0)&&(strlen($same[1])!=1)){
               $is_integer=false;
            }
         } else {
            $is_integer=false; 
         }
         if (!$is_integer){
            register_error(elgg_echo("test:bad_num_random_questions"));
	    forward("test/edit/$testpost");
         }
         if ($num_random_questions<1){
            register_error(elgg_echo("test:bad_num_random_questions"));
	    forward("test/edit/$testpost");
         }
      }

      //////////////////////////////////////////////////////////////////////////

      if (strcmp($type_grading,'test_type_grading_marks')!=0){
	 //Integer question max game points
         $is_integer=true;
         $mask_integer='^([[:digit:]]+)$';                           
         if (ereg($mask_integer,$question_max_game_points,$same)){
            if ((substr($same[1],0,1)==0)&&(strlen($same[1])!=1)){
               $is_integer=false;
            } 
         } else {
            $is_integer=false;
         }
         if (!$is_integer){
            register_error(elgg_echo("test:bad_question_max_game_points"));
            forward("test/edit/$testpost");
         }
      }  

      //////////////////////////////////////////////////////////////////////////
	
      //Integer max attempts
      if (strcmp($type_max_attempts,'test_limited_attempts')==0){
	 //Integer max attempts
	 $is_integer=true;
         $mask_integer='^([[:digit:]]+)$';                           
         if (ereg($mask_integer,$max_attempts,$same)){
            if ((substr($same[1],0,1)==0)&&(strlen($same[1])!=1)){
               $is_integer=false;
            }
         } else {
            $is_integer=false;
         }
         if (!$is_integer){
            register_error(elgg_echo("test:bad_max_attempts"));
            forward("test/edit/$testpost");
         }
      }
   }

   // Convert string of tags into a preformatted array
   $tagarray = string_to_tag_array($tags);
	
   // Make sure the title and description aren't blank
   if ((strcmp($title,"")==0) || (strcmp($description,"")==0)) {	
      register_error(elgg_echo("test:blank"));
      forward("test/edit/$testpost");		
   } 
	
   // Set its access
   $test->access_id = $access_id;

   // Set its title 
   $test->title= $title;

   // Set its description
   $test->description= $description;

   // Before we can set metadata, we need to save the test post
   if (!$test->save()) {
      register_error(elgg_echo("test:error_save"));
      forward("test/edit/$testpost");
   }

   //Set times
   $test->option_activate_value = $option_activate_value;
   $test->option_close_value = $option_close_value;
   $test->save_action = false;
   if (strcmp($option_activate_value,'test_activate_now')!=0){
      $test->activate_date = $activate_date;
      $test->activate_time = $activate_time;
      $test->form_activate_date = $activate_date;
      $test->form_activate_time = $opentime;
   }
   if (strcmp($option_close_value,'test_not_close')!=0){
      $test->close_date = $close_date;
      $test->close_time = $close_time;
      $test->form_close_date = $close_date;
      $test->form_close_time = $closetime;
   }
   if ((strcmp($option_activate_value,'test_activate_date')==0)&&(strcmp($option_close_value,'test_close_date')==0)){
      if (($now>=$activate_time)&&($now<$close_time)){
         $test->opened=true;
      } else {
         $test->opened=false;
      }
   } elseif (strcmp($option_activate_value,'test_activate_date')==0){
      if ($now>=$activate_time) {
         $test->opened=true;
      } else {
         $test->opened=false;
      }
   } elseif (strcmp($option_close_value,'test_close_date')==0){
      if ($now<$close_time) {
         $test->opened=true;
      } else {
         $test->opened=false;
      } 
   } else {
      $test->opened=true;
   }
		
   if ($count_responses==0){
      // Set max duration minutes
      if ($max_duration_minutes == 0)
         $test->max_duration_minutes = 999999999;
      else
         $test->max_duration_minutes = $max_duration_minutes;

      // Set assessable
      if (strcmp($assessable,"on")==0) {
         $test->assessable=true;
      } else { 
         $test->assessable=false;
      }

      //Set all in checkbox
      $test->all_in_checkbox = $all_in_checkbox;

      //Set num cancel questions
      $test->num_cancel_questions= $num_cancel_questions;

      // Set penalty not response
      if (strcmp($penalty_not_response,"on")==0) {
         $test->penalty_not_response=true;
      } else { 
         $test->penalty_not_response=false;
      }

      // Set random questions
      if (strcmp($random_questions,"on")==0) {
         $test->random_questions=true;
	 $previous_num_random_questions = $test->num_random_questions;
	 if (strcmp($previous_num_random_questions,$num_random_questions)!=0){
            if ($num_random_questions<$num_questions){
               $num_questions = $num_random_questions;
            }
            foreach($questions as $one_question){
               if (strcmp($test->type_grading,'test_type_grading_marks')==0) {
                  $one_question->grading = ($test->max_mark*1.0)/$num_questions;
               } else {
	          $one_question->grading = $test->question_max_game_points/$num_questions;
               }
            }
	    $test->num_random_questions=$num_random_questions;
         }
      } else { 
         $test->random_questions=false;
      }

      // Set type of grading
      $test->type_grading= $type_grading;
      if (strcmp($type_grading,'test_type_grading_marks')==0){
         $test->max_mark = $max_mark;
	 $test->type_mark = $type_mark;
	 //Information for plugin marks
	 switch($type_mark){
            case 'test_type_mark_numerical':
	       if ($max_mark==10)
                  $test->mark_type=NUMERIC10;
	       else
	          $test->mark_type=NUMERIC100;
	       break;
	    case 'test_type_mark_textual':
	       $test->mark_type=STRINGUNI;
	       break;
	    case 'test_type_mark_apto':
	       $test->mark_type=BOOLEAN;
	       break;
         }  
      } else {
         $test->question_max_game_points = $question_max_game_points;
      }
       		
      // Set attempts
      $test->type_max_attempts = $type_max_attempts;
      if (strcmp($type_max_attempts,'test_limited_attempts')==0){
	 $test->max_attempts = $max_attempts;
      }
      else $test->max_attempts= 999999;

      // Set correct responses visibility
      $test->correct_responses_visibility = $correct_responses_visibility;

      // Set subgroups
      if (strcmp($subgroups,"on")==0) {
         $test->subgroups=true;
	 $test->who_answers='subgroup';
      } else { 
         $test->subgroups=false;
	 $test->who_answers='member';
      }
   } 

   //More information for plugin marks
   if (strcmp($type_grading,'test_type_grading_marks')==0){
      $test->mark_weight = $mark_weight;
   }

   //Set public global marks
   if (strcmp($type_grading,'test_type_grading_marks')==0){
      if (strcmp($public_global_marks,"on")==0) {
         $test->public_global_marks=true;
      } else { 
         $test->public_global_marks=false;
      }
   }  

   //Set not_response_is_zero
   if (($test->assessable)&&(strcmp($test->type_grading,'test_type_grading_marks')==0)) {
      if (strcmp($not_response_is_zero,"on")==0) {
         $test->not_response_is_zero=true;
      } else { 
         $test->not_response_is_zero=false;
      }
   }
		
   // Set feedback
   if (strcmp($feedback,"")!=0)
      $test->feedback = $feedback;
   else
      $test->feedback = "not_feedback";
				
   // Now let's add tags.
   if (is_array($tagarray)) {
      $test->tags = $tagarray;
   }

   // Questions and files access
   if (!empty($questions)){
      foreach ($questions as $one_question){
         $one_question->access_id = $test->access_id;
         if (!$one_question->save()){
            register_error(elgg_echo("test:question_error_save"));
            forward("test/edit/$testpost");
         }
	 $files = elgg_get_entities_from_relationship(array('relationship' => 'question_file_link','relationship_guid' => $one_question->getGUID(),'inverse_relationship' => false,'type' => 'object','limit'=>0));
	 foreach ($files as $one_file){
	    $one_file->access_id = $one_question->access_id;
	    if (!$one_file->save()){
               register_error(elgg_echo("test:file_error_save"));
               forward("test/edit/$testpost");
            }
	 } 
      }
   }

   // Remove the test post cache
   elgg_clear_sticky_form('edit_test');

   // Forward 
   if ($test->created) {

      // Add to river
      elgg_create_river_item(array(
            'view'=>'river/object/test/update',
            'action_type'=>'update',
            'subject_guid'=>$user_guid,
            'object_guid'=>$testpost,
         ));
      //Nofity
      if ($access_id!=0) { 
            $username = $user->name;
            $site_guid = elgg_get_config('site_guid');
            $site = get_entity($site_guid);
            $sitename = $site->name;
            $group = $container;
            $groupname = $container->name;
            $link = $test->getURL();
            $subject = sprintf(elgg_echo('test:update:group:email:subject'),$username,$sitename,$groupname);
            $group_members = $group->getMembers(array('limit'=>false));
            foreach ($group_members as $member){
               $member_guid = $member->getGUID();
               if ($member_guid != $test->owner_guid){
                  $body = sprintf(elgg_echo('test:update:group:email:body'),$member->name,$username,$sitename,$groupname,$title,$link);
	          notify_user($member_guid,$test->owner_guid,$subject,$body, array('action'=>'update', 'object'=>$test));
               }
            }
      }
      
      //Event using the event_manager plugin if it is active
      if (elgg_is_active_plugin('event_manager') && strcmp($option_close_value,'test_not_close')!=0){

         $event_guid = $test->event_guid;
         if (!($event=get_entity($event_guid))){
            $event = new Event();
         } 

         $event->title = sprintf(elgg_echo("test:event_manager_title"),$test->title);
         $event->description = $test->getURL();
         $event->container_guid = $container_guid;
         $event->access_id = $access_id;
         $event->save();
         $event->tags = string_to_tag_array($tags);   
         $event->comments_on = 0;
         $event->registration_ended = 1;
         $event->show_attendees = 0;
         $event->max_attendees = "";
         $event->start_day = $close_date;
         $event->start_time = $close_time;
         $event->end_ts = $close_time+1;
         $event->organizer = $user->getDisplayName();
         $event->setAccessToOwningObjects($access_id);

         // Save it, if it is new
         if (!get_entity($event_guid)){         
            if ($event->save()){
               $event_guid = $event->getGUID();
               $test->event_guid = $event_guid;
            } else {
               register_error(elgg_echo("test:event_manager_error_save"));
            }
         }
      }
   }
  
   forward(elgg_get_site_url() . 'test/view/' . $testpost);

}              

   
?>
