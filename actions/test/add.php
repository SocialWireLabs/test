<?php

gatekeeper();

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
$max_duration_minutes = get_input('max_duration_minutes');
$assessable = get_input('assessable');
$all_in_checkbox = get_input('all_in_checkbox');
$num_cancel_questions = get_input('num_cancel_questions');
$penalty_not_response = get_input('penalty_not_response');
$random_questions = get_input('random_questions');
if (strcmp($random_questions,"on")==0){
   $num_random_questions = get_input('num_random_questions');
}

$type_grading = get_input('type_grading');

if ((strcmp($assessable,"on")==0)&&(strcmp($type_grading,'test_type_grading_marks')==0)) {
   $not_response_is_zero = get_input('not_response_is_zero');
}
if (strcmp($type_grading,'test_type_grading_marks')==0){
   $max_mark = get_input('max_mark');
   $type_mark = get_input('type_mark');
   $mark_weight = get_input('mark_weight');
   $public_global_marks = get_input('public_global_marks');
} else {
   $question_max_game_points = get_input('question_max_game_points');
}
$type_max_attempts = get_input('type_max_attempts');
if (strcmp($type_max_attempts,'test_limited_attempts')==0){
   $max_attempts = get_input('max_attempts');
}
$correct_responses_visibility = get_input('correct_responses_visibility');
$feedback = get_input('feedback');
$subgroups = get_input('subgroups');
$tags = get_input('testtags');
$access_id = get_input('access_id'); 
$container_guid = get_input('container_guid');
$container = get_entity($container_guid);
$selected_action = get_input('submit');

// Cache to the session
elgg_make_sticky_form('add_test');

//////////////////////////////////////////////////////////////////////////

//Times
if (strcmp($option_activate_value,'test_activate_date')==0){
   $mask_time="[0-2][0-9]:[0-5][0-9]";
   if (!ereg($mask_time,$opentime,$same)){
	register_error(elgg_echo("test:bad_times"));
	forward($_SERVER['HTTP_REFERER']);
   }
}
if (strcmp($option_close_value,'test_close_date')==0){
   $mask_time="[0-2][0-9]:[0-5][0-9]";
   if (!ereg($mask_time,$closetime,$same)){
	register_error(elgg_echo("test:bad_times"));
	forward($_SERVER['HTTP_REFERER']);
   }
}
$now=time();
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
      forward($_SERVER['HTTP_REFERER']);
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
      forward($_SERVER['HTTP_REFERER']);
   }
}
if ($activate_time>=$close_time) {
   register_error(elgg_echo("test:error_times"));
   forward($_SERVER['HTTP_REFERER']);
}
//////////////////////////////////////////////////////////////////////////

//Relationship all_in_checkbox with num_cancel_questions 

if ((strcmp($all_in_checkbox,"proporcional")==0)&&(strcmp($num_cancel_questions,"0")!=0)){
   register_error(elgg_echo("test:bad_relationship_all_in_checkbox_num_cancel_questions"));
   forward($_SERVER['HTTP_REFERER']);
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
   forward($_SERVER['HTTP_REFERER']);
}

//////////////////////////////////////////////////////////////////////////

//Integer num_random_questions (num_random_questions>0)
if (strcmp($random_questions,"on")==0){
   if (strcmp($num_random_questions,"")==0){
      register_error(elgg_echo("test:bad_num_random_questions"));
      forward($_SERVER['HTTP_REFERER']);
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
      forward($_SERVER['HTTP_REFERER']);
   }
   if ($num_random_questions<1){
      register_error(elgg_echo("test:bad_num_random_questions"));
      forward($_SERVER['HTTP_REFERER']);
   }
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
      forward($_SERVER['HTTP_REFERER']);
   }*/
   $is_number=is_numeric($mark_weight);
   if (!$is_number){
      register_error(elgg_echo("test:bad_mark_weight"));
      forward($_SERVER['HTTP_REFERER']);
   }
   if ($mark_weight>100){
      register_error(elgg_echo("test:bad_mark_weight"));
      forward($_SERVER['HTTP_REFERER']);
   }
} else {
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
      forward($_SERVER['HTTP_REFERER']);
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
      forward($_SERVER['HTTP_REFERER']);
   }
}

//////////////////////////////////////////////////////////////////////////

// Convert string of tags into a preformatted array
$tagarray = string_to_tag_array($tags);

// Make sure the title and description aren't blank
if ((strcmp($title,"")==0) || (strcmp($description,"")==0)) {
   register_error(elgg_echo("test:blank"));
   forward($_SERVER['HTTP_REFERER']);
} 			
   
// Initialise a new ElggObject
$test = new ElggObject();

// Tell the system it's a test post
$test->subtype = "test";

// Set its owner, container and group
$test->owner_guid = $user_guid;
$test->container_guid = $container_guid;
$test->group_guid = $container_guid;

// Set its access
$test->access_id = $access_id;

// Set its title 
$test->title = $title; 

// Set its description
$test->description = $description;

// Set created
$test->created=false;
   
// Save the test post
if (!$test->save()) {
   register_error(elgg_echo("test:error_save"));
   forward($_SERVER['HTTP_REFERER']);
}

$testpost=$test->getGUID();

// Set times
$test->option_activate_value = $option_activate_value;
$test->option_close_value = $option_close_value;
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

// Set max duration
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
   $test->num_random_questions=$num_random_questions;
} else { 
   $test->random_questions=false;
}

// Set type of grading
$test->type_grading = $type_grading;
if (strcmp($type_grading,'test_type_grading_marks')==0){
   $test->max_mark = $max_mark;
   $test->type_mark = $type_mark;
   // Information for plugin marks
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
   $test->mark_weight = $mark_weight;
   if (strcmp($public_global_marks,"on")==0) {
      $test->public_global_marks=true;
   } else { 
      $test->public_global_marks=false;
   }
} else {
   $test->question_max_game_points = $question_max_game_points;
}

//Set not_response_is_zero
if (($test->assessable)&&(strcmp($test->type_grading,'test_type_grading_marks')==0)) {
   if (strcmp($not_response_is_zero,"on")==0) {
      $test->not_response_is_zero=true;
   } else { 
      $test->not_response_is_zero=false;
   }
}
   
// Set attempts
$test->type_max_attempts = $type_max_attempts;
if (strcmp($type_max_attempts,'test_limited_attempts')==0){
   $test->max_attempts = $max_attempts;
}

// Set correct responses visibility
$test->correct_responses_visibility = $correct_responses_visibility;

// Set feedback
if (strcmp($feedback,"")!=0)
   $test->feedback = $feedback;
else
   $test->feedback = "not_feedback";

//Set subgroups
if (strcmp($subgroups,"on")==0) {
   $test->subgroups=true;
   $test->who_answers='subgroup';
} else { 
   $test->subgroups=false;
   $test->who_answers='member';
}

// Now let's add tags.
if (is_array($tagarray)) {
   $test->tags = $tagarray;
}
	
// Remove the test post cache
elgg_clear_sticky_form('add_test');

//Forward
forward(elgg_get_site_url() . 'test/view/' . $testpost);

?>