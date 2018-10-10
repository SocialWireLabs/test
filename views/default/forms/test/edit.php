<div class="contentWrapper">

<?php

$testpost=$vars['entity']->getGUID();
$test = get_entity($testpost);
$action = "test/edit";

if ((test_check_status($test))&&($test->created)){

   $test_opened = elgg_echo('test:opened');
   $close_test = elgg_echo('test:close');
   $form_body = "";
   $form_body .= "<p>" . $test_opened . "</p>";
   $entity_hidden = elgg_view('input/hidden', array('name' => 'testpost', 'value' => $testpost));
   $entity_hidden .= elgg_view('input/hidden', array('name' => 'close_test', 'value' => "yes"));
   $submit_input = elgg_view('input/submit', array('name' => 'submit', 'value' => $close_test));
   $form_body .= "<p>" . $submit_input . $entity_hidden . "</p>";
   echo elgg_view('input/form', array('action' => elgg_get_site_url()."action/$action", 'body' => $form_body));

} else {

   $count_responses=$test->countAnnotations('all_responses');
   $count_responses_draft=$test->countAnnotations('all_responses_draft');
   $count_responses = $count_responses + $count_responses_draft;

   if (!elgg_is_sticky_form('edit_test')) {
      $title = $test->title;
      $description = $test->description;
      $opendate = $test->form_activate_date;
      $opentime = $test->form_activate_time;
      $closedate = $test->form_close_date;
      $closetime = $test->form_close_time;
      $option_activate_value = $test->option_activate_value;
      $option_close_value = $test-> option_close_value;
      if ($test->max_duration_minutes == 999999999) {
         $max_duration_minutes = 0;
      } else {
         $max_duration_minutes = $test->max_duration_minutes;
      }
      $assessable = $test->assessable;

      $all_in_checkbox = $test->all_in_checkbox;
      $num_cancel_questions = $test ->num_cancel_questions;
      $penalty_not_respones = $test->penalty_not_response;
      $not_response_is_zero = $test->not_response_is_zero;
      $random_questions = $test->random_questions;
      if ($random_questions){
         $num_random_questions = $test->num_random_questions;
      }
      $type_grading = $test->type_grading;
      if (($assessable)&&(strcmp($type_grading,'test_type_grading_marks')==0)) {
         $not_response_is_zero = $test->not_response_is_zero;
      }
      if (strcmp($type_grading,'test_type_grading_marks')==0){
         $max_mark = $test->max_mark;
         $type_mark = $test->type_mark;
         $mark_weight = $test->mark_weight;
         $public_global_marks = $test->public_global_marks;
      } else {
         $question_max_game_points = $test->question_max_game_points;
      }
      $type_max_attempts = $test->type_max_attempts;
      if (strcmp($type_max_attempts,'test_limited_attempts')==0){
         $max_attempts = $test->max_attempts;
      }
      $correct_responses_visibility = $test->correct_responses_visibility;
      $feedback = $test->feedback;
      $subgroups = $test->subgroups;
      $tags = $test->tags;
      $access_id = $test->access_id;
     
   } else {

      $title = elgg_get_sticky_value('edit_test','title');
      $description = elgg_get_sticky_value('edit_test','description');
      $opendate = elgg_get_sticky_value('edit_test','opendate');
      $closedate = elgg_get_sticky_value('edit_test','closedate');
      $opentime = elgg_get_sticky_value('edit_test','opentime');
      $closetime = elgg_get_sticky_value('edit_test','closetime');
      $option_activate_value = elgg_get_sticky_value('edit_test','option_activate_value');
      $option_close_value = elgg_get_sticky_value('edit_test','option_close_value');
      $assessable = elgg_get_sticky_value('edit_test','assessable');
      $feedback = elgg_get_sticky_value('edit_test','feedback');
    
      $tags =  elgg_get_sticky_value('edit_test','testtags');
      $access_id = elgg_get_sticky_value('edit_test','access_id');

      if ($count_responses==0){
         $max_duration_minutes = elgg_get_sticky_value('edit_test','max_duration_minutes');
	 $all_in_checkbox = elgg_get_sticky_value('edit_test','all_in_checkbox');
         $num_cancel_questions = elgg_get_sticky_value('edit_test','num_cancel_questions');
	 $penalty_not_response = elgg_get_sticky_value('edit_test','penalty_not_response');
	 $random_questions = elgg_get_sticky_value('edit_test','random_questions');
         $num_random_questions = elgg_get_sticky_value('edit_test','num_random_questions');
         $type_grading = elgg_get_sticky_value('edit_test','type_grading');
         if (strcmp($type_grading,'test_type_grading_marks')==0){
	    $max_mark = elgg_get_sticky_value('edit_test','max_mark');
            $type_mark = elgg_get_sticky_value('edit_test','type_mark');
            $mark_weight = elgg_get_sticky_value('edit_test','mark_weight');
            $public_global_marks = elgg_get_sticky_value('edit_test','public_global_marks');
         } else {
            $question_max_game_points = elgg_get_sticky_value('edit_test','question_max_game_points');;
         }
         $type_max_attempts = elgg_get_sticky_value('edit_test','type_max_attempts');
         if (strcmp($type_max_attempts,'test_limited_attempts')==0){
            $max_attempts = elgg_get_sticky_value('edit_test','max_attempts');
         }
         $correct_responses_visibility = elgg_get_sticky_value('edit_test','correct_responses_visibility');
	 $subgroups = elgg_get_sticky_value('edit_test','subgroups');
      }  else {
         $max_duration_minutes = $test->max_duration_minutes;
	 $all_in_checkbox = $test->all_in_checkbox;
	 $num_cancel_questions = $test->num_cancel_questions;
	 $penalty_not_response = $test->penalty_not_response;
         $type_grading = $test->type_grading;
         if (strcmp($type_grading,'test_type_grading_marks')==0){
	    $max_mark = $test->max_mark;
            $type_mark = $test->type_mark;
            $mark_weight = elgg_get_sticky_value('edit_test','mark_weight');
            $public_global_marks = elgg_get_sticky_value('edit_test','public_global_marks');
         } else {
            $question_max_game_points = $test->question_max_game_points;;
         }
         $type_max_attempts = $test->type_max_attempts;
         if (strcmp($type_max_attempts,'test_limited_attempts')==0){
            $max_attempts = $test->max_attempts;
         }
         $correct_responses_visibility = $test->correct_responses_visibility;
	 $subgroups = $test->subgroups;
      }
      if (($assessable)&&(strcmp($type_grading,'test_type_grading_marks')==0)){
         $not_response_is_zero = elgg_get_sticky_value('edit_test','not_response_is_zero');
      }

   }

   elgg_clear_sticky_form('edit_test');

   if (strcmp($max_duration_minutes,"")==0)
      $max_duration_minutes = 0;

   if (strcmp($num_random_questions,"")==0)
      $num_random_questions = 1;

   if (strcmp($mark_weight,"")==0)
      $mark_weight = 0;

   if (strcmp($question_max_game_points,"")==0)
      $question_max_game_points = 0;

   if (strcmp($max_attempts,"")==0)
      $max_attempts = 0;

   if (strcmp($opentime,"")==0)
      $opentime = "00:00";

   if (strcmp($closetime,"")==0)
      $closetime = "00:00";

   $options_activate=array();
   $options_activate[0]=elgg_echo('test:activate_now');
   $options_activate[1]=elgg_echo('test:activate_date');
   $op_activate=array();
   $op_activate[0]='test_activate_now';
   $op_activate[1]='test_activate_date';
   if (strcmp($option_activate_value,$op_activate[0])==0){
       $checked_radio_activate_0 = "checked = \"checked\"";
       $checked_radio_activate_1 = "";
       $style_display_activate = "display:none";
   } else {
      $checked_radio_activate_0 = "";
      $checked_radio_activate_1 = "checked = \"checked\"";
      $style_display_activate = "display:block";
   }
   $options_close=array();
   $options_close[0]=elgg_echo('test:not_close');
   $options_close[1]=elgg_echo('test:close_date');
   $op_close=array();
   $op_close[0]='test_not_close';
   $op_close[1]='test_close_date';
   if (strcmp($option_close_value,$op_close[0])==0){
       $checked_radio_close_0 = "checked = \"checked\"";
       $checked_radio_close_1 = "";
       $style_display_close = "display:none";
   } else {
      $checked_radio_close_0 = "";
      $checked_radio_close_1 = "checked = \"checked\"";
      $style_display_close = "display:block";
   }
   $opendate_label = elgg_echo('test:opendate');
   $closedate_label = elgg_echo('test:closedate');
   $opentime_label = elgg_echo('test:opentime');
   $closetime_label = elgg_echo('test:closetime');
 
   if ($count_responses>0) {
      $disabled = "disabled";
   } else {
      $disabled = "";
   }

   $max_duration_label = elgg_echo('test:max_duration_label');
   $max_duration_minutes_label = elgg_echo('test:minutes');

   $assessable_label = elgg_echo('test:assessable_label');
   if ($assessable){
      $selected_assessable = "checked = \"checked\"";
   } else {
      $selected_assessable = "";
   }

   $all_in_checkbox_label=elgg_echo('test:all_in_checkbox_text');
   $options_all_in_checkbox=array();
   $options_all_in_checkbox[0]=elgg_echo('test:all_in_checkbox_proporcional');
   $options_all_in_checkbox[1]=elgg_echo('test:all_in_checkbox_all_in');
   $op_all_in_checkbox=array();
   $op_all_in_checkbox[0]='proporcional';
   $op_all_in_checkbox[1]='all_in';
   if (strcmp($all_in_checkbox,$op_all_in_checkbox[0])==0){
      $checked_radio_all_in_checkbox_0 = "checked = \"checked\"";
      $checked_radio_all_in_checkbox_1 = "";
   }
   if (strcmp($all_in_checkbox,$op_all_in_checkbox[1])==0){
      $checked_radio_all_in_checkbox_0 = "";
      $checked_radio_all_in_checkbox_1 = "checked = \"checked\"";
   }
   $num_cancel_questions_label=elgg_echo('test:num_cancel_questions_text');

   if ((strcmp($num_cancel_questions,"0")==0)||(strcmp($num_cancel_questions,"")==0)){
      $style_display_num_cancel_questions = "display:none";
   } else {
      $style_display_num_cancel_questions = "display:block";
   }

   $penalty_not_response_label = elgg_echo('test:penalty_not_response_label');
   if ($penalty_not_response){
      $penalty_not_response = "checked = \"checked\"";
   } else {
      $penalty_not_response = "";
   }

   $not_response_is_zero_label = elgg_echo('test:not_response_is_zero_label');
   if ($not_response_is_zero){
      $selected_not_response_is_zero = "checked = \"checked\"";
   } else {
      $selected_not_response_is_zero = "";
   }

   $random_questions_label = elgg_echo('test:random_questions_label');
   $num_random_questions_label = elgg_echo('test:num_random_questions_label');
   if ($random_questions){
      $selected_random_questions = "checked = \"checked\"";
      $style_display_random_questions = "display:block";
   } else {
      $selected_random_questions = "";
      $style_display_random_questions = "display:none";
   }

   $type_grading_label=elgg_echo('test:type_grading_label');
   $options_type_grading=array();
   $options_type_grading[0]=elgg_echo('test:type_grading_marks');
   $options_type_grading[1]=elgg_echo('test:type_grading_game_points');
   $op_type_grading=array();
   $op_type_grading[0]='test_type_grading_marks';
   $op_type_grading[1]='test_type_grading_game_points';
   if (strcmp($type_grading,$op_type_grading[0])==0){
       $checked_radio_type_grading_0 = "checked = \"checked\"";
       $checked_radio_type_grading_1 = "";
       $style_display_type_grading = "display:block";
       $style_display_type_grading_2 = "display:none";
       $style_display_type_grading_3 = "display:block";
   } else {
      $checked_radio_type_grading_0 = "";
      $checked_radio_type_grading_1 = "checked = \"checked\"";
      $style_display_type_grading = "display:none";
      $style_display_type_grading_2 = "display:block";
      $style_display_type_grading_3 = "display:none";
   }
   
   $max_mark_label=elgg_echo('test:max_mark_label');
   $options_max_mark=array();
   $options_max_mark[0]=elgg_echo('10');
   $options_max_mark[1]=elgg_echo('100');
   $op_max_mark=array();
   $op_max_mark[0]='10';
   $op_max_mark[1]='100';
   if (strcmp($max_mark,$op_max_mark[0])==0){
      $checked_radio_max_mark_0 = "checked = \"checked\"";
      $checked_radio_max_mark_1 = "";
   }
   if (strcmp($max_mark,$op_max_mark[1])==0){
      $checked_radio_max_mark_0 = "";
      $checked_radio_max_mark_1 = "checked = \"checked\"";
   }
   $type_mark_label=elgg_echo('test:type_mark_label');
   $options_type_mark=array();
   $options_type_mark[0]=elgg_echo('test:type_mark_numerical');
   $options_type_mark[1]=elgg_echo('test:type_mark_textual');
   $options_type_mark[2]=elgg_echo('test:type_mark_apto');
   $op_type_mark=array();
   $op_type_mark[0]='test_type_mark_numerical';
   $op_type_mark[1]='test_type_mark_textual';
   $op_type_mark[2]='test_type_mark_apto';
   if (strcmp($type_mark,$op_type_mark[0])==0){
       $checked_radio_type_mark_0 = "checked = \"checked\"";
       $checked_radio_type_mark_1 = "";
       $checked_radio_type_mark_2 = "";
       $style_display_type_mark = "display:none";
   }
   if (strcmp($type_mark,$op_type_mark[1])==0){
      $checked_radio_type_mark_0 = "";
      $checked_radio_type_mark_1 = "checked = \"checked\"";
      $checked_radio_type_mark_2 = "";
      $style_display_type_mark = "display:none";
   }
   if (strcmp($type_mark,$op_type_mark[2])==0){
      $checked_radio_type_mark_0 = "";
      $checked_radio_type_mark_1 = "";
      $checked_radio_type_mark_2 = "checked = \"checked\"";
      $style_display_type_mark = "display:block";
   }
   $mark_weight_label=elgg_echo('test:mark_weight_label');
   $public_global_marks_label = elgg_echo('test:public_global_marks_label');
   if ($public_global_marks){
      $selected_public_global_marks = "checked = \"checked\"";
   } else {
      $selected_public_global_marks = "";
   }
   $question_max_game_points_label=elgg_echo('test:question_max_game_points_label');
   
   $type_max_attempts_label = elgg_echo('test:type_max_attempts_label');
   $options_type_max_attempts=array();
   $options_type_max_attempts[0]=elgg_echo('test:limited_attempts');
   $options_type_max_attempts[1]=elgg_echo('test:unlimited_attempts');
   $options_type_max_attempts[2]=elgg_echo('test:max_grading_limited_attempts');
   $op_type_max_attempts=array();
   $op_type_max_attempts[0]='test_limited_attempts';
   $op_type_max_attempts[1]='test_unlimited_attempts';
   $op_type_max_attempts[2]='test_max_grading_limited_attempts';
   if (strcmp($type_max_attempts,$op_type_max_attempts[0])==0){
       $checked_radio_type_max_attempts_0 = "checked = \"checked\"";
       $checked_radio_type_max_attempts_1 = "";
       $checked_radio_type_max_attempts_2 = "";
       $style_display_type_max_attempts = "display:block";
   }
   if (strcmp($type_max_attempts,$op_type_max_attempts[1])==0){
      $checked_radio_type_max_attempts_0 = "";
      $checked_radio_type_max_attempts_1 = "checked = \"checked\"";
      $checked_radio_type_max_attempts_2 = "";
      $style_display_type_max_attempts = "display:none";
   }
   if (strcmp($type_max_attempts,$op_type_max_attempts[2])==0){
      $checked_radio_type_max_attempts_0 = "";
      $checked_radio_type_max_attempts_1 = "";
      $checked_radio_type_max_attempts_2 = "checked = \"checked\"";
      $style_display_type_max_attempts = "display:none";
   }
   $max_attempts_label = elgg_echo('test:max_attempts_label');

   $correct_responses_visibility_label = elgg_echo('test:correct_responses_visibility_label');
   $options_correct_responses_visibility=array();
   $options_correct_responses_visibility[0]=elgg_echo('test:never_correct_responses_visibility');
   $options_correct_responses_visibility[1]=elgg_echo('test:after_attempts_correct_responses_visibility');
   $options_correct_responses_visibility[2]=elgg_echo('test:after_close_time_correct_responses_visibility');
   $op_correct_responses_visibility=array();
   $op_correct_responses_visibility[0]='test_never_correct_responses_visibility';
   $op_correct_responses_visibility[1]='test_after_attempts_correct_responses_visibility';
   $op_correct_responses_visibility[2]='test_after_close_time_correct_responses_visibility';
   
   if (strcmp($correct_responses_visibility,$op_correct_responses_visibility[0])==0){
       $checked_radio_correct_responses_visibility_0 = "checked = \"checked\"";
       $checked_radio_correct_responses_visibility_1 = "";
       $checked_radio_correct_responses_visibility_2 = "";
   }
   if (strcmp($correct_responses_visibility,$op_correct_responses_visibility[1])==0){
      $checked_radio_correct_responses_visibility_0 = "";
      $checked_radio_correct_responses_visibility_1 = "checked = \"checked\"";
      $checked_radio_correct_responses_visibility_2 = "";
   }
   if (strcmp($correct_responses_visibility,$op_correct_responses_visibility[2])==0){
      $checked_radio_correct_responses_visibility_0 = "";
      $checked_radio_correct_responses_visibility_1 = "";
      $checked_radio_correct_responses_visibility_2 = "checked = \"checked\"";
   }

   $feedback_label = elgg_echo('test:feedback_label');
   if (strcmp($feedback,"not_feedback")==0)
      $feedback="";
   $feedback_textbox = elgg_view('input/longtext', array('name' => 'feedback', 'value' => $feedback));

   $subgroups_label = elgg_echo('test:subgroups_label');
   if ($subgroups){
      $selected_subgroups = "checked = \"checked\"";
   } else {
      $selected_subgroups = "";
   }

   $tag_label = elgg_echo('tags');
   $tag_input = elgg_view('input/tags', array('name' => 'testtags', 'value' => $tags));   
   $access_label = elgg_echo('access');
   $access_input = elgg_view('input/access', array('name' => 'access_id', 'value' => $access_id));
       
   ?>

   <form action="<?php echo elgg_get_site_url()."action/".$action?>" name="edit_test" enctype="multipart/form-data" method="post">

   <?php echo elgg_view('input/securitytoken'); ?>

   <p>
   <b><?php echo elgg_echo("test:title_label"); ?></b><br>
   <?php echo elgg_view("input/text", array('name' => 'title', 'value' => $title)); ?>
   </p>
  
   <p>
   <b><?php echo elgg_echo("test:description_label"); ?></b><br>
   <?php echo elgg_view("input/longtext", array('name' => 'description', 'value' => $description)); ?>
   </p>

   <table class="test_dates_table">
   <tr>
   <td>
   <p>
   <b><?php echo elgg_echo('test:activate_label'); ?></b><br>
   <?php echo "<input type=\"radio\" name=\"option_activate_value\" value=$op_activate[0] $checked_radio_activate_0 onChange=\"test_show_activate_time()\">$options_activate[0]";?><br> 
   <?php echo "<input type=\"radio\" name=\"option_activate_value\" value=$op_activate[1] $checked_radio_activate_1 onChange=\"test_show_activate_time()\">$options_activate[1]";?><br> 
   <div id="resultsDiv_activate" style="<?php echo $style_display_activate;?>;"> 
      <?php echo $opendate_label; ?><br> 
      <?php echo elgg_view('input/date',array('timestamp'=>TRUE, 'autocomplete'=>'off','class'=>'test-compressed-date','name'=>'opendate','value'=>$opendate));  ?>
      <?php echo "<br>" . $opentime_label; ?> <br> 
      <?php echo "<input type = \"text\" name = \"opentime\" value = $opentime>"; ?>
   </div>
   </p><br>
   </td>
   <td>
   <p>
   <b><?php echo elgg_echo('test:close_label'); ?></b><br>
   <?php echo "<input type=\"radio\" name=\"option_close_value\" value=$op_close[0] $checked_radio_close_0 onChange=\"test_show_close_time()\">$options_close[0]";?><br> 
   <?php echo "<input type=\"radio\" name=\"option_close_value\" value=$op_close[1] $checked_radio_close_1 onChange=\"test_show_close_time()\">$options_close[1]";?><br>
   <div id="resultsDiv_close" style="<?php echo $style_display_close;?>;">
      <?php echo $closedate_label; ?><br> 
      <?php echo elgg_view('input/date',array('timestamp'=>TRUE, 'autocomplete'=>'off','class'=>'test-compressed-date','name'=>'closedate','value'=>$closedate)); ?>    
      <?php echo "<br>" . $closetime_label; ?> <br> 
      <?php echo "<input type = \"text\" name = \"closetime\" value = $closetime>"; ?>  
   </div>
   </p><br>
   </td>
   </tr>
   </table>

   <p>
   <b>
   <?php echo $max_duration_label; ?>
   </b><br>
   <?php echo $max_duration_minutes_label; ?>
   <br> 
   <?php echo "<input type = \"text\" $disabled name = \"max_duration_minutes\" value = $max_duration_minutes>"; ?>
   </p><br>

   <p>
   <b>
   <?php echo "<input type = \"checkbox\" name = \"assessable\" onChange=\"test_show_assessable()\" $selected_assessable> $assessable_label"; ?>
   </b>   
   </p><br>

   <p><b>
   <?php echo $all_in_checkbox_label; ?>
   </b><br>
   <?php echo "<input type=\"radio\" $disabled name=\"all_in_checkbox\" value=$op_all_in_checkbox[1] $checked_radio_all_in_checkbox_1 >$options_all_in_checkbox[1]"; ?><br> 
   <?php echo "<input type=\"radio\" $disabled name=\"all_in_checkbox\" value=$op_all_in_checkbox[0] $checked_radio_all_in_checkbox_0 >$options_all_in_checkbox[0]"; ?><br> 
   </p><br>

   <p>
   <?php
   $num_cancel_questions_array = array("0","1","2","3","4");
   if ($count_responses > 0) {
   ?>
   <select disabled name="num_cancel_questions" onchange="test_show_num_cancel_questions(this)">
   <?php
   } else {
   ?>
   <select name="num_cancel_questions" onchange="test_show_num_cancel_questions(this)">
   <?php
   }
      foreach ($num_cancel_questions_array as $one_number) {   
         ?>
         <option value="<?php echo $one_number; ?>" <?php if ($one_number==$num_cancel_questions) echo "selected=\"selected\""; ?>> <?php echo $one_number; ?> </option>
         <?php
      }
      ?>
   </select>
   &nbsp;
   <?php echo $num_cancel_questions_label;?>
   </p><br>

   <p>
   <div id="resultsDiv_num_cancel_questions" style="<?php echo $style_display_num_cancel_questions; ?>;">
   <b>
   <?php echo "<input type = \"checkbox\" $disabled name = \"penalty_not_response\" $selected_penalty_not_response> $penalty_not_response_label"; ?>
   </b> 
   </div>
   </p><br>


   <p>
   <b>
   <?php echo "<input type = \"checkbox\" $disabled name = \"random_questions\" onChange=\"test_show_random_questions()\" $selected_random_questions> $random_questions_label"; ?>
   </b>
   </p>
   <p>
   <div id="resultsDiv_random_questions" style="<?php echo $style_display_random_questions; ?>;">
   <b>
   <?php echo $num_random_questions_label; ?>
   </b><br> 
   <?php echo "<input type = \"text\" $disabled name = \"num_random_questions\" value = $num_random_questions>"; ?>
   </b>
   </div>
   </p><br>

   <p>
   <b>
   <?php echo $type_grading_label; ?>
   </b><br>
   <?php echo "<input type=\"radio\" $disabled name=\"type_grading\" value=$op_type_grading[0] $checked_radio_type_grading_0 onChange=\"test_show_type_grading()\">$options_type_grading[0]"; ?><br> 
   <?php echo "<input type=\"radio\" $disabled name=\"type_grading\" value=$op_type_grading[1] $checked_radio_type_grading_1 onChange=\"test_show_type_grading()\">$options_type_grading[1]"; ?><br> 
   </p>

   <div id="resultsDiv_type_grading" style="<?php echo $style_display_type_grading; ?>;">
      <div id="resultsDiv_assessable" style="<?php echo $style_display_assessable;?>;">
         <div id="resultsDiv_type_grading_3" style="<?php echo $style_display_type_grading_3;?>;">  
            <p>
            <b>
            <?php echo "<input type = \"checkbox\" name = \"not_response_is_zero\" $selected_not_response_is_zero> $not_response_is_zero_label"; ?>
            </b> 
            </p>
         </div>
      </div>
      <br><p>
      <b>
      <?php echo $max_mark_label; ?>
      </b><br>
      <?php echo "<input type=\"radio\" $disabled name=\"max_mark\" value=$op_max_mark[0] $checked_radio_max_mark_0>$options_max_mark[0]"; ?><br> 
      <?php echo "<input type=\"radio\" $disabled name=\"max_mark\" value=$op_max_mark[1] $checked_radio_max_mark_1>$options_max_mark[1]"; ?><br>      
      </p>
      <p>
      <b>
      <?php echo $type_mark_label; ?>
      </b><br>
      <?php echo "<input type=\"radio\" $disabled name=\"type_mark\" value=$op_type_mark[0] $checked_radio_type_mark_0>$options_type_mark[0]"; ?><br> 
      <?php echo "<input type=\"radio\" $disabled name=\"type_mark\" value=$op_type_mark[1] $checked_radio_type_mark_1>$options_type_mark[1]"; ?><br> 
      <?php echo "<input type=\"radio\" $disabled name=\"type_mark\" value=$op_type_mark[2] $checked_radio_type_mark_2>$options_type_mark[2]"; ?><br> 
      </p>
      <p>
      <b><?php echo $mark_weight_label; ?></b> 
      <?php echo "<input type = \"text\" name = \"mark_weight\" value = $mark_weight>"; ?>   
      </p>
      <p>
      <b>
      <?php echo "<input type = \"checkbox\" name = \"public_global_marks\" $selected_public_global_marks> $public_global_marks_label"; ?> 
      </b>
      </p><br>
   </div>
   <div id="resultsDiv_type_grading_2" style="<?php echo $style_display_type_grading_2;?>;">
      <p>
      <b><?php echo $question_max_game_points_label; ?></b> 
      <?php echo "<input type = \"text\" $disabled name = \"question_max_game_points\" value = $question_max_game_points>"; ?>   
      </p><br>
   </div>

   <p>
   <b>
   <?php echo $type_max_attempts_label; ?>
   </b><br />
   <?php echo "<input type=\"radio\" $disabled name=\"type_max_attempts\" value=$op_type_max_attempts[0] $checked_radio_type_max_attempts_0 onChange=\"test_show_type_max_attempts(0)\">$options_type_max_attempts[0]"; ?><br> 
   <?php echo "<input type=\"radio\" $disabled name=\"type_max_attempts\" value=$op_type_max_attempts[1] $checked_radio_type_max_attempts_1 onChange=\"test_show_type_max_attempts(1)\">$options_type_max_attempts[1]"; ?><br> 
   <?php echo "<input type=\"radio\" $disabled name=\"type_max_attempts\" value=$op_type_max_attempts[2] $checked_radio_type_max_attempts_2 onChange=\"test_show_type_max_attempts(2)\">$options_type_max_attempts[2]"; ?><br> 
   </p>
   <div id="resultsDiv_type_max_attempts" style="<?php echo $style_display_type_max_attempts;?>;">
      <p>
      <b><?php echo $max_attempts_label; ?></b> 
      <?php echo "<input type = \"text\" $disabled name = \"max_attempts\" value = $max_attempts>"; ?>   
      </p><br>
   </div>
		
   <p>
   <b>
   <?php echo $correct_responses_visibility_label; ?>
   </b><br />
   <?php echo "<input type=\"radio\" $disabled name=\"correct_responses_visibility\" value=$op_correct_responses_visibility[0] $checked_radio_correct_responses_visibility_0>$options_correct_responses_visibility[0]"; ?><br> 
   <?php echo "<input type=\"radio\" $disabled name=\"correct_responses_visibility\" value=$op_correct_responses_visibility[1] $checked_radio_correct_responses_visibility_1>$options_correct_responses_visibility[1]"; ?><br> 
   <?php echo "<input type=\"radio\" $disabled name=\"correct_responses_visibility\" value=$op_correct_responses_visibility[2] $checked_radio_correct_responses_visibility_2>$options_correct_responses_visibility[2]"; ?><br>
   </p><br>

   <p>
   <b><?php echo $feedback_label; ?></b>
   <?php echo $feedback_textbox; ?>
   </p><br>
   <?php $container = $test->getContainerEntity();
         if (!($container->getContainerEntity() instanceof ElggGroup)){ ?>
   <p>
   <b>
   <?php echo "<input type = \"checkbox\" $disabled name = \"subgroups\" $selected_subgroups> $subgroups_label"; ?>
   </b> 
   </p><br>
   <?php } ?>
   <p>
   <b><?php echo $tag_label; ?></b><br />
   <?php echo $tag_input; ?>
   </p><br>
   <p>
   <b><?php echo $access_label; ?></b><br />
   <?php echo $access_input; ?>
   </p>	

   <?php	        

   $submit_input_save = elgg_view('input/submit', array('name' => 'submit', 'value' => elgg_echo("test:save")));
   echo $submit_input_save;
   
   ?>
   <input type="hidden" name="testpost" value="<?php echo $testpost; ?>">

</form>

<?php
}
?>

<script language="javascript">
   function test_show_activate_time(){
      var resultsDiv_activate = document.getElementById('resultsDiv_activate');
      if (resultsDiv_activate.style.display == 'none'){
         resultsDiv_activate.style.display = 'block';
      } else {       
         resultsDiv_activate.style.display = 'none';
      }
   }   
   function test_show_close_time(){
      var resultsDiv_close = document.getElementById('resultsDiv_close');
      if (resultsDiv_close.style.display == 'none'){
            resultsDiv_close.style.display = 'block';
      } else {       
         resultsDiv_close.style.display = 'none';
      }
   }  
   function test_show_num_cancel_questions(select){
      var number = select.options[select.selectedIndex].value;
      if (number == "0") {
         resultsDiv_num_cancel_questions.style.display = 'none';
      } else {
         resultsDiv_num_cancel_questions.style.display = 'block';
      }
   }  
   function test_show_random_questions(){
      var resultsDiv_random_questions = document.getElementById('resultsDiv_random_questions');    
      
      if (resultsDiv_random_questions.style.display == 'none'){
         resultsDiv_random_questions.style.display = 'block';
      } else {       
         resultsDiv_random_questions.style.display = 'none';
      }
   }   
   function test_show_type_grading(){
      var resultsDiv_type_grading = document.getElementById('resultsDiv_type_grading');
      var resultsDiv_type_grading_2 = document.getElementById('resultsDiv_type_grading_2');
      var resultsDiv_type_grading_3 = document.getElementById('resultsDiv_type_grading_3');
      if (resultsDiv_type_grading.style.display == 'none'){
         resultsDiv_type_grading.style.display = 'block';
	 resultsDiv_type_grading_2.style.display = 'none';
	 resultsDiv_type_grading_3.style.display = 'block';
      } else {       
         resultsDiv_type_grading.style.display = 'none';
	 resultsDiv_type_grading_2.style.display = 'block';
	 resultsDiv_type_grading_3.style.display = 'none';
      }
   }  
   function test_show_assessable(){
      var resultsDiv_assessable = document.getElementById('resultsDiv_assessable');    
      
      if (resultsDiv_assessable.style.display == 'none'){
         resultsDiv_assessable.style.display = 'block';
      } else {       
         resultsDiv_assessable.style.display = 'none';
      }
   }   
   function test_show_type_max_attempts(type_max_attempts_id){
      var resultsDiv_type_max_attempts = document.getElementById('resultsDiv_type_max_attempts');    
      
      if (resultsDiv_type_max_attempts.style.display == 'none'){
         if (type_max_attempts_id == 0) 
            resultsDiv_type_max_attempts.style.display = 'block';
      } else {       
         resultsDiv_type_max_attempts.style.display = 'none';
      }
   }                
</script>

</div>


