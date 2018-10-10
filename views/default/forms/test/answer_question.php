<?php

$test=$vars['test'];
$num_question=$vars['num_question'];
$question_text=$vars['question_text'];
$question_body=$vars['question_body'];
$question_grading=$vars['question_grading'];
$response_type=$vars['response_type'];
$comments_body=$vars['comments_body'];
switch ($response_type) {
   case 'radiobutton':
      $response_inputs = $vars['response_inputs'];
      break;
   case 'checkbox':
      $response_inputs = $vars['response_inputs'];
      break;
   case 'grid':
      $responses_rows_array = $vars['responses_rows_array'];
      $grid_response_inputs = $vars['grid_response_inputs'];
      break;
   case 'pairs':
      $index = $vars['index'];
      $user_response_draft = $vars['user_response_draft'];
      $user_response_draft_content_array= explode(Chr(27), $user_response_draft);
      $user_response_draft_content_array = array_map('trim', $user_response_draft_content_array);
      $user_response_draft_content_array=explode(Chr(26), $user_response_draft_content_array[2*$index]);
      $responses_left_array = $vars['responses_left_array'];
      $responses_right_array = $vars['responses_right_array'];
      break;
   case 'dropdown':
      $index = $vars['index'];
      $user_response_draft = $vars['user_response_draft'];
      $user_response_draft_content_array= explode(Chr(27), $user_response_draft);
      $user_response_draft_content_array = array_map('trim', $user_response_draft_content_array);      
      $user_response_draft_content_array=explode(Chr(26), $user_response_draft_content_array[2*$index]);
      $responses_dropdown_array = $vars['responses_dropdown_array'];
      $text_dropdown = $vars['text_dropdown'];
      $numbers_responses_dropdowns_array = $vars['numbers_responses_dropdowns_array'];
      $page_position = 1;
      break;
}

//Grading   
if (strcmp($test->type_grading,'test_type_grading_marks')==0){
   $grading_label=elgg_echo("test:mark");
} else {
   $grading_label=elgg_echo("test:game_points");
}

//Question
?>

<div class="test_frame_blue">
   <p>
   <b><?php echo elgg_echo('test:question_label') . " " . $num_question; ?></b> 
   </p>
   <?php echo $question_text; ?>
   <br>
   <?php 
   if (strcmp($question_body,"")!=0){
      echo  $question_body;
      echo "<br>";
   }
   ?>
</div>
<br>

<?php
//Response
?>
<div class="test_frame_yellow">
   <b><?php echo "<p><b>" . elgg_echo('test:response') . "</b></p>";
   switch ($response_type) {
      case 'radiobutton':
         ?>
         <p><?php echo $response_inputs; ?></p>
	 <p><?php echo $clear_inputs; ?></p>
         <?php
      break;
      case 'checkbox':
         ?>
         <p><?php echo $response_inputs; ?></p>
         <?php
      break;
      case 'grid':
         $j = 0;
         foreach ($responses_rows_array as $one_row) {
            ?>
            <p><?php echo $one_row; ?></p>
            <p><?php echo $grid_response_inputs[$j]; ?></p>
            <?php
            $j = $j + 1;
         }
         break;
      case 'pairs':
         $num_responses=count($responses_left_array)+count($responses_right_array);
         $new_responses = "<div id='contenedor1'>";
         $j=0;
         for($i=0;$i<count($responses_left_array);$i++){
            $j=$i*2+1;
            if($user_response_draft_content_array[$j-1]==""||$user_response_draft_content_array[$j-1]=="not_response")
               $new_responses.="<div id='div".$j."' class='div_response_pairs' onclick='pintaFondo(".$j.",".$num_responses.")'><textarea id='textarea".$j."' class='textarea_response_pairs' readOnly='true'>".$responses_left_array[$i]."</textarea></div>";
            else
               $new_responses.="<div id='div".$user_response_draft_content_array[$j-1]."' class='div_response_pairs' onclick='pintaFondo(".$user_response_draft_content_array[$j-1].",".$num_responses.")'><textarea id='textarea".$user_response_draft_content_array[$j-1]."' class='textarea_response_pairs' readOnly='true'>".$responses_left_array[($user_response_draft_content_array[$j-1]-1)/2]."</textarea></div>";
         }
         $new_responses .= "</div>";

         $new_responses .= "<div id='contenedor2'>";
         $j=0;
         for($i=1;$i<=count($responses_right_array);$i++){
            $j=$i*2;
            if($user_response_draft_content_array[$j-1]==""||$user_response_draft_content_array[$j-1]=="not_response")
               $new_responses.="<div id='div".$j."' class='div_response_pairs' onclick='pintaFondo(".$j.",".$num_responses.")'><textarea id='textarea".$j."' class='textarea_response_pairs' readOnly='true'>".$responses_right_array[$i-1]."</textarea></div>";
            else
               $new_responses.="<div id='div".$user_response_draft_content_array[$j-1]."' class='div_response_pairs' onclick='pintaFondo(".$user_response_draft_content_array[$j-1].",".$num_responses.")'><textarea id='textarea".$user_response_draft_content_array[$j-1]."' class='textarea_response_pairs' readOnly='true'>".$responses_right_array[($user_response_draft_content_array[$j-1]/2)-1]."</textarea></div>";
         }
         if($user_response_draft_content_array[0]=="not_response"||$user_response_draft_content_array[0]=="")
            $new_responses .= "</div><div class='respuestas_pairs'><input type='hidden' id='respuestas'><input type='hidden' name='respuestasOrdenadas' id='respuestasOrdenadas'></div>";
         else{
            $respuestasOrdenadas="";
            $i=0;
            foreach ($user_response_draft_content_array as $one_user_response_draft_content) {
               if($i==0)   
                  $respuestasOrdenadas.=$one_user_response_draft_content;
               else
                  $respuestasOrdenadas.=",".$one_user_response_draft_content;
               $i++;
            }
            $new_responses .= "</div><div class='respuestas_pairs'><input type='hidden' id='respuestas'><input type='hidden' name='respuestasOrdenadas' id='respuestasOrdenadas' value='".$respuestasOrdenadas."'></div>";
         }


         echo ($new_responses);
         break;
      case 'dropdown':
         $start=0;
         $index_responses_dropdown=0;
         $i=0;
         $new_responses ="";
         do{
            $question_position=strpos($text_dropdown,"(?)",$start);
            $temp_question_text=substr($text_dropdown, $start, $question_position-$start);
            $start=$question_position+3;
            $new_responses.= $temp_question_text;
            $new_responses.= "<select name='dropdown_".($i+1)."'>";
            if($user_response_draft_content_array[$i]=="")
               $new_responses.= "<option value='' selected='selected'></option>";
            else
               $new_responses.= "<option value=''></option>";
            for($j=0;$j<$numbers_responses_dropdowns_array[$i];$j++){
               if($user_response_draft_content_array[$i]==$responses_dropdown_array[$index_responses_dropdown])
                  $new_responses.= "<option value='".$responses_dropdown_array[$index_responses_dropdown]."' selected='selected'>".$responses_dropdown_array[$index_responses_dropdown]."</option>";
               else
                  $new_responses.= "<option value='".$responses_dropdown_array[$index_responses_dropdown]."'>".$responses_dropdown_array[$index_responses_dropdown]."</option>";
               $index_responses_dropdown++;
            }
            $new_responses.= "</select>";
            $i++;
         }while(strpos($text_dropdown,"(?)",$start));

         echo ($new_responses);
         break;
   }
?>

</div><br>

<div class="test_frame_green">

   <p><b><?php echo elgg_echo('test:comments_label'); ?></b></p>
   <p><?php echo $comments_body; ?></p>
</div>
<br>