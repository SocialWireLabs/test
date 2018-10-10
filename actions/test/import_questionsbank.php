<?php

gatekeeper();

$testpost = get_input('testpost');
$questionsbankpost = get_input('questionsbankpost');
$tags = get_input('tags');
$question_type = get_input('question_type');
$question_types = implode(",", $question_type);
$question_types=implode(",",$question_type);
$response_type = get_input('response_type');
$response_types = implode(",", $response_type);
$questions_selection_type = get_input('questions_selection_type');
if (strcmp($questions_selection_type,'test_random_questions_selection_type')==0){
   $num_questions_import = get_input('num_questions_import');
}else{
   $num_questions_import = "all";
}

// Cache to the session
elgg_make_sticky_form('import_questionsbank_test');

if (empty($question_type)) {
   register_error(elgg_echo("test:blank_question_type_import"));
   forward("test/import_questionsbank/$testpost");
}
if (empty($response_type)) {
   register_error(elgg_echo("test:blank_response_type_import"));
   forward("test/import_questionsbank/$testpost");
}
if (strcmp($questions_selection_type,'test_random_questions_selection_type')==0){
   $is_integer = true;
   $mask_integer='^([[:digit:]]+)$'; 
   if (ereg($mask_integer,$num_questions_import,$same)){
      if ((substr($same[1],0,1)==0)&&(strlen($same[1])!=1)){
         $is_integer=false;
      }
   } else {
      $is_integer=false;
   }
   if (!$is_integer){
      register_error(elgg_echo("test:bad_number_questions_import"));
      forward("test/import_questionsbank/$testpost");
   }
   if ($num_questions_import<1){
      register_error(elgg_echo("test:bad_number_questions_import"));
      forward("test/import_questionsbank/$testpost");
   }
}

// Remove the test post cache
elgg_clear_sticky_form('import_questionsbank_test');

//Forward
forward("test/select_questions_questionsbank/$testpost/$questionsbankpost/?tags=$tags&question_types=$question_types&response_types=$response_types&questions_selection_type=$questions_selection_type&num_questions_import=$num_questions_import");

?>
