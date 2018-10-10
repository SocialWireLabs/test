<div class="contentWrapper">
<?php

if (isset($vars['entity'])){

   $testpost=$vars['entity']->getGUID();
   $test=$vars['entity'];
   $action = "test/answer";

   if (isset($vars['index'])){
      $index=$vars['index'];
   }
   else{
      $index="none";
   }

   $user_guid = $vars['user_guid'];
   $user = get_entity($user_guid);
   $container_guid  = $test->container_guid;
   $container = get_entity($container_guid);

   //Questions
   $options = array('relationship' => 'test_question', 'relationship_guid' => $testpost,'inverse_relationship' => false, 'type' => 'object', 'subtype' => 'test_question','limit'=>0);
   $questions=elgg_get_entities_from_relationship($options);

   if (!empty($questions)) {
      $only_simple_choice = true;
      foreach($questions as $one_question) {
         if (strcmp($one_question->response_type,'radiobutton')!=0){
	    $only_simple_choice = false;
	    break;
	 }
      }
   }

 
   if ($test->subgroups){
      $user_subgroup = elgg_get_entities_from_relationship(array('type_subtype_pairs' => array('group' => 'lbr_subgroup'),'container_guids' => $container_guid,'relationship' => 'member','inverse_relationship' => false,'relationship_guid' => $user_guid));
      $user_subgroup_guid=$user_subgroup[0]->getGUID();
   }

   //Answers
   if (!$test->subgroups){
      $options = array('relationship' => 'test_answer_draft', 'relationship_guid' => $testpost,'inverse_relationship' => false, 'type' => 'object', 'subtype' => 'test_answer_draft', 'order_by' => 'e.time_created desc', 'limit' => 0, 'owner_guid' => $user_guid);
   } else {
       $options = array('relationship' => 'test_answer_draft', 'relationship_guid' => $testpost,'inverse_relationship' => false, 'type' => 'object', 'subtype' => 'test_answer_draft', 'order_by' => 'e.time_created desc', 'limit' => 0, 'container_guid' => $user_subgroup_guid);
   }
   $user_responses_draft=elgg_get_entities_from_relationship($options);
   $user_response_draft=$user_responses_draft[0];
   $user_response_draft_content_array = explode(Chr(27),$user_response_draft->content);
   $user_response_draft_content_array = array_map('trim', $user_response_draft_content_array);
   if ($user_response_draft->first_time == true && $index!="none"){
      $user_response_draft->answer_beginning_time = time();
      $user_response_draft->first_time = false;
   }
   $answer_beginning_time=$user_response_draft->answer_beginning_time;
   $num_questions = $user_response_draft->num_questions;

   if (!$test->subgroups){
      $options = array('relationship' => 'test_answer', 'relationship_guid' => $testpost,'inverse_relationship' => false, 'type' => 'object', 'subtype' => 'test_answer', 'order_by' => 'e.time_created desc', 'limit' => 0, 'owner_guid' => $user_guid);
   } else {
      $options = array('relationship' => 'test_answer', 'relationship_guid' => $testpost,'inverse_relationship' => false, 'type' => 'object', 'subtype' => 'test_answer', 'order_by' => 'e.time_created desc', 'limit' => 0, 'container_guid' => $user_subgroup_guid);
   }
   $user_responses=elgg_get_entities_from_relationship($options);
   if (!empty($user_responses)){
      $user_response=$user_responses[0];
      $user_response_content_array = explode(Chr(27),$user_response->content);
      $user_response_content_array = array_map('trim', $user_response_content_array);
      $attempts=$user_response->attempts;
   } else {
      $user_response="";
      $attempts=0;
   }

   if ($test->random_questions){
      $selected_random_questions = explode(";",$user_response_draft->selected_random_questions);
      $selected_question_index = $selected_random_questions[$index];
   } else {
      $selected_question_index = $index;
   }


   //Question
   $options = array('relationship' => 'test_question', 'relationship_guid' => $testpost,'inverse_relationship' => false, 'type' => 'object', 'subtype' => 'test_question','metadata_name_value_pairs' => array('name' => 'index', 'value' => $selected_question_index));
   $questions=elgg_get_entities_from_relationship($options);
   $one_question=$questions[0];


   if ($index!="none"){

   ///////////////////////////////////////////////////////////////////
   //Question

   $question=$one_question->question;
   $question_text = "<div class=\"test_question_frame\">";
   $question_text .= elgg_view('output/text', array('value' => $question));
   $question_text .= "</div>";

   //Question body

   $question_body ="";
   if (strcmp($one_question->question_html,"")!=0){
      $question_body .= "<p>" . "<b>" . elgg_echo('test:question_simple_read') . "</b>" . "</p>";
      $question_body .= "<div class=\"test_question_frame\">";
      $question_body .= elgg_view('output/longtext', array('value' => $one_question->question_html));
      $question_body .= "</div>";
      if (strcmp($one_question->question_type,"simple")!=0)
         $question_body .= "<br>";
   }
   switch ($one_question->question_type) {
      case 'urls_files':
         $question_body .= "<p>" . "<b>" . elgg_echo('test:question_urls_files_read') . "</b>" . "</p>";
         $question_body .= "<div class=\"test_question_frame\">";
         $question_urls = explode(Chr(26),$one_question->question_urls);
         $question_urls = array_map('trim',$question_urls);
         $files = elgg_get_entities_from_relationship(array('relationship' => 'question_file_link','relationship_guid' => $one_question->getGUID(),'inverse_relationship' => false,'type' => 'object','subtype' => 'test_question_file','limit'=>0));
         if ((count($question_urls)>0)&&(strcmp($question_urls[0],"")!=0)) {
	    foreach ($question_urls as $one_url){
	       $comp_url = explode(Chr(24),$one_url);
	       $comp_url = array_map('trim',$comp_url);
	       $url_name = $comp_url[0];
               $url_value = $comp_url[1];
               if (elgg_is_active_plugin("sw_embedlycards")){
                       $question_body .= "<div>
                       <a class='embedly-card' href='$url_value'></a>
                       </div>";
                } else if (elgg_is_active_plugin("hypeScraper"))
                    $question_body .= elgg_view('output/sw_url_preview', array('value' => $url_value,));
                else
                    $question_body .= "<a rel=\"nofollow\" href=\"$url_value\" target=\"_blank\">$url_name</a></br>";
	    }
         }

         if ((count($files)>0)&&(strcmp($files[0]->title,"")!=0)){
	   foreach($files as $one_file) {
              $params = $one_file->getGUID() . "_question";
              $icon = questions_set_icon_url($one_file, "small");
              $url_file = elgg_get_site_url()."mod/test/download.php?params=$params";
              $trozos = explode(".", $one_file->title);
              $ext = strtolower(end($trozos));
              if (($ext == 'jpg') || ($ext == 'png') || ($ext == 'gif') || ($ext == 'tif') || ($ext == 'tiff') || ($ext =='jpeg'))
                $question_body .= "<p align=\"center\"><a href=\"".$url_file."\">"."<img src=\"" . $url_file . "\" width=\"600px\">"."</a></p>";
              else
                $question_body .= "<p><a href=\"".$url_file."\">"."<img src=\"" . elgg_get_site_url(). $icon . "\">".$one_file->title."</a></p>";
              }
         }
         $question_body .= "</div>";
         break;
   }
  
   $response_type=$one_question->response_type;
   $question_grading=$one_question->grading;

   switch ($response_type) {
      case 'radiobutton':
      case 'checkbox':
         $responses=$one_question->responses;
         break;
      
      case 'grid':
         $responses_rows=$one_question->responses_rows;
         $responses_columns=$one_question->responses_columns;
         break;

      case 'pairs':
         $responses_left=$one_question->responses_left;
         $responses_right=$one_question->responses_right;
         break;
      case 'dropdown':
         $responses_dropdown = $one_question->responses_dropdown;
         $text_dropdown = $one_question->question_text;
         $numbers_responses_dropdowns = $one_question->numbers_responses_dropdowns;
         break;
   }


   ///////////////////////////////////////////////////////////////////
   //Responses
   switch ($response_type) {
      case 'radiobutton':
      case 'checkbox':
         $response_inputs = "";
         $responses_array = explode(Chr(26),$responses);
         $responses_array = array_map('trim', $responses_array);
         $this_responses=$user_response_draft_content_array[2*$index];

         if (strcmp($response_type,"checkbox")==0){
            $this_responses = explode(Chr(26),$this_responses);
            $this_responses = array_map('trim', $this_responses);
            $response_inputs .= elgg_view('input/checkboxes', array('name' => 'response','options' => $responses_array, 'value' => $this_responses));
         } else {
            $response_inputs .= elgg_view('input/radio', array('onclick' => "test_clear_radiobutton(this,'$responses')", 'name' => 'response','options' => $responses_array, 'value' => $this_responses));
	     foreach ($responses_array as $one_response) {
	       $name_hidden = 'hidden_'.$one_response;
	       if (strcmp($one_response,$this_responses)==0)
	          $response_inputs .= elgg_view('input/hidden',array('name' => $name_hidden,'value'=>'true'));
	       else
	          $response_inputs .= elgg_view('input/hidden',array('name' => $name_hidden,'value'=>'false'));	       
	    }
	   
         }
         break;
      case 'grid':
         $j = 0;
         $grid_response_inputs = array();
         $responses_rows_array = explode(Chr(26), $responses_rows);
         $responses_rows_array = array_map('trim', $responses_rows_array);
         $responses_columns_array = explode(Chr(26), $responses_columns);
         $responses_columns_array = array_map('trim', $responses_columns_array);
         foreach ($responses_rows_array as $one_row) {
            $grid_response_inputs[$j] = "";
            $name_response = "grid_response_" . $j;
            $this_responses = "";
            $this_responses = explode(Chr(26), $user_response_draft_content_array[2*$index]);
            $this_responses = array_map('trim', $this_responses);
            $this_responses = $this_responses[$j];
            $grid_response_inputs[$j] .= elgg_view('input/radio', array('onclick' => "test_clear_grid('$j',this,'$responses_columns')",'name' => $name_response, 'options' => $responses_columns_array, 'value' => $this_responses));
	    foreach ($responses_columns_array as $one_response) {
	       $name_hidden = $j.'_hidden_'.$one_response;
	       if (strcmp($one_response,$this_responses)==0)
	          $grid_response_inputs[$j] .= elgg_view('input/hidden',array('name' => $name_hidden,'value'=>'true'));
	       else
	          $grid_response_inputs[$j] .= elgg_view('input/hidden',array('name' => $name_hidden,'value'=>'false'));	       
	    }
            $j = $j + 1;
         }
         break;
      case 'pairs':
         $user_response_draft_content_array=explode(Chr(26), $user_response_draft_content_array[$index]);
         $responses_left_array = explode(Chr(26), $responses_left);
         $responses_left_array = array_map('trim', $responses_left_array);
         $responses_right_array = explode(Chr(26), $responses_right);
         $responses_right_array = array_map('trim', $responses_right_array);
         break;
      case 'dropdown':
         $user_response_draft_content_array=explode(Chr(26), $user_response_draft_content_array[$index]);
         $responses_dropdown_array = explode(Chr(26),$responses_dropdown);
         $numbers_responses_dropdowns_array = explode(",", $numbers_responses_dropdowns);
         break;
   }

   ////////////////////////////////////////////////////////////////////
   //Comments
   $comments=$user_response_draft->comments;
   if (strcmp($comments,"not_comments")==0)
      $comments="";
   $comments_body=elgg_view('input/longtext', array('name' => 'comments', 'value' => $comments));

   ///////////////////////////////////////////////////////////////////
   //Previous information
   } else {

   ?>
   <div class="test_frame">
      <?php
      if (strcmp($test->description,"")!=0){
         ?>
         <p>
         <b><?php echo elgg_echo('test:test_description_label'); ?></b>
         </p>
         <div class="test_question_frame">
            <?php echo elgg_view('output/longtext', array('value' => $test->description)); ?>
         </div>
         <br>
         <?php
      }

      //General comments
      $num_comments =  $test->countComments();
      if ($num_comments>0)
         $test_general_comments_label = elgg_echo('test:general_comments') . " (" . $num_comments . ")";
      else
         $test_general_comments_label = elgg_echo('test:general_comments');
      ?>
      <p align="left"><a onclick="test_show_general_comments();" style="cursor:hand;"><?php echo $test_general_comments_label; ?></a></p>
      <div id="commentsDiv" style="display:none;">
         <?php echo elgg_view_comments($test);?>
      </div>

      <?php
      if (!empty($user_response)){
         if (strcmp($test->type_grading,'test_type_grading_marks')==0){
            $response_grading = number_format($user_response->grading,2);
            $grading_text = '<b>' .elgg_echo('test:last_mark') . ": " . '</b>' . $response_grading;
         } else {
            $response_grading = $user_response->grading;
            $grading_text = '<b>' .elgg_echo('test:last_game_points') . ": " . '</b>' . $response_grading;
         }
	 ?>
         <p><?php echo $grading_text; ?></p>
	 <?php
      }

      if ($test->max_duration_minutes == 999999999){
         $max_duration_text = '<b>' . elgg_echo('test:max_duration_label' ) . ": " . '</b>' . elgg_echo('test:unlimited');
      } else{
         $max_duration_text = '<b>' . elgg_echo('test:max_duration_label') . ": " . '</b>' . $test->max_duration_minutes . "'";
      }

      if (strcmp($test->type_grading,'test_type_grading_marks')==0){
         $max_grading=$test->max_mark;
         $max_grading_text = '<b>' . elgg_echo('test:max_grading_label') . ": " . '</b>' . $max_grading;
      } else {
         $max_grading=$test->question_max_game_points;
         $max_grading_text = '<b>' . elgg_echo('test:max_game_points') . ": " . '</b>' . $max_grading;
      }

      $num_questions_text = '<b>' . elgg_echo('test:num_questions_label') . " " . '</b>' . $num_questions;

      if (strcmp($test->num_cancel_questions,'0')==0){
         $num_cancel_questions_text = '<b>' . elgg_echo('test:penalty_no').  '</b>';
      } else{
         $num_cancel_questions_text = '<b>' . $test->num_cancel_questions . " " . elgg_echo('test:penalty_yes') . '</b>';
          if ($test->penalty_not_response){
             $penalty_not_response_text = '<b>' . elgg_echo('test:penalty_not_response_yes') . '</b>';
	 } else {
             $penalty_not_response_text = '<b>' . elgg_echo('test:penalty_not_response_no') . '</b>';
	 }
      }

      if (!$only_simple_choice) {
         if (strcmp($test->all_in_checkbox,'proporcional')==0){
            $all_in_checkbox_text = '<b>' . elgg_echo('test:proporcional_info') . '</b>';
         } else {
            $all_in_checkbox_text = '<b>' . elgg_echo('test:all_in_info')  . '</b>';
         }
      }

      if (!empty($user_response)){
         $attempts = $user_response->attempts;
      } else {
         $attempts = 0;
      }
      switch($test->type_max_attempts){
         case 'test_unlimited_attempts':
            $attempts_text = '<b>' . elgg_echo('test:attempts') . ": " . '</b>' . $attempts . '<b>' . " (" . elgg_echo('test:attempts_unlimited') . ")" . elgg_echo('test:mark:warning') . '</b>';
            break;
         case 'test_limited_attempts':
	  $attempts_text = "<b>" . elgg_echo('test:attempts') . ": " . '</b>' . $attempts . '<b>' . " (" . elgg_echo('test:max_attempts') . ": " . $test->max_attempts . ")" . elgg_echo('test:mark:warning') . '</b>';
            break;
         case 'test_max_grading_limited_attempts':
            $attempts_text = '<b>' . elgg_echo('test:attempts') . ": " . '</b>' . $attempts . '<b>' . " (" . elgg_echo('test:attempts_limited_max_grading') . ")" . elgg_echo('test:mark:warning') . '</b>';
            break;
      }
      ?>

      <p><?php echo $max_duration_text; ?></p>
      <p><?php echo $max_grading_text; ?></p>
      <p><?php echo $num_questions_text; ?></p>
      <p><?php echo $num_cancel_questions_text; ?></p>
      <?php
      if (strcmp($test->num_cancel_questions,'0')!=0){
      ?>
         <p><?php echo $penalty_not_response_text; ?></p>
      <?php
      }
      ?>
      <p><?php echo $all_in_checkbox_text; ?></p>
      <p><?php echo $attempts_text; ?></p>

   </div>
   <br>

   <?php
  
   }
   
   if ($index != "none") {
      $friendly_answer_beginning_time=date("d/m/Y",$answer_beginning_time) . " " . elgg_echo("test:at") . " " . date("G:i",$answer_beginning_time);
      $answer_beginning_time_text = '<b>' . elgg_echo('test:start_time') . ": " . '</b>' . $friendly_answer_beginning_time;
      $answer_end_time = $answer_beginning_time + $test->max_duration_minutes*60;
      $friendly_answer_end_time = date("d/m/Y",$answer_end_time) . " " . elgg_echo("test:at") . " " . date("G:i",$answer_end_time);
      $answer_end_time_text = '&nbsp;<b>' . elgg_echo('test:finish_time') . ": " . '</b>' . $friendly_answer_end_time;
   ?>

   <form action="<?php echo elgg_get_site_url()."action/".$action?>" name="answer_test" enctype="multipart/form-data" method="post">

   <?php
   echo elgg_view('input/securitytoken');


   ///////////////////////////////////////////////////////////////////////
   //Body
   if ($test->max_duration_minutes == 999999999){
         echo "<p> $answer_beginning_time_text </p>";
   } else{
         echo "<p> $answer_beginning_time_text $answer_end_time_text </p>";
   }


   $num_question=$index+1;
   switch ($response_type) {
      case 'radiobutton':
      case 'checkbox':
         echo elgg_view("forms/test/answer_question",array('test'=>$test,'num_question'=>$num_question,'question_text'=>$question_text,'question_body'=>$question_body,'question_grading'=>$question_grading,'response_type' => $response_type,'response_inputs'=>$response_inputs,'comments_body'=>$comments_body));
         break;
      
      case 'grid':
         echo elgg_view("forms/test/answer_question", array('test' => $test, 'num_question' => $num_question, 'question_text' => $question_text, 'question_body' => $question_body, 'question_grading' => $question_grading, 'response_type' => $response_type, 'responses_rows_array' => $responses_rows_array, 'grid_response_inputs' => $grid_response_inputs, 'comments_body' => $comments_body));
         break;

      case 'pairs':
         echo elgg_view("forms/test/answer_question", array('test' => $test, 'num_question' => $num_question, 'question_text' => $question_text, 'question_body' => $question_body, 'question_grading' => $question_grading, 'response_type' => $response_type, 'responses_left_array' => $responses_left_array, 'responses_right_array' => $responses_right_array, 'comments_body' => $comments_body, 'index' => $index, 'user_response_draft' => $user_response_draft->content));
         break;
      case 'dropdown':
         echo elgg_view("forms/test/answer_question", array('test' => $test, 'num_question' => $num_question, 'question_text' => $question_text, 'question_body' => $question_body, 'question_grading' => $question_grading, 'response_type' => $response_type, 'responses_dropdown_array' => $responses_dropdown_array, 'text_dropdown' => $text_dropdown, 'numbers_responses_dropdowns_array' => $numbers_responses_dropdowns_array, 'comments_body' => $comments_body, 'index' => $index, 'user_response_draft' => $user_response_draft->content));
         break;
   }

   ///////////////////////////////////////////////////////////////////////
   //Submit
   $end_confirm_msg = elgg_echo('test:end_confirm');
   $test_answer = elgg_echo('test:answer');
   $test_answer_end = elgg_echo('test:answer_end');
   $test_answer_next = elgg_echo('test:answer_next');
   $test_answer_previous = elgg_echo('test:answer_previous');
   $submit_input_answer = elgg_view('input/submit', array('class'=>'elgg-button elgg-button-special','style'=> 'float:right','name' => 'submit', 'value' => $test_answer, 'onclick' => "return confirm('$end_confirm_msg')"));
   $submit_input_answer_end = elgg_view('input/submit', array('class'=>'elgg-button elgg-button-special','style'=> 'float:right','name' => 'submit', 'value' => $test_answer_end, 'onclick' => "return confirm('$end_confirm_msg'))"));
   $submit_input_answer_next = elgg_view('input/submit', array('name' => 'submit', 'value' => $test_answer_next));
   $submit_input_answer_previous = elgg_view('input/submit', array('name' => 'submit', 'value' => $test_answer_previous));

   $entity_hidden = elgg_view('input/hidden', array('name' => 'testpost', 'value' => $testpost));
   $entity_hidden .= elgg_view('input/hidden', array('name' => 'user_guid', 'value'=> $user_guid));
   $entity_hidden .=  elgg_view('input/hidden', array('name' => 'index', 'value'=> $index));

   if ($num_questions==1){
      ?>
      <p><?php echo "$submit_input_answer $entity_hidden"; ?></p>
      <?php
   } else {

      $link_question_go = "&nbsp;&nbsp;<a onclick=\"javascript:check_question_number_and_go(".$testpost.",".$user_guid.",".$num_questions.");return true; \">".elgg_echo('test:go_to_question').":"."</a>";

      $box_question_go = "&nbsp;<input type=\"text\" name=\"number_question_go\" value=\"" . $number_question_go . "\" style=\"width: 80px\"/>";

      if ($index==0){
         ?>
         <p><?php echo "$submit_input_answer_next $submit_input_answer_end $entity_hidden $link_question_go $box_question_go"; ?></p>
	 <?php
      } else {
         if ($index==($num_questions-1)) {
	    ?>
	    <p><?php echo "$submit_input_answer_previous $submit_input_answer_end $entity_hidden $link_question_go $box_question_go"; ?></p>
	    <?php
         } else {
	    ?>
            <p><?php echo "$submit_input_answer_previous $submit_input_answer_next $submit_input_answer_end $entity_hidden $link_question_go $box_question_go"; ?></p>
            <?php
	 }
      }
   }
   ?>

   </form>

<?php

} else{
      $user_guid = elgg_get_logged_in_user_guid();
      $start_button_text = elgg_echo('test:start');
      $start_button_link = elgg_get_site_url() . 'test/view/'. $testpost . '/?index=0&this_user_guid_answer=' . $user_guid;
      $start_button = elgg_view('input/button', array('name' => 'return', 'class' => 'elgg-button-submit', 'value' => $start_button_text));
      $start_button = "<a href=" . $start_button_link . ">" . $start_button. "</a>";
      echo "$start_button";
   } 
}

?>
</div>

<script type="text/javascript">
  
   function test_clear_radiobutton(select,responses_string){
      var pref = 'hidden_';
      var suf = select.value;
      var name_hidden = pref.concat(suf);
      var hidden_input = document.getElementsByName(name_hidden);
      if (hidden_input.item(0).value == 'true'){
         hidden_input.item(0).value = 'false';
	 select.checked = false;
      } else { 
	 hidden_input.item(0).value = 'true';
	 responses_array = responses_string.split(String.fromCharCode(26));
	 for (var i=0; i<responses_array.length; i++) {
	    if(responses_array[i]!=select.value) {
	       var name_other_hidden = pref.concat(responses_array[i]);
	       var other_hidden_input = document.getElementsByName(name_other_hidden);
	       other_hidden_input.item(0).value = 'false';
	    }
	 }
	 select.checked = true;
      }
   }

   function test_clear_grid(j,select,responses_string){
      var pref1 = j;
      var pref2 = '_hidden_';
      var pref = pref1.concat(pref2);
      var suf = select.value;
      var name_hidden = pref.concat(suf);
      var hidden_input = document.getElementsByName(name_hidden);
      if (hidden_input.item(0).value == 'true'){
         hidden_input.item(0).value = 'false';
	 select.checked = false;
      } else { 
	 hidden_input.item(0).value = 'true';
	 responses_array = responses_string.split(String.fromCharCode(26));
	 for (var i=0; i<responses_array.length; i++) {
	    if(responses_array[i]!=select.value) {
	       var name_other_hidden = pref.concat(responses_array[i]);
	       var other_hidden_input = document.getElementsByName(name_other_hidden);
	       other_hidden_input.item(0).value = 'false';
	    }
	 }
	 select.checked = true;
      }
   }

   function test_show_general_comments(){
      var commentsDiv = document.getElementById('commentsDiv');
      if (commentsDiv.style.display == 'none'){
         commentsDiv.style.display = 'block';
      } else {
         commentsDiv.style.display = 'none';
      }
   }
   function test_check_time(answer_end_time){
      var now = new Date();
      var now_time = now.getTime();
      var dif_time = (answer_end_time-now_time)/60000.0;
      return dif_time;
   }
   function check_question_number_and_go(testpost,user_guid,num_questions){
      var name = "number_question_go";
      var number_question_go = document.getElementsByName(name).item(0).value - 1;
      if (isNaN(number_question_go) || number_question_go<0 || number_question_go==undefined || number_question_go>=num_questions){
         alert("<?php echo elgg_echo('test:error');?>");
      } else {
         var url1 = "<?php echo elgg_get_site_url(); ?>test/view/";
         var url2 = "/?index=";
         var url3 = "&this_user_guid_answer=";
         var url = url1.concat(testpost,url2,number_question_go,url3,user_guid);
         window.location.href = url;
      }
    }

   function pintaFondo(id,nRespuestas){
      var cadenas=document.getElementById("respuestas").value.split(",");
      var i=0,j=0;
      var bloque;
      var iDiv;
      var ta;
      var ultimoValor;
      var valorActual;
      var valorSalto=-1;
      var valorSaltoAnterior=-1;
      var text;
      var left_text=new Array(nRespuestas/2);
      var right_text=new Array(nRespuestas/2);
      var longitud=document.getElementById("respuestas").value.length;
      var respuestasOrdenadas;
      var primerValorPar=false;
      var responses_left=new Array(nRespuestas/2);
      var num_responses_left=0;
      var responses_right=new Array(nRespuestas/2);
      var num_responses_right=0;
      var responses_tem="";
      if(document.getElementById("respuestasOrdenadas").value==""){
         i=1;
         while(i<=nRespuestas){
            if(i==1){
               document.getElementById("respuestasOrdenadas").value=i;
               document.getElementById("respuestasOrdenadas").value+=","+(i+1);
            }
            else{
               document.getElementById("respuestasOrdenadas").value+=","+i;
               document.getElementById("respuestasOrdenadas").value+=","+(i+1);  
            }
            i+=2;
         }
      }
      respuestasOrdenadas=document.getElementById("respuestasOrdenadas").value.split(",");
      if(cadenas[cadenas.length-1]==id){
         document.getElementById("textarea"+id).style.backgroundColor="white";
         if(id%2==0)
            desbloqueaDiv(cadenas,"par",nRespuestas);
         else
            desbloqueaDiv(cadenas,"impar",nRespuestas);
         document.getElementById("respuestas").value=document.getElementById("respuestas").value.substring(0,longitud-2)
      }
      else{
         if(cadenas[0]==""){
            i=1;
            while(i<=nRespuestas){
               bloque=document.getElementById("textarea"+i);   
               if(bloque.style.backgroundColor!="white")
                  bloque.style.backgroundColor="white";
               i++;
            }
            document.getElementById("respuestas").value+=id;
         }
         else{
            i=0;
            if(cadenas[0]%2==0){
               primerValorPar=true;
            }

            i=0;
            while(i<nRespuestas/2){
               if(primerValorPar){
                  if(cadenas[0]==respuestasOrdenadas[i*2+1])
                     posicionPrimerValorVectorRespuestas=i;
               }
               else{
                  if(id==respuestasOrdenadas[i*2+1])
                     posicionPrimerValorVectorRespuestas=i; 
               }
               i++;
            }

            i=0;
            while(i<nRespuestas/2){
               if(primerValorPar){
                  if(id==respuestasOrdenadas[i*2])
                     posicionIdVectorRespuestas=i;
               }
               else{
                  if(cadenas[0]==respuestasOrdenadas[i*2])
                     posicionIdVectorRespuestas=i; 
               }
               i++;
            }

            i=0;
            while(i<nRespuestas){
               if(respuestasOrdenadas[i]%2==0){
                  if(primerValorPar){
                     if(respuestasOrdenadas[i]){   
                        responses_right[num_responses_right]=respuestasOrdenadas[i];
                        num_responses_right++;
                     }
                  }
                  else{
                     if(respuestasOrdenadas[i]){
                        responses_right[num_responses_right]=respuestasOrdenadas[i];
                        num_responses_right++;
                     }
                  }
               }
               else{
                  if(primerValorPar){
                     if(respuestasOrdenadas[i]){
                        responses_left[num_responses_left]=respuestasOrdenadas[i];
                        num_responses_left++;
                     }
                  }
                  else{
                     if(respuestasOrdenadas[i]){
                        responses_left[num_responses_left]=respuestasOrdenadas[i];
                        num_responses_left++;
                     }
                  }
               }
               i++;
            }

            if(primerValorPar){
               for(j=0;j<num_responses_right;j++){
                  if(j==posicionIdVectorRespuestas){
                     responses_tem=responses_right[j];
                     responses_right[j]=responses_right[posicionPrimerValorVectorRespuestas];
                     responses_right[posicionPrimerValorVectorRespuestas]=responses_tem;
                  }
               }
            }
            else{
               for(j=0;j<num_responses_right;j++){
                  if(j==posicionPrimerValorVectorRespuestas){
                     responses_tem=responses_right[j];
                     responses_right[j]=responses_right[posicionIdVectorRespuestas];
                     responses_right[posicionIdVectorRespuestas]=responses_tem;
                  }
               }  
            }
            
            i=0;
            while(i<responses_left.length){
               left_text[i]=document.getElementById("textarea"+responses_left[i]).innerHTML;
               right_text[i]=document.getElementById("textarea"+responses_right[i]).innerHTML;
               i++;
            }

            i=0;
            while(i<responses_left.length){
               if(i==0){
                  document.getElementById("respuestasOrdenadas").value=responses_left[i];
                  document.getElementById("respuestasOrdenadas").value+=","+responses_right[i];
               }
               else{
                  document.getElementById("respuestasOrdenadas").value+=","+responses_left[i];
                  document.getElementById("respuestasOrdenadas").value+=","+responses_right[i]; 
               }
               i++;
            }
            i=1;
            while(i<=nRespuestas){
               bloque=document.getElementById("div"+i);  
               bloque.parentNode.removeChild(bloque);
               i++;
            }
            i=0;
            while(i<responses_left.length){
               iDiv = document.createElement('div');
               iDiv.id = 'div'+responses_left[i];
               iDiv.className = "div_response_pairs";
               ta = document.createElement("textarea");
               ta.id="textarea"+responses_left[i];
               ta.className = "textarea_response_pairs";
               ta.readOnly="true";
               text= document.createTextNode(left_text[i]);
               ta.appendChild(text);
               iDiv.appendChild(ta);
               document.getElementById('contenedor1').appendChild(iDiv);
               document.getElementById("div"+responses_left[i]).setAttribute("onclick","javascript:pintaFondo("+responses_left[i]+","+nRespuestas+");");
               i++;
            }
            i=0;
            while(i<responses_right.length){
               iDiv = document.createElement('div');
               iDiv.id = 'div'+responses_right[i];
               iDiv.className = "div_response_pairs";
               ta = document.createElement("textarea");
               ta.id="textarea"+responses_right[i];
               ta.className = "textarea_response_pairs";
               ta.readOnly="true";
               text= document.createTextNode(right_text[i]);
               ta.appendChild(text);
               iDiv.appendChild(ta);
               document.getElementById('contenedor2').appendChild(iDiv);
               document.getElementById("div"+responses_right[i]).setAttribute("onclick","javascript:pintaFondo("+responses_right[i]+","+nRespuestas+");");
               i++;     
            }
            document.getElementById("respuestas").value+=","+id;
         }

         cadenas=document.getElementById("respuestas").value.split(",");
         i=0;
         while(i<cadenas.length){
            document.getElementById("textarea"+cadenas[i]).style.backgroundColor="#60B8F7";
            i++;
         }
         if(cadenas[cadenas.length-1]==id&&id%2==0&&cadenas.length%2!=0)
               bloqueaDiv(cadenas,"par",nRespuestas);
         else if(cadenas[cadenas.length-1]==id&&id%2!=0&&cadenas.length%2!=0)
               bloqueaDiv(cadenas,"impar",nRespuestas);
         if(cadenas.length%2==0&&cadenas.length!=1)
            if(id%2==0)
               desbloqueaDiv(cadenas,"impar",nRespuestas);
            else
               desbloqueaDiv(cadenas,"par",nRespuestas); 
         if(cadenas.length==2){
            document.getElementById("respuestas").value="";
         }
      }
   }

   function bloqueaDiv(cadenas,paridad,nRespuestas){
      var i=1;
      while(i<=nRespuestas){
         if(cadenas[cadenas.length-1]!=i){
            if(paridad=="par")
               if(i%2==0)
                  document.getElementById("div"+i).onclick=null;
            if(paridad=="impar")
               if(i%2!=0)
                  document.getElementById("div"+i).onclick=null;
         }
         i++;
      }
   }

   function desbloqueaDiv(cadenas,paridad,nRespuestas){
      var i=1;
      while(i<=nRespuestas){
         if(cadenas[cadenas.length-1]!=i){
            if(paridad=="par")
               if(i%2==0)
                  document.getElementById("div"+i).setAttribute("onclick","pintaFondo("+i+","+nRespuestas+");");            
            if(paridad=="impar")
               if(i%2!=0)
                  document.getElementById("div"+i).setAttribute("onclick","pintaFondo("+i+","+nRespuestas+");");
         }
         i++;
      }
   }
</script>
