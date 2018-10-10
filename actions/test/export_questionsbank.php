<?php

gatekeeper();

$questionsbankpost = get_input('questionsbankpost');
$questionsbank = get_entity($questionsbankpost);
$testpost = get_input('testpost');
$test = get_entity($testpost);
$user_guid = elgg_get_logged_in_user_guid();
$index = get_input('index');

if ($questionsbank->getSubtype() == "questionsbank" && $questionsbank->canEdit()) {
   if ($index=="all")
      $index_test = 0;
   else
      $index_test = $index;
   
   while (true){
      $options = array('relationship' => 'test_question', 'relationship_guid' => $testpost,'inverse_relationship' => false, 'type' => 'object', 'subtype' => 'test_question', 'metadata_name_value_pairs' => array('name' => 'index', 'value' => $index_test));
      $questions_test=elgg_get_entities_from_relationship($options);
      $one_question_test = $questions_test[0];

      if (empty($one_question_test)){
         system_message(elgg_echo("questionsbank:updated"));
         forward("test/edit/$testpost");
      }

      $input_question = $one_question_test->question;
      $input_question_html = $one_question_test->question_html;
      $input_question_explanation = $one_question_test->question_explanation;
      $input_question_type = $one_question_test->question_type;
      switch($input_question_type){
         case 'urls_files':   
            $input_question_urls = $one_question_test->question_urls;
            break;
      }
      $input_response_type = $one_question_test->response_type;
      switch ($input_response_type) {
         case 'radiobutton':
         case 'checkbox':
            $input_responses = $one_question_test->responses;
            break;
         case 'grid':
            $input_responses_rows = $one_question_test->responses_rows;
            $input_responses_columns = $one_question_test->responses_columns;
            break;
         case 'pairs':
            $input_responses_left = $one_question_test->responses_left;
            $input_responses_right = $one_question_test->responses_right;
            break;
         case 'dropdown':
            $input_responses_dropdown = $one_question_test->responses_dropdown;
            $text_dropdown = $one_question_test->question_text;
            $numbers_responses_dropdowns = $one_question_test->numbers_responses_dropdowns;
            break;
      }
      
      switch ($input_response_type) {
            case 'radiobutton':
                $input_correct_responses = $one_question_test->correct_responses;
                break;
            case 'checkbox':
                $input_correct_responses = $one_question_test->correct_responses;
                break;
            case 'grid':
                $input_correct_responses = $one_question_test->correct_responses;
                break;
            case 'pairs':
                $input_correct_responses = $one_question_test->correct_responses;
                break;
            case 'dropdown':
                $input_correct_responses = $one_question_test->correct_responses;
                break;
      }
      $test_question_files = elgg_get_entities_from_relationship(array('relationship' => 'question_file_link','relationship_guid' => $one_question_test->getGUID(),'inverse_relationship' => false,'type' => 'object','limit'=>0));
      if (!empty($test_question_files)) {
         $file_counter = count($test_question_files);
      } else {
         $file_counter = 0;
      }
      $input_tags = $one_question_test->tags;
     
      $options = array('relationship' => 'questionsbank_question', 'relationship_guid' => $questionsbankpost,'inverse_relationship' => false, 'type' => 'object', 'subtype' => 'questionsbank_question','limit'=>0);
      $questions=elgg_get_entities_from_relationship($options);
      if (!empty($questions)){
         $index_questions_bank = count($questions);
      } else {
         $index_questions_bank = 0;
      }

      if ((strcmp($input_question_type,"urls_files")==0)){
         if ($file_counter>0){
            $file_save_well=true;
            $file=array();
            $i=0;
            foreach($test_question_files as $one_file){
               $file[$i] = new QuestionsQuestionsbankPluginFile();
               $file[$i]->subtype = "questionsbank_question_file";
               $prefix = "file/";
               $name = $one_file->title;
               $mimetype = $one_file->mimetype;
               $filestorename = elgg_strtolower(time().$name);
               $file[$i]->setFilename($prefix.$filestorename);
               $file[$i]->setMimeType($mimetype);
               $file[$i]->originalfilename = $name;
               $file[$i]->simpletype = $one_file->simpletype;
               $file[$i]->open("write");
               $file_owner = $one_file->getOwnerEntity();
               $file_owner_time_created = date('Y/m/d',$file_owner->time_created);
               $file_dir_root = elgg_get_config('dataroot');
               $filename = $file_dir_root . $file_owner_time_created . "/" . $file_owner->guid . "/" . $one_file->filename;
               $content = file_get_contents($filename);
               $file[$i]->write($content);
               $file[$i]->close();
               $file[$i]->title = $name;
               $file[$i]->owner_guid = $user_guid;
               $file[$i]->container_guid = $questionsbank->container_guid;
               $file[$i]->access_id = $questionsbank->access_id;
               $file_save = $file[$i]->save();
               if (!$file_save) {
                  $file_save_well=false;
                  break;
               }
               $i=$i+1;
            }
            if (!$file_save_well){
               foreach($file as $one_file){
                  $deleted=$one_file->delete();
                  if (!$deleted){
                     register_error(elgg_echo('questionsbank:filenotdeleted'));
                     forward("test/edit/$testpost");
                  }
          }
               register_error(elgg_echo('questionsbank:file_error_save'));
               forward("test/edit/$testpost");
            }
         }   
      }
      
      ////////////////////////////////////////////////////////////////   

      //Create new question
      $question = new ElggObject();
      $question->subtype = "questionsbank_question";
      $question->owner_guid = $user_guid;
      $question->container_guid = $questionsbank->container_guid;
      $question->access_id = $questionsbank->access_id;
      if (!$question->save()){
         if ($file_counter>0){
            foreach($file as $one_file){
               $deleted=$one_file->delete();
               if (!$deleted){
                  register_error(elgg_echo('questionsbank:filenotdeleted'));
                  forward("test/edit/$testpost");
               }
            }
         }
         register_error(elgg_echo('questionsbank:question_save_error'));
         forward("test/edit/$testpost");
      }        
      $question->question = $input_question;
      $question->question_html = $input_question_html;
      $question->question_explanation = $input_question_explanation;
      $question->question_type = $input_question_type;
      switch($input_question_type){
         case 'urls_files':
            $question->question_urls = $input_question_urls;
            break;
      }
      if ($file_counter>0){
         for($i=0; $i<$file_counter; $i++){
            add_entity_relationship($question->getGUID(),'question_file_link',$file[$i]->getGUID());
         }
      }

      $question->response_type = $input_response_type;
      switch ($input_response_type) {
         case 'radiobutton':
         case 'checkbox':
            $question->responses = $input_responses;
            break;
         case 'grid':
            $question->responses_rows = $input_responses_rows;
            $question->responses_columns = $input_responses_columns;
            break;
         case 'pairs':
            $question->responses_left = $input_responses_left;
            $question->responses_right = $input_responses_right;
            break;
         case 'dropdown':
            $question->responses_dropdown = $input_responses_dropdown;
            $question->question_text = $text_dropdown;
            $question->numbers_responses_dropdowns = $numbers_responses_dropdowns;
            break;
      }
      
      $question->available_correct_responses = true;
      switch ($input_response_type) {
         case 'radiobutton':
            $question->correct_responses = $input_correct_responses;
            break;
         case 'checkbox':
            $question->correct_responses = $input_correct_responses;
            break;
         case 'grid':
            $question->correct_responses = $input_correct_responses;
            break;
         case 'pairs':
            $question->correct_responses = $input_correct_responses;
            break;
         case 'dropdown':
            $question->correct_responses = $input_correct_responses;
            break;
      }
      $question->tags = $input_tags;
      $question->index = $index_questions_bank;

      add_entity_relationship($questionsbankpost,'questionsbank_question',$question->getGUID());
      
      if ($index == "all")
         $index_test = $index_test+1;
      else {
         system_message(elgg_echo("questionsbank:updated"));
         forward("test/edit/$testpost");
      }
   }
}

?>
