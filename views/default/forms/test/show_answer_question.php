<?php

elgg_load_library('test');

$test=$vars['test'];
$one_question = $vars['one_question'];
$num_question=$vars['num_question'];
$question_text=$vars['question_text'];
$question_body=$vars['question_body'];
$question_grading=$vars['question_grading'];
$this_grading=$vars['this_grading'];
$response_type=$vars['response_type'];
$response_inputs=$vars['response_inputs'];
$correct_responses_visibility=$vars['correct_responses_visibility'];
$correct_response_inputs=$vars['correct_response_inputs'];
$comments_body=$vars['comments_body'];

$form_body = "";

//Grading   
if (strcmp($test->type_grading,'test_type_grading_marks')==0){
   $grading_label=elgg_echo("test:mark");
} else {
   $grading_label=elgg_echo("test:game_points");
}

$form_body .= "<div class=\"test_frame_blue\">";
//Question
$form_body .= "<p><b>" . elgg_echo('test:question_label') . " $num_question" . "</p></b>";
$form_body .= $question_text . "<br>";
if (strcmp($question_body,"")!=0){
   $form_body .=  $question_body;
}

$form_body .= "</div>";
$form_body .= "<br>";

$form_body .= "<div class=\"test_frame_yellow\">";
//Response
$form_body .= "<p><b>" . elgg_echo('test:response') . "</p></b>";
$form_body .= $response_inputs;

$owner = $test->getOwnerEntity();
$owner_guid = $owner->getGUID();
$user_guid = elgg_get_logged_in_user_guid();
$user = get_entity($user_guid);
$group_guid=$test->container_guid;
$group = get_entity($group_guid);
$group_owner_guid = $group->owner_guid;


$operator=false;
if (($owner_guid==$user_guid)||($group_owner_guid==$user_guid)||(check_entity_relationship($user_guid,'group_admin',$group_guid))){
   $operator=true;
}
if ($operator){
   $form_body .= "<br><p><b>" . elgg_echo('test:correct_response') . "</p></b>";
   $form_body .= $correct_response_inputs; 
   if ($one_question->question_explanation){
      $form_body .= "<br><p><b>" . elgg_echo('test:question_explanation') . "</p></b>";
      $form_body .= $one_question->question_explanation;
   }
   $form_body .= "<p><b>" . elgg_echo('test:grading_label') . "</p></b>";
   $form_body .= $this_grading;
} else{
   if ($correct_responses_visibility){     
      $form_body .= "<br><p><b>" . elgg_echo('test:correct_response') . "</p></b>";
      $form_body .= $correct_response_inputs; 
      if ($one_question->question_explanation){
         $form_body .= "<br><p><b>" . elgg_echo('test:question_explanation') . "</p></b>";
         $form_body .= $one_question->question_explanation;
      } 
      $form_body .= "<p><b>" . elgg_echo('test:grading_label') . "</p></b>";
      $form_body .= $this_grading;
   }   
}

//Comments
if (strcmp($comments_body,"")!=0){
   $form_body .= $comments_body;
}
$form_body .= "</div>";
$form_body .= "<br>";

echo elgg_echo($form_body);

?>