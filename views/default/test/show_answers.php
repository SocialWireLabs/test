<?php

$test=$vars['entity'];
$testpost=$test->getGUID();
$offset = $vars['offset'];
$membersarray = $vars['membersarray'];
if (!empty($membersarray))
   $count = count($membersarray);
else
   $count = 0;

$limit = 10;
$this_limit = $offset+$limit;

$form_body = "";

$opened = test_check_status($test);
$wwwroot = elgg_get_config('wwwroot');
$img_template = '<img border="0" width="16" height="16" alt="%s" title="%s" src="' . $wwwroot . 'mod/test/graphics/%s" />';
///////////////////////////////////////////////////////////////////////
//Assign marks or game points
if ($test->assessable){
   if (strcmp($test->type_grading,'test_type_grading_marks')==0){
      $url_assign_marks=elgg_add_action_tokens_to_url(elgg_get_site_url() . "action/test/assign_marks?testpost=" . $testpost);
      $assign_marks_text=elgg_echo("test:assign_marks");
      $link_assign_marks="<a href=\"{$url_assign_marks}\">{$assign_marks_text}</a>";
      
      if (!$opened)
         $form_body .= $link_assign_marks;
         
   } else {
      $url_assign_game_points=elgg_add_action_tokens_to_url(elgg_get_site_url() . "action/test/assign_game_points?testpost=" . $testpost);
      $assign_game_points_text=elgg_echo("test:assign_game_points");
      $link_assign_game_points="<a href=\"{$url_assign_game_points}\">{$assign_game_points_text}</a>";
      if (!$opened)
         $form_body .= $link_assign_game_points;
   }
}

//Export statistics
$url_export_statistics_pdf=elgg_add_action_tokens_to_url(elgg_get_site_url() . "action/test/export_statistics_pdf?testpost=" . $testpost);
$export_statistics_pdf_text=elgg_echo("test:export_statistics_pdf");
$link_export_statistics_pdf="<a href=\"{$url_export_statistics_pdf}\">{$export_statistics_pdf_text}</a>";
if (!$opened){
   if ($test->assessable)
      $form_body .= " | ".$link_export_statistics_pdf;
   else
      $form_body .= $link_export_statistics_pdf;
}

//General comments
$num_comments =  $test->countComments();
if ($num_comments>0)
   $test_general_comments_label = elgg_echo('test:general_comments') . " (" . $num_comments . ")";
else
   $test_general_comments_label = elgg_echo('test:general_comments');
$form_body .= "<div class=\"contentWrapper\">";
$form_body .= "<div class=\"test_frame_green\">";
$form_body .= "<p align=\"left\"><a onclick=\"test_show_general_comments();\" style=\"cursor:hand;\">$test_general_comments_label</a></p>";
$form_body .= "<div id=\"commentsDiv\" style=\"display:none;\">";
$form_body .= elgg_view_comments($test);
$form_body .= "</div>";
$form_body .= "</div>";
$form_body .= "</div>";

////////////////////////////////////////////////////////////////////////////
//Responses

$form_body .= "<div class=\"contentWrapper\">";
$form_body .= "<div class=\"test_frame_green\">";

if ($count>0){

   $form_body .= elgg_echo('test:responses') . " (" . $count . ")" . "<br>";

   $i=0;
   $k=0;
   foreach ($membersarray as $member){
      if (($i>=$offset)&&($i<$this_limit)){
         $member_guid=$member->getGUID();
         
         $url=elgg_add_action_tokens_to_url(elgg_get_site_url() . "test/view/$testpost/?first_user_guid=$member_guid");
         $url_text = elgg_echo('test:response') . " " . elgg_echo('test:of') . " " . $member->name;
         $url_delete = elgg_add_action_tokens_to_url(elgg_get_site_url() . "action/test/delete_answer?testpost=" . $testpost . "&user_guid=" . $member_guid . "&offset=" . $offset);
         $url_add_attempt = elgg_add_action_tokens_to_url(elgg_get_site_url() . "action/test/add_attempt?testpost=" . $testpost . "&user_guid=" . $member_guid . "&offset=" . $offset);
         $img_delete_msg = elgg_echo('test:delete_answer');
         $img_add_attempt_msg = elgg_echo('test:add_attempt');
         $confirm_delete_msg = elgg_echo('test:delete_answer_confirm');
         $confirm_add_attempt_msg = elgg_echo('test:add_attempt_confirm');
         $img_delete = sprintf($img_template, $img_delete_msg, $img_delete_msg, "delete.gif");
         $img_add_attempt = sprintf($img_template, $img_add_attempt_msg, $img_add_attempt_msg, "add.png");
         
         if (!$test->subgroups){
               $options = array('relationship' => 'test_answer', 'relationship_guid' => $testpost,'inverse_relationship' => false, 'type' => 'object', 'subtype' => 'test_answer', 'order_by' => 'e.time_created desc', 'limit' => 0, 'owner_guid' => $member_guid);
         } else {
               $options = array('relationship' => 'test_answer', 'relationship_guid' => $testpost,'inverse_relationship' => false, 'type' => 'object', 'subtype' => 'test_answer', 'order_by' => 'e.time_created desc', 'limit' => 0, 'container_guid' => $member_guid);
         }
         $user_responses=elgg_get_entities_from_relationship($options);
       
         $attempts=0;
         $grading="";
	 $time="";
	 $time_minutes="";
         $user_response="";
         if (!empty($user_responses)){
            $user_response=$user_responses[0];            
            $attempts=$user_response->attempts;
            $grading=$user_response->grading; 
	    $time=$user_response->time;
	    if (strcmp($time,"")!=0){
	       $time_minutes = $time/60;
	       $time_minutes = number_format($time_minutes,2) . "'";
	    }
         } 

         $total_time="";
         $all_times_array = array_map('trim',explode(";",$user_response->all_times));
         foreach ($all_times_array as $one_time) {
            $total_time=$total_time+$one_time;
         }
         if (strcmp($time,"")!=0){
            $total_time_minutes = $total_time/60;
            $total_time_minutes = number_format($total_time_minutes,2) . "'";
         }

         if (!empty($user_response)){
            $link="<a href=\"{$url}\">{$url_text}</a>";
            if (!$opened) {
               $link .= " <a onclick=\"return confirm('$confirm_delete_msg')\" href=\"{$url_delete}\">{$img_delete}</a>";
	       if(strcmp($test->type_max_attempts,"test_limited_attempts")==0){

                  $link .= " <a onclick=\"return confirm('$confirm_add_attempt_msg')\" href=\"{$url_add_attempt}\">{$img_add_attempt}</a>";
	       }
            }
         } else {
            $link="$url_text";
         }

         $form_body .= elgg_view("test/show_answers_view",array('entity'=>$test,'user'=>$member,'link'=>$link,'attempts'=>$attempts,'time'=>$time_minutes,'grading'=>$grading, 'total_time'=>$total_time_minutes));         
      }
      $i=$i+1;
   }

   $form_body .= elgg_view("navigation/pagination",array('count'=>$count,'offset'=>$offset,'limit'=>$limit));

} else {
   $form_body .= elgg_echo('test:responses') . "<br>";
   $form_body .= elgg_echo('test:not_responses');
}
$form_body .= "</div>";
$form_body .= "</div>";

echo elgg_echo($form_body);

?>

<script type="text/javascript">
   function test_show_general_comments(){
      var commentsDiv = document.getElementById('commentsDiv');
      if (commentsDiv.style.display == 'none'){
         commentsDiv.style.display = 'block';
      } else {       
         commentsDiv.style.display = 'none';
      }
   }    
</script>
 
