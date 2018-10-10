<?php

gatekeeper();
if (is_callable('group_gatekeeper')) 
   group_gatekeeper();

$owner = elgg_get_page_owner_entity();
$user_guid = elgg_get_logged_in_user_guid();
$user = get_entity($user_guid);

$title = elgg_echo(sprintf(elgg_echo('test:user'),$owner->name));

$group_guid = $owner->getGUID();
$group_owner_guid = $owner->owner_guid;

$operator=false;
if (($group_owner_guid==$user_guid)||(check_entity_relationship($user_guid,'group_admin',$group_guid))){
   $operator=true;
}

if ($operator)
   elgg_register_title_button('test','add');

$tests = elgg_get_entities(array('type'=>'object','subtype'=>'test','limit'=>false,'container_guid'=>$owner->getGUID()));
			
if (!$operator){
   $i=0;
   $j=0;
   $my_repeatable_tests=array();
   $my_repeatable_tests_attempts=array();
   $my_repeatable_tests_grading=array();
   $my_repeatable_tests_time=array();
   $my_repeatable_tests_total_time=array();
   $my_finished_tests=array();
   $my_finished_tests_attempts=array();
   $my_finished_tests_grading=array();
   $my_finished_tests_time=array();
   $my_finished_tests_total_time=array();
   foreach($tests as $test){
      if ($test->created){	
         $container_guid  = $test->container_guid;
         if ($test->subgroups){
	    $user_subgroups = elgg_get_entities_from_relationship(array('type_subtype_pairs' => array('group' => 'lbr_subgroup'),'container_guids' => $container_guid,'relationship' => 'member','inverse_relationship' => false,'relationship_guid' => $user_guid));
	    if ($user_subgroups) {
	       $user_subgroup_guid=$user_subgroups[0]->getGUID();
	       $options = array('relationship' => 'test_answer', 'relationship_guid' => $test->getGUID(),'inverse_relationship' => false, 'type' => 'object', 'subtype' => 'test_answer', 'order_by' => 'e.time_created desc', 'limit' => 0, 'container_guid' => $user_subgroup_guid); 
	    }
         } else {
	    $options = array('relationship' => 'test_answer', 'relationship_guid' => $test->getGUID(),'inverse_relationship' => false, 'type' => 'object', 'subtype' => 'test_answer', 'order_by' => 'e.time_created desc', 'limit' => 0, 'owner_guid' => $user_guid);
         }
         $attempts=0;
         $grading=0;
	 $time=0;
         $user_response="";
	 if ($options)
            $user_responses=elgg_get_entities_from_relationship($options);
         if (!empty($user_responses)){
	    $user_response=$user_responses[0];
	    $grading=number_format($user_response->grading,2);
	    $attempts=$user_response->attempts;
	    $time=$user_response->time;
	    $total_time = 0;
	    $all_times = explode(";",$user_response->all_times);
	    foreach ($all_times as $one_time) {
	       $total_time += $one_time;
	    }
         } 
	 if ((($test->subgroups) && ($user_subgroup_guid))||(!$test->subgroups)) {
            if (strcmp($test->type_grading,'test_type_grading_marks')==0){
               $max_grading=$test->max_mark;
            } else {
               $max_grading=$test->question_max_game_points;
            }
            $repeatable=false;
            if (((strcmp($test->type_max_attempts,'test_limited_attempts')==0)&&($attempts<$test->max_attempts))||((strcmp($test->type_max_attempts,'test_max_grading_limited_attempts')==0)&&($grading<$max_grading))||(strcmp($test->type_max_attempts,'test_not_limited_attempts')==0)){ 
               $repeatable=true;
            }
            if ($repeatable){
               $my_repeatable_tests[$i]=$test;
               $my_repeatable_tests_attempts[$i]=$attempts;
               $my_repeatable_tests_grading[$i]=$grading;
	       $my_repeatable_tests_time[$i]=$time;
	       $my_repeatable_tests_total_time[$i]=$total_time;
               $i=$i+1;
            } else {
               $my_finished_tests[$j]=$test;
               $my_finished_tests_attempts[$j]=$attempts;
               $my_finished_tests_grading[$j]=$grading;
	       $my_finished_tests_time[$j]=$time;
	       $my_finished_tests_total_time[$i]=$total_time;
               $j=$j+1;
            }
	 }
      }
   }
   $num_repeatable_tests=$i;
   $num_finished_tests=$j;
}
	
$content = "";
	
if ($operator){
   foreach ($tests as $test){
      $content .= elgg_view("object/test", array('full_view' => false, 'entity' => $test, 'user_type' => "operator"));
   }
} else {
   $i=0;
   while ($i<$num_repeatable_tests){
      $content .= elgg_view("object/test", array('full_view' => false, 'entity' => $my_repeatable_tests[$i], 'user_type' => "repeatable",'attempts' => $my_repeatable_tests_attempts[$i], 'grading' => $my_repeatable_tests_grading[$i],'time' => $my_repeatable_tests_time[$i],'total_time' => $my_repeatable_tests_total_time[$i]));
      $i=$i+1;
   }
   $j=0;
   while ($j<$num_finished_tests){   
      $content .= elgg_view("object/test", array('full_view' => false, 'entity' => $my_finished_tests[$j], 'user_type' => "finished", 'attempts' => $my_finished_tests_attempts[$j], 'grading' => $my_finished_tests_grading[$j], 'time' => $my_finished_tests_time[$j], 'total_time' => $my_finished_tests_total_time[$j]));
      $j=$j+1;
   }
}

$params = array('content' => $content,'title' => $title);

if (elgg_instanceof($owner, 'group')) {
   $params['filter'] = '';
}

$body = elgg_view_layout('content', $params);
echo elgg_view_page($title, $body);
		
?>