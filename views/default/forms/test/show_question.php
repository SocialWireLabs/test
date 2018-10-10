<div class="contentWrapper">

<?php

elgg_load_library('test');

if (isset($vars['entity'])) {
   $test=$vars['entity'];
   $testpost=$test->getGUID();

   $container_guid  = $test->container_guid;

   if (isset($vars['this_index'])) {
      $this_index=$vars['this_index'];
      if ($this_index=="none")
         $this_index=0;
   } else {
      $this_index=0;
   }

   //Question
   $options = array('relationship' => 'test_question', 'relationship_guid' => $testpost,'inverse_relationship' => false, 'type' => 'object', 'subtype' => 'test_question','metadata_name_value_pairs' => array('name' => 'index', 'value' => $this_index));
   $questions=elgg_get_entities_from_relationship($options);
   $one_question=$questions[0];

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
            $page_position = 1;
            break;
      }

      //Responses

      $response_inputs = "";
      switch ($response_type) {
         case 'radiobutton':
            $new_responses_array = array();
            foreach ($responses_array as $one_response){
               $new_responses_array[$one_response]=$one_response;
            }
            $response_inputs .= elgg_view('input/radio', array('disabled' => 'yes','options' => $new_responses_array, 'value' => $this_responses));
            break;
         case 'checkbox':
            $new_responses_array = array();
            foreach ($responses_array as $one_response){
               $new_responses_array[$one_response]=$one_response;
            }
            $response_inputs .= elgg_view('input/checkboxes', array('disabled' => 'yes','options' => $new_responses_array, 'value' => $this_responses));
            break;
         case 'grid':
            $new_responses_columns_array = array();
            foreach ($responses_columns_array as $one_response){
               $new_responses_columns_array[$one_response]=$one_response;
            }
            $j=0;
            foreach ($responses_rows_array as $one_row) {
               if ($j>0) 
                  $response_inputs .= "<br>";
               $response_inputs .= "<p><b>" . $one_row . "</b></p>";
               $grid_response_inputs[$j] = elgg_view('input/radio', array('disabled' => 'yes','options' => $new_responses_columns_array, 'value' => $this_responses));
               $response_inputs .= $grid_response_inputs[$j];
               $j = $j+1;
            }
            break;
         case 'pairs':
            $new_responses = "<div id='contenedor1'>";
            $j=0;
            for($i=0;$i<count($responses_left_array);$i++){
               $j=$i*2+1;
               $new_responses.="<div class='div_response_pairs'><textarea class='textarea_response_pairs' disabled='true'>".$responses_left_array[$i]."</textarea></div>";
            }
            $new_responses .= "</div>";

            $new_responses .= "<div id='contenedor2'>";
            $j=0;
            for($i=1;$i<=count($responses_right_array);$i++){
               $j=$i*2;
               $new_responses.="<div class='div_response_pairs'><textarea class='textarea_response_pairs' disabled='true'>".$responses_right_array[$i-1]."</textarea></div>";
            }
            $new_responses .= "</div><div class='respuestas_pairs'></div>";

            $response_inputs .= $new_responses;
            break;
         case 'dropdown':
            $start=0;
            $index_responses_dropdown=0;
            $i=0;
            do{
               $question_position=strpos($text_dropdown,"(?)",$start);
               $temp_question_text=substr($text_dropdown, $start, $question_position-$start);
               $start=$question_position+3;
               $response_inputs.= $temp_question_text;
               $response_inputs.= "<select name='dropdown_".($i+1)."'>";
               $response_inputs.= "<option value='' selected='selected'></option>";
               for($j=0;$j<$numbers_responses_dropdowns[$i];$j++){
                  $response_inputs.= "<option value='".$responses_dropdown[$index_responses_dropdown]."'>".$responses_dropdown[$index_responses_dropdown]."</option>";
                  $index_responses_dropdown++;
               }
               $response_inputs.= "</select>";
               $i++;
            }while(strpos($text_dropdown,"(?)",$start)); 
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


      ///////////////////////////////////////////////////////////////
      //Body

      $form_body = "";

      $num_question = $this_index+1;

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
      $form_body .= "<p><b>" . elgg_echo('test:response') . "</b></p>";
      $form_body .= $response_inputs;
      //Correct response
      $form_body .= "<br><p><b>" . elgg_echo('test:correct_response') . "</b></p>";
      $form_body .= $correct_response_inputs; 
      if ($one_question->question_explanation){
         $form_body .= "<br><p><b>" . elgg_echo('test:question_explanation') . "</b></p>";
         $form_body .= $one_question->question_explanation;
      } 

      $form_body .= "</div>";
      $form_body .= "<br>";

      //Submit
      $options = array('relationship' => 'test_question', 'relationship_guid' => $testpost,'inverse_relationship' => false, 'type' => 'object', 'subtype' => 'test_question');
      $questions=elgg_get_entities_from_relationship($options);
      $num_questions = count($questions);

      if ($num_questions==1){
         echo elgg_echo($form_body);
      } else {
         $link_question_go = "&nbsp;&nbsp;<a onclick=\"javascript:check_question_number_and_go(".$testpost.",".$num_questions.");return true; \">".elgg_echo('test:go_to_question').":"."</a>";
         $box_question_go = "&nbsp;<input type=\"text\"name=\"number_question_go\"value=\"" . $number_question_go . "\"style=\"width: 80px\"/>";

         $action = "test/show_question";

         $test_show_question_previous = elgg_echo('test:show_question_previous');
         $test_show_question_next = elgg_echo('test:show_question_next');
         $submit_input_show_question_next = elgg_view('input/submit', array('name' => 'submit', 'value' => $test_show_question_next));
         $submit_input_show_question_previous = elgg_view('input/submit', array('name' => 'submit', 'value' => $test_show_question_previous));
         $entity_hidden = elgg_view('input/hidden', array('name' => 'testpost', 'value' => $testpost));
         $entity_hidden .= elgg_view('input/hidden', array('name' => 'index', 'value'=> $this_index));

         if ($this_index==0){
            $form_body .= "<p>" . "$submit_input_show_question_next $entity_hidden $link_question_go $box_question_go" . "</p>";
         } else {
            if ($this_index==($num_questions-1)) {
               $form_body .= "<p>" . "$submit_input_show_question_previous $entity_hidden $link_question_go $box_question_go" . "</p>";
            } else {
               $form_body .= "<p>" . "$submit_input_show_question_previous $submit_input_show_question_next $entity_hidden $link_question_go $box_question_go" . "</p>";
            }
	 }

	 ?>
         <form action="<?php echo elgg_get_site_url()."action/".$action?>" name="show_question_test" enctype="multipart/form-data" method="post">
         <?php
         echo elgg_view('input/securitytoken');
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
   function check_question_number_and_go(testpost,num_questions){
      var name = "number_question_go";
      var number_question_go = document.getElementsByName(name).item(0).value - 1;
      if (isNaN(number_question_go) || number_question_go<0 || number_question_go==undefined || number_question_go>=num_questions){
         alert("<?php echo elgg_echo("test:error");?>");
      } else {
         var url1 = "<?php echo elgg_get_site_url(); ?>test/view/";
         var url2 = "/?index=";
         var url = url1.concat(testpost,url2,number_question_go);
         window.location.href = url;
      }
    }
</script>
