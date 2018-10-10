<div class="contentWrapper">

<?php

elgg_load_library('test');

if (isset($vars['entity'])) {
   $test=$vars['entity'];
   $testpost=$test->getGUID();

   $user_guid = $vars['user_guid'];
   $user = get_entity($user_guid);
   $container_guid  = $test->container_guid;

   if (isset($vars['index'])) {
      $index=$vars['index'];
   } else {
      $index="none";
   }

   $opened = test_check_status($test);

   //Questions
   $options = array('relationship' => 'test_question', 'relationship_guid' => $testpost,'inverse_relationship' => false, 'type' => 'object', 'subtype' => 'test_question','limit'=>0);
   $questions=elgg_get_entities_from_relationship($options);

   if (!empty($questions)) {
      $num_questions = count($questions);
      $only_simple_choice = true;
      foreach($questions as $one_question) {
         if (strcmp($one_question->response_type,'radiobutton')!=0){
	    $only_simple_choice = false;
	    break;
	 }
      }
   }

   //Answers
   if (!$test->subgroups){
      $options = array('relationship' => 'test_answer', 'relationship_guid' => $test->getGUID(),'inverse_relationship' => false, 'type' => 'object', 'subtype' => 'test_answer', 'order_by' => 'e.time_created desc', 'limit' => 0, 'owner_guid'=> $user_guid);
   } else {
      $options = array('relationship' => 'test_answer', 'relationship_guid' => $test->getGUID(),'inverse_relationship' => false, 'type' => 'object', 'subtype' => 'test_answer', 'order_by' => 'e.time_created desc', 'limit' => 0, 'container_guid'=> $user_guid);
   }
   $user_responses=elgg_get_entities_from_relationship($options);

   if (!empty($user_responses)) {
      $user_response=$user_responses[0];
      $user_response_content_array = explode(Chr(27),$user_response->content);
      $user_response_content_array = array_map('trim', $user_response_content_array);
   } else{
      $user_response="";
   }

   if ($test->random_questions){
      $selected_random_questions = explode(";",$user_response->selected_random_questions);
      $selected_question_index = $selected_random_questions[$index];
   } else {
      $selected_question_index = $index;
   }

   //Question
   $options = array('relationship' => 'test_question', 'relationship_guid' => $testpost,'inverse_relationship' => false, 'type' => 'object', 'subtype' => 'test_question','metadata_name_value_pairs' => array('name' => 'index', 'value' => $selected_question_index));
   $questions=elgg_get_entities_from_relationship($options);
   $one_question=$questions[0];


   if (!empty($user_response)) {

      $num_questions = $user_response->num_questions;

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
                  if (elgg_is_active_plugin("sw_embedlycards"))
                     {
                       $question_body .= "<div>
                       <a class='embedly-card' href='$url_value'></a>
                       </div>";
                    }
                  else if (elgg_is_active_plugin("hypeScraper"))
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

      $question_grading=$one_question->grading;
      $response_type=$one_question->response_type;
      $correct_responses=$one_question->correct_responses;

      switch ($response_type) {
         case 'radiobutton':
         case 'checkbox':
            $responses_array = explode(Chr(26), $one_question->responses);
            $responses_array = array_map('trim', $responses_array);
            break;
         case 'grid':
            $responses_rows_array= explode(Chr(26), $one_question->responses_rows);
            $responses_rows_array = array_map('trim', $responses_rows_array);
            $responses_columns_array = explode(Chr(26), $one_question->responses_columns);
            $responses_columns_array = array_map('trim', $responses_columns_array);
            break;
         case 'pairs':
            $responses_left_array = explode(Chr(26),$one_question->responses_left);
            $responses_left_array = array_map('trim',$responses_left_array);
            $responses_right_array = explode(Chr(26),$one_question->responses_right);
            $responses_right_array = array_map('trim',$responses_right_array);
            break;
         case 'dropdown':
            $responses_dropdown = explode(Chr(26),$one_question->responses_dropdown);
            $responses_dropdown = array_map('trim',$responses_dropdown);
            $text_dropdown = $one_question->question_text;
            $numbers_responses_dropdowns = explode(",",$one_question->numbers_responses_dropdowns);
            $numbers_responses_dropdowns = array_map('trim',$numbers_responses_dropdowns);
            $num_temp="";
            $i=0;
            foreach ($numbers_responses_dropdowns as $one_number) {
               if($i==0)
                  $num_temp=$one_number;
               else
                  $num_temp.=",".$one_number;
               $i++;
            }
            $numbers_responses_dropdowns=$num_temp;
            $page_position = 1;
            break;
      }

      ///////////////////////////////////////////////////////////////////
      //Grading

      if (strcmp($user_response_content_array[1+2*$index],"not_qualified")==0) {
         $this_grading = "";
      } else {
         $this_grading = $user_response_content_array[1+2*$index];
	 if (strcmp($test->type_grading,'test_type_grading_marks')==0)
            $this_grading = number_format($this_grading,2);
      }

      //////////////////////////////////////////////////////////
      //Responses

      $response_inputs = "";
      switch ($response_type) {
         case 'radiobutton':
            $new_responses_array = array();
            foreach ($responses_array as $one_response){
               $new_responses_array[$one_response]=$one_response;
            }
	    $this_responses = $user_response_content_array[2*$index];
            $response_inputs .= elgg_view('input/radio', array('disabled' => 'yes','options' => $new_responses_array, 'value' => $this_responses));
            break;
         case 'checkbox':
            $new_responses_array = array();
            foreach ($responses_array as $one_response){
               $new_responses_array[$one_response]=$one_response;
            }
	    $this_responses = $user_response_content_array[2*$index];
	    $this_responses = explode(Chr(26),$this_responses);
            $this_responses = array_map('trim', $this_responses);
            $response_inputs .= elgg_view('input/checkboxes', array('disabled' => 'yes','options' => $new_responses_array, 'value' => $this_responses));
            break;
         case 'grid':
            $new_responses_columns_array = array();
            foreach ($responses_columns_array as $one_response){
               $new_responses_columns_array[$one_response]=$one_response;
            }
	    $this_responses = $user_response_content_array[2*$index];
	    $this_responses = explode(Chr(26),$this_responses);
            $this_responses = array_map('trim', $this_responses);
	    $j=0;
	    foreach ($responses_rows_array as $one_row) {
	       if ($j>0)
	          $response_inputs .= "<br>";
	       $response_inputs .= "<p><b>" . $one_row . "</b></p>";
               foreach ($new_responses_columns_array as $one_response_column) {
	          if ($this_responses[$j]==$one_response_column){
		     $checked = "checked = \"checked\"";
		  } else {
		     $checked = "";
		  }
		  $grid_response_inputs[$j] = "<input type=\"radio\" disabled  value=$one_response_column $checked >$one_response_column" . "<br>";
	          //$grid_response_inputs[$j] = elgg_view('input/radio', array('disabled' => 'yes','options' => $new_responses_columns_array, 'value' => $this_responses));
	          $response_inputs .= $grid_response_inputs[$j];
	       }
	       $j = $j+1;
	    }
            break;
         case 'pairs':
            $this_responses = $user_response_content_array[2*$index];
            $this_responses = explode(Chr(26),$this_responses);
            $this_responses = array_map('trim', $this_responses);
            $new_responses = "<div id='contenedor1'>";
            foreach($responses_left_array as $one_response_left){
               $new_responses.="<div class='div_response_pairs'><textarea class='textarea_response_pairs' disabled='true'>".$one_response_left."</textarea></div>";
            }
            $new_responses .= "</div>";
            $new_responses .= "<div id='contenedor2'>";
            $j=0;
            $i=1;
            foreach($responses_left_array as $one_response_left){
               $j=0;
               foreach($this_responses as $one_this_response){
                  if($j%2==0){
                     if($i==$one_this_response){
                        $new_responses.="<div class='div_response_pairs'><textarea class='textarea_response_pairs' disabled='true'>".$responses_right_array[$this_responses[$j+1]/2-1]."</textarea></div>";
                        break;
                     }
                  }
                  $j++;
               }
               $i=$i+2;
            }
            $new_responses .= "</div><div class='respuestas_pairs'></div>";

            $response_inputs .= $new_responses;
            break;
         case 'dropdown':
            $this_responses = $user_response_content_array[2*$index];
            $this_responses = explode(Chr(26),$this_responses);
            $this_responses = array_map('trim', $this_responses);
            $numbers_responses_dropdowns = explode(",",$one_question->numbers_responses_dropdowns);
            $numbers_responses_dropdowns = array_map('trim',$numbers_responses_dropdowns);
            $start=0;
            $index_responses_dropdown=0;
            $i=0;
            do{
               $question_position=strpos($text_dropdown,"(?)",$start);
               $temp_question_text=substr($text_dropdown, $start, $question_position-$start);
               $start=$question_position+3;
               $new_responses .= $temp_question_text;
               $new_responses .= "<select name='dropdown_".($i+1)."' disabled>";
               $new_responses.= "<option value='".$this_responses[$i]."'>".$this_responses[$i]."</option>";
               $new_responses.= "</select>";
               $i++;
            }while(strpos($text_dropdown,"(?)",$start));
            $response_inputs .= $new_responses;
            break;
      }


      $correct_response_inputs = "";
      switch ($response_type) {
         case 'radiobutton':
            $correct_response_inputs .= elgg_view('input/radio', array('disabled' => 'yes','options' => $new_responses_array, 'value' => $correct_responses));
            break;
         case 'checkbox':
	    $correct_responses_array = explode(Chr(26),$correct_responses);
            $correct_responses_array = array_map('trim', $correct_responses_array);
            $correct_response_inputs .= elgg_view('input/checkboxes', array('disabled' => 'yes','options' => $new_responses_array, 'value' => $correct_responses_array));
            break;
         case 'grid':
	    $correct_responses_array = explode(Chr(26),$correct_responses);
            $correct_responses_array = array_map('trim', $correct_responses_array);
	    $j = 0;
            foreach ($responses_rows_array as $one_row) {
	       if ($j>0)
	          $correct_response_inputs .= "<br>";
               $correct_response_inputs .= "<p><b>" . $one_row . "</b></p>";
               foreach ($responses_columns_array as $one_response_column) {
                  if ($correct_responses_array[$j] == $one_response_column) {
                     $checked = "checked = \"checked\"";
                  } else {
                     $checked = "";
                  }
                  $correct_response_inputs .= "<b><input type=\"radio\" disabled value=$one_response_column $checked >$one_response_column" . "</b><br>";
               }
               $j = $j + 1;
            }
            break;
         case 'pairs':
            $correct_responses=explode(Chr(27),$correct_responses);
            $correct_responses = array_map('trim', $correct_responses);
            $new_correct_responses = "<div id='correct_response1'>";
	    foreach($correct_responses as $one_correct_response){
               $one_correct_response_array = explode(Chr(26),$one_correct_response);
               $one_correct_response_array = array_map('trim',$one_correct_response_array);
	       $new_correct_responses.="<div class='div_response_pairs'><textarea disabled='true'>".$one_correct_response_array[0]."</textarea></div>";
             }
            $new_correct_responses .= "</div>";
            $new_correct_responses .= "<div id='correct_response2'>";
	    foreach($correct_responses as $one_correct_response){
               $one_correct_response_array = explode(Chr(26),$one_correct_response);
               $one_correct_response_array = array_map('trim',$one_correct_response_array);
	       $new_correct_responses.="<div class='div_response_pairs'><textarea disabled='true'>".$one_correct_response_array[1]."</textarea></div>";
            }
            $new_correct_responses .= "</div><div class='respuestas_pairs'></div>";

            $correct_response_inputs .= $new_correct_responses;
            break;
         case 'dropdown':
            $correct_responses=explode(Chr(26),$correct_responses);
            $correct_responses = array_map('trim', $correct_responses);
            $start=0;
            $index_responses_dropdown=0;
            $i=0;
            do{
               $question_position=strpos($text_dropdown,"(?)",$start);
               $temp_question_text=substr($text_dropdown, $start, $question_position-$start);
               $start=$question_position+3;
               $correct_response_inputs .= $temp_question_text;
               $correct_response_inputs .= "<select name='dropdown_".($i+1)."' disabled>";
               for($j=0;$j<$numbers_responses_dropdowns[$i];$j++){
                  if(($j+1)==$correct_responses[$i])
                     $correct_response_inputs.= "<option value='".$responses_dropdown[$index_responses_dropdown]."'>".$responses_dropdown[$index_responses_dropdown]."</option>";
                  $index_responses_dropdown++;
               }
               $correct_response_inputs.= "</select>";
               $i++;
            }while(strpos($text_dropdown,"(?)",$start));
            break;
      }

      //////////////////////////////////////////////////////////
      //Comments

      $comments_body = "";
      if (strcmp($user_response->comments,"not_comments")!=0){
         $comments_body .= "<p><b>" . elgg_echo('test:comments_label') . "</b></p>";
         $comments_body .= "<div class=\"test_question_frame\">";
         $comments_body .= elgg_view('output/longtext',array('value' => $user_response->comments));
         $comments_body .= "</div>";
      }

      if ($index == "none"){

         $form_body = "";
         $form_body .= "<div class=\"test_frame\">";

         if (strcmp($test->description,"")!=0){
            $form_body .= "<p>" . "<b>" . elgg_echo('test:test_description_label') . "</b>" . "</p>";
	    $form_body .= "<div class=\"test_question_frame\">";
            $form_body .= elgg_view('output/longtext', array('value' => $test->description));
	    $form_body .= "</div><br>";
         }

         //General comments
         $num_comments =  $test->countComments();
         if ($num_comments>0)
            $test_general_comments_label = elgg_echo('test:general_comments') . " (" . $num_comments . ")";
         else
            $test_general_comments_label = elgg_echo('test:general_comments');
         $form_body .= "<p align=\"left\"><a onclick=\"test_show_general_comments();\" style=\"cursor:hand;\">$test_general_comments_label</a></p>";
         $form_body .= "<div id=\"commentsDiv\" style=\"display:none;\">";
         $form_body .= elgg_view_comments($test);
         $form_body .= "</div>";

         if (strcmp($test->type_grading,'test_type_grading_marks')==0){
            $max_grading=$test->max_mark;
         } else {
            $max_grading=$test->question_max_game_points;
         }
         $form_body .= '<p><b>' . elgg_echo('test:max_grading_label') . ": " . '</b>' . $max_grading . "</p>";

         if (strcmp($test->type_grading,'test_type_grading_marks')==0){
            $response_grading = number_format($user_response->grading,2);
	    $response_grading_output=test_grading_output($test,$response_grading);
            $form_body .= "<p><b>" . elgg_echo('test:your_mark') . ": " . "</b>" . $response_grading_output . "</p>";
         } else {
            $response_grading = $user_response->grading;
	    $response_grading_output=test_grading_output($test,$response_grading);
            $form_body .= "<p><b>" . elgg_echo('test:your_game_points') . ": " . "</b>" . $response_grading_output . "</p>";
         }

         $form_body .= '<p><b>' . elgg_echo('test:num_questions_label') . " " . '</b>' . $num_questions . "</p>";

         if (strcmp($test->num_cancel_questions,'0')==0){
            $form_body .= '<p><b>' . elgg_echo('test:penalty_no') . "</b></p>";
         } else{
            $form_body .= '<p><b>' . $test->num_cancel_questions . " " . elgg_echo('test:penalty_yes') . "</b></p>";
	    if ($test->penalty_not_response){
               $form_body .= '<p><b>' . elgg_echo('test:penalty_not_response_yes') . '</b></p>';
	    } else {
               $form_body .= '<p><b>' . elgg_echo('test:penalty_not_response_no') . '</b></p>';
	    }
         }

	 if (!$only_simple_choice) {
            if (strcmp($test->all_in_checkbox,'proporcional')==0){
               $form_body .= '<p><b>' . elgg_echo('test:proporcional_info') . '</b></p>';
            } else {
               $form_body .= '<p><b>' . elgg_echo('test:all_in_info')  . '</b></p>';
            }
	 }

         $time=$user_response->time;
         $time_minutes = $time/60;
	 $total_time=0;
	 $all_times = explode(";",$user_response->all_times);
	 foreach($all_times as $one_time){
	    $total_time += $one_time;
	 }
	 $total_time_minutes = $total_time/60;
         if ($test->max_duration_minutes == 999999999){
            $duration_text = "<b>" .  elgg_echo('test:time') . ": " . "</b>" . number_format($time_minutes,2) . "<b>" . "' (" . elgg_echo('test:max_duration') . ": " . elgg_echo("test:unlimited") . ") - " . "</b>" . "<b>" . elgg_echo('test:total_time') . ": " . "</b>" . number_format($total_time_minutes,2) . "'";
         } else{
            $duration_text = "<b>" . elgg_echo('test:time') . ": " . "</b>" . number_format($time_minutes,2) . "<b>" . "' (" . elgg_echo('test:max_duration') . ": " . $test->max_duration_minutes . "') - " ."</b>" . "<b>" . elgg_echo('test:total_time') . ": " . "</b>" . number_format($total_time_minutes,2) . "'";
         }

         $attempts=$user_response->attempts;

         switch($test->type_max_attempts){
	   case 'test_unlimited_attempts':
              $attempts_text = "<b>" . elgg_echo('test:attempts') . ": " . "</b>" . $attempts . "<b>" . " (" . elgg_echo('test:attempts_unlimited') . ")" . elgg_echo('test:mark:warning') ."</b>";
              break;
	   case 'test_limited_attempts':
              $attempts_text = "<b>" . elgg_echo('test:attempts') . ": " . "</b>" . $attempts . "<b>" . " (" . elgg_echo('test:max_attempts') . ": " . $test->max_attempts . ")" . elgg_echo('test:mark:warning') . "</b>";
	      break;
	   case 'test_max_grading_limited_attempts':
	      $attempts_text = "<b>" . elgg_echo('test:attempts') . ": " . "</b>" . $attempts . "<b>" . " (" . elgg_echo('test:attempts_limited_max_grading') . ")" . elgg_echo('test:mark:warning') . "</b>";
	      break;
         }
         $form_body .= "<p>" . $duration_text . "</p>";
         $form_body .= "<p>" . $attempts_text . "</p>";

         if (strcmp($test->feedback,"not_feedback")!=0){
            $form_body .= "<p><b>" . elgg_echo('test:feedback_label') . "</b></p>";
	    $form_body .= "<div class=\"test_question_frame\">";
            $form_body .= elgg_view('output/longtext',array('value' => $test->feedback));
	    $form_body .= "</div>";
         }

         $form_body .= "</div>";
         $form_body .= "<br>";
         echo($form_body);
      }

      ///////////////////////////////////////////////////////////////
      //Body

      $form_body = "";

      $num_question = $index+1;

      if (strcmp($test->type_grading,'test_type_grading_marks')==0){
	 $max_grading=$test->max_mark;
      } else {
	 $max_grading=$test->question_max_game_points;
      }

      if ($index != "none"){

         $now = time();
	 if (strcmp($test->type_grading,'test_type_grading_marks')==0){
            $response_grading = number_format($user_response->grading,2);
	    $response_grading_output=test_grading_output($test,$response_grading);

         } else {
            $response_grading = $user_response->grading;
	    $response_grading_output=test_grading_output($test,$response_grading);
         }
         if (((strcmp($test->correct_responses_visibility,'test_after_close_time_correct_responses_visibility')==0)&&($now>=$test->close_time))||((strcmp($test->correct_responses_visibility,'test_after_attempts_correct_responses_visibility')==0)&&(strcmp($test->type_max_attempts,'test_limited_attempts')==0)&&($user_response->attempts==$test->max_attempts))||((strcmp($test->correct_responses_visibility,'test_after_attempts_correct_responses_visibility')==0)&&(strcmp($test->type_max_attempts,'test_max_grading_limited_attempts')==0)&&($response_grading>=$max_grading))){
	    $correct_responses_visibility=true;
         } else {
	    $correct_responses_visibility=false;
         }

         $form_body .= elgg_view("forms/test/show_answer_question",array('one_question'=> $one_question, 'test'=>$test,'num_question'=>$num_question,'question_text'=>$question_text,'question_body'=>$question_body,'question_grading'=>$question_grading,'this_grading'=>$this_grading,'response_type'=>$response_type,'response_inputs'=>$response_inputs,'correct_responses_visibility'=>$correct_responses_visibility,'correct_response_inputs'=>$correct_response_inputs,'comments_body'=>$comments_body));

         //Submit

         if ($num_questions==1){
            echo elgg_echo($form_body);
         } else {

            $link_question_go = "&nbsp;&nbsp;<a onclick=\"javascript:check_question_number_and_go(".$testpost.",".$user_guid.",".$num_questions.");return true; \">".elgg_echo('test:go_to_answer').":"."</a>";

            $box_question_go = "&nbsp;<input type=\"text\"name=\"number_question_go\"value=\"" . $number_question_go . "\"style=\"width: 80px\"/>";

            $action = "test/show_answer";

            $test_show_answer_previous = elgg_echo('test:show_answer_previous');
            $test_show_answer_next = elgg_echo('test:show_answer_next');
            $submit_input_show_answer_next = elgg_view('input/submit', array('name' => 'submit', 'value' => $test_show_answer_next));
            $submit_input_show_answer_previous = elgg_view('input/submit', array('name' => 'submit', 'value' => $test_show_answer_previous));
            $entity_hidden = elgg_view('input/hidden', array('name' => 'testpost', 'value' => $testpost));
	    $entity_hidden .= elgg_view('input/hidden', array('name' => 'user_guid', 'value'=> $user_guid));
            $entity_hidden .= elgg_view('input/hidden', array('name' => 'index', 'value'=> $index));

            if ($index==0){
               $form_body .= "<p>" . "$submit_input_show_answer_next $entity_hidden $link_question_go $box_question_go" . "</p>";
            } else {
               if ($index==($num_questions-1)) {
                  $form_body .= "<p>" . "$submit_input_show_answer_previous $entity_hidden $link_question_go $box_question_go" . "</p>";
               } else {
                  $form_body .= "<p>" . "$submit_input_show_answer_previous $submit_input_show_answer_next $entity_hidden $link_question_go $box_question_go" . "</p>";
               }
	    }

	    ?>
            <form action="<?php echo elgg_get_site_url()."action/".$action?>" name="show_answer_test" enctype="multipart/form-data" method="post">
            <?php
            echo elgg_view('input/securitytoken');
	    echo elgg_echo($form_body);
         }

      } else{
         $view_button_text = elgg_echo('test:see_answers');
         $view_button_link = elgg_get_site_url() . 'test/view/'. $testpost . '/?index=0&this_user_guid_show_answer=' . $user_guid;
         $view_button = elgg_view('input/button', array('name' => 'return','class' => 'elgg-button-submit', 'value' => $view_button_text));
         $view_button = "<a href=" . $view_button_link . ">" . $view_button. "</a>";
         echo "$view_button";
      }
   } else {
      if (!$opened){
         $form_body .= "<p>" . elgg_echo('test:closed') . "</p>";
	 $form_body .= "<p>" . elgg_echo('test:not_previous_response') . "</p>";
      } else {
         $form_body .= "<p>" . elgg_echo('test:not_response') . "</p>";
      }
      echo elgg_echo($form_body);
   }

}

?>
</form>
</div>

<script type="text/javascript">
   function test_show_general_comments(){
      var commentsDiv = document.getElementById('commentsDiv');
      if (commentsDiv.style.display == 'none'){
         commentsDiv.style.display = 'block';
      } else {
         commentsDiv.style.display = 'none';
      }
   }
   function check_question_number_and_go(testpost,user_guid,num_questions){
      var name = "number_question_go";
      var number_question_go = document.getElementsByName(name).item(0).value - 1;
      if (isNaN(number_question_go) || number_question_go<0 || number_question_go==undefined || number_question_go>=num_questions){
         alert("<?php echo elgg_echo("test:error");?>");
      } else {
         var url1 = "<?php echo elgg_get_site_url(); ?>test/view/";
         var url2 = "/?index=";
         var url3 = "&this_user_guid_show_answer=";
         var url = url1.concat(testpost,url2,number_question_go,url3,user_guid);
         window.location.href = url;
      }
    }
</script>
