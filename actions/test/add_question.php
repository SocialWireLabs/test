<?php

gatekeeper();

$testpost = get_input('testpost');
$test = get_entity($testpost);

if ($test->getSubtype() == "test" && $test->canEdit()) {

   $user_guid = elgg_get_logged_in_user_guid();

   $input_question = get_input('question');
   $input_question_html = get_input('question_html');
   $input_question_explanation = get_input('question_explanation', '', false);
   $input_question_type = get_input('question_type');
   switch($input_question_type){
      case 'urls_files':
         $question_urls = get_input('question_urls');
         $question_urls = array_map('trim',$question_urls);
         $question_urls_names = get_input('question_urls_names');
         $question_urls_names = array_map('trim',$question_urls_names);
         $i=0;
         $input_question_urls = "";
         if ((count($question_urls)>0)&&(strcmp($question_urls[0],"")!=0)) {
            foreach($question_urls as $url){
               if ($i!=0)
	          $input_question_urls .= Chr(26);
               $input_question_urls .= $question_urls_names[$i] . Chr(24) . $question_urls[$i];
               $i=$i+1;
            }
         }
         $number_question_urls = count($question_urls);
         break;
   }
   $file_counter = count($_FILES['upload']['name']);
   $input_response_type = get_input('response_type');
   
   switch ($input_response_type) {
      case 'radiobutton':
      case 'checkbox':
         $responses = get_input('responses');
         $responses = array_map('trim', $responses);
         $input_responses = implode(Chr(26), $responses);
         $number_responses = count($responses);
         break;
      case 'grid':
         $responses_rows = get_input('responses_rows');
         $responses_rows = array_map('trim', $responses_rows);
         $input_responses_rows = implode(Chr(26), $responses_rows);
         $number_responses_rows = count($responses_rows);
         $responses_columns = get_input('responses_columns');
         $responses_columns = array_map('trim', $responses_columns);
         $input_responses_columns = implode(Chr(26), $responses_columns);
         $number_responses_columns = count($responses_columns);
         break;
      case 'pairs':
         $responses_left = get_input('responses_left');
         $responses_left = array_map('trim', $responses_left);
         $input_responses_left = implode(Chr(26),$responses_left);
         $number_responses_left = count($responses_left);
         $responses_right = get_input('responses_right');
         $responses_right = array_map('trim', $responses_right);
         $input_responses_right = implode(Chr(26),$responses_right);
         $number_responses_right = count($responses_right);
         break;
      case 'dropdown':
         $text_dropdown=get_input('text_dropdown');
         $numbers_responses_dropdowns=get_input('numbers_responses_dropdowns');
         $numbers_responses_dropdowns_array=explode(',', $numbers_responses_dropdowns);
         $numbers_responses_dropdowns_array=array_map('trim', $numbers_responses_dropdowns_array);            
         $number_dropdowns=get_input('number_dropdowns');
         $responses_dropdown=get_input('responses_dropdown');
         $responses_dropdown=array_map('trim', $responses_dropdown);
         $input_responses_dropdown=implode(Chr(26),$responses_dropdown);
         $number_responses_option_dropdown = count($responses_dropdown);
   }

   switch ($input_response_type) {   
      case 'radiobutton':
         $input_number_correct_response = get_input('number_correct_response');
         break;
      case 'checkbox':
         $input_numbers_correct_responses = get_input('numbers_correct_responses');
         if (!empty($input_numbers_correct_responses)) {
            $numbers_correct_responses = explode(',', $input_numbers_correct_responses);
            $numbers_correct_responses = array_map('trim', $numbers_correct_responses);
            $number_correct_responses = count($numbers_correct_responses);
         } else {
            $number_correct_responses = 0;
         }
         break;
      case 'grid':
         $input_numbers_correct_responses = get_input('grid_correct_responses');
         if (!empty($input_numbers_correct_responses)) {
            $numbers_correct_responses = explode(',', $input_numbers_correct_responses);
            $numbers_correct_responses = array_map('trim', $numbers_correct_responses);
            $number_correct_responses = count($numbers_correct_responses);
         } else {
            $number_correct_responses = 0;
         }
         break;
      case 'pairs':
         $input_numbers_correct_responses=get_input('pairs_correct_responses');
         if (!empty($input_numbers_correct_responses)){
            $numbers_correct_responses = explode(',',$input_numbers_correct_responses);
            $numbers_correct_responses = array_map('trim', $numbers_correct_responses);
            $number_correct_responses = count($numbers_correct_responses);
         } else {
            $number_correct_responses = 0;
         }
         break;
      case 'dropdown':
         $input_numbers_correct_responses=get_input('dropdown_correct_responses');
         if (!empty($input_numbers_correct_responses)){
            $numbers_correct_responses = explode(',',$input_numbers_correct_responses);
            $numbers_correct_responses = array_map('trim', $numbers_correct_responses);
            $number_correct_responses = count($numbers_correct_responses);
         } else {
            $number_correct_responses = 0;
         }
         break;
   }
  
   $input_question_tags = get_input('question_tags');
   //Convert string of tags into a preformatted array
   $questiontagsarray = string_to_tag_array($input_question_tags);

   $options = array('relationship' => 'test_question', 'relationship_guid' => $testpost,'inverse_relationship' => false, 'type' => 'object', 'subtype' => 'test_question','limit'=>0);
   $questions=elgg_get_entities_from_relationship($options);
   if (empty($questions)) {
      $num_questions=0;
   } else {
      $num_questions=count($questions);
   }
   $index=$num_questions;

   // Cache to the session
   elgg_make_sticky_form('add_question_test');

   // Make sure the question isn't blank
   if (strcmp($input_question,"")==0) {
      register_error(elgg_echo("test:question_blank"));
      forward("test/add_question/$testpost/$input_question_type");
   }

   // Question urls
   if (strcmp($input_question_type,"urls_files")==0){
      $blank_question_url=false;
      $questionurlsarray=array();
      $i=0;
      foreach($question_urls as $one_url){
         $questionurlsarray[$i]=$one_url;
	 if (strcmp($one_url,"")==0){
            $blank_question_url=true;
	    break;
	 }
	 $i=$i+1;
      }
      $blank_question_name=false;
      if (!$blank_question_url){
         foreach($question_urls_names as $one_url_name){
	    if (strcmp($one_url_name,"")==0){
               $blank_question_name=true;
	       break;
	    }
         }
      }
      if (($blank_question_url)&&($number_question_urls>1)){
         register_error(elgg_echo("test:url_blank"));
	 forward("test/add_question/$testpost/$input_question_type");
      }
      $same_question_url=false;
      $i=0;
      while(($i<$number_question_urls)&&(!$same_question_url)){
         $j=$i+1;
	 while($j<$number_question_urls){
	    if (strcmp($questionurlsarray[$i],$questionurlsarray[$j])==0){
               $same_question_url=true;
	       break;
	    }
	    $j=$j+1;
	 }
	 $i=$i+1;
      }
      if ($same_question_url){
         register_error(elgg_echo("test:url_repetition"));
	 forward("test/add_question/$testpost/$input_question_type");
      }
      if (!$question_url_blank){
         foreach($question_urls as $url){
            $xss_test = "<a rel=\"nofollow\" href=\"$url\" target=\"_blank\">$url</a>";
            if ($xss_test != filter_tags($xss_test)) {
               register_error(elgg_echo('test:url_failed'));
	       forward("test/add_question/$testpost/$input_question_type");
            }
         }
      }
   }

   // Responses
   if ((strcmp($input_response_type, "radiobutton") == 0) || (strcmp($input_response_type, "checkbox") == 0)) {
      $blank_response=false;
      $responsesarray=array();
      $i=0;
      foreach($responses as $one_response){
         $responsesarray[$i]=$one_response;
         if (strcmp($one_response,"")==0){
            $blank_response=true;
            break;
         }
         $i=$i+1;
      }
      if ($blank_response){
         register_error(elgg_echo("test:responses_blank"));
         forward("test/add_question/$testpost/$input_question_type");
      }
      $same_response=false;
      $i=0;
      while(($i<$number_responses)&&(!$same_response)){
         $j=$i+1;
         while($j<$number_responses){
            if (strcmp($responsesarray[$i],$responsesarray[$j])==0){
               $same_response=true;
   	    break;
   	 }
   	 $j=$j+1;
         }
         $i=$i+1;
      }
      if ($same_response){
         register_error(elgg_echo("test:response_repetition"));
         forward("test/add_question/$testpost/$input_question_type");
      }
      if ($number_responses<2){
         register_error(elgg_echo("test:response_only_one_option"));
         forward("test/add_question/$testpost/$input_question_type");
      }
   }
   if (strcmp($input_response_type, "grid") == 0) {
      $blank_response = false;
      $responsesrowsarray = array();
      $i = 0;
      foreach ($responses_rows as $one_response) {
         $responsesrowsarray[$i] = $one_response;
         if (strcmp($one_response, "") == 0) {
            $blank_response = true;
            break;
         }
         $i = $i + 1;
      }
      if ($blank_response) {
         register_error(elgg_echo("test:row_blank"));
         forward("test/add_question/$testpost/$input_question_type");
      }

      $same_response = false;
      $i = 0;
      while (($i < $number_responses_rows) && (!$same_response)) {
         $j = $i + 1;
         while ($j < $number_responses_rows) {
            if (strcmp($responsesrowsarray[$i], $responsesrowsarray[$j]) == 0) {
               $same_response = true;
               break;
            }
            $j = $j + 1;
         }
         $i = $i + 1;
      }
      if ($same_response) {
         register_error(elgg_echo("test:row_repetition"));
         forward("test/add_question/$testpost/$input_question_type");
      }
      $blank_response = false;
      $responsescolumnsarray = array();
      $i = 0;
      foreach ($responses_columns as $one_response) {
         $responsescolumnsarray[$i] = $one_response;
         if (strcmp($one_response, "") == 0) {
            $blank_response = true;
            break;
         }
         $i = $i + 1;
      }
      if ($blank_response) {
         register_error(elgg_echo("test:column_blank"));
         forward("test/add_question/$testpost/$input_question_type");
      }
      $same_response = false;
      $i = 0;
      while (($i < $number_responses_columns) && (!$same_response)) {
         $j = $i + 1;
         while ($j < $number_responses_columns) {
            if (strcmp($responsescolumnsarray[$i], $responsescolumnsarray[$j]) == 0) {
               $same_response = true;
               break;
            }
            $j = $j + 1;
         }
         $i = $i + 1;
      }
      if ($same_response) {
         register_error(elgg_echo("test:column_repetition"));
         forward("test/add_question/$testpost/$input_question_type");
      }
      
      if ($number_responses_columns < 2) {
         register_error(elgg_echo("test:column_only_one_option"));
         forward("test/add_question/$testpost/$input_question_type");
      }
   }

   if (strcmp($input_response_type,"pairs")==0){
        $blank_response=false;
        $responsesleftarray=array();
        $i=0;
        foreach($responses_left as $one_response){
            $responsesleftarray[$i]=$one_response;
            if (strcmp($one_response,"")==0){
                $blank_response=true;
                break;
            }
            $i=$i+1;
        }            
        if ($blank_response){
            register_error(elgg_echo("test:left_blank"));
            forward("test/add_question/$testpost/$input_question_type");
        }

        $blank_response=false;
        $responsesrightarray=array();
        $i=0;
        foreach($responses_right as $one_response){
            $responsesrightarray[$i]=$one_response;
            if (strcmp($one_response,"")==0){
                $blank_response=true;
                break;
            }
            $i=$i+1;
        }            
        if ($blank_response){
            register_error(elgg_echo("test:right_blank"));
            forward("test/add_question/$testpost/$input_question_type");
        }

        if($number_responses_left != $number_responses_right){
            register_error(elgg_echo("test:not_same_numbers_left_right"));
            forward("test/add_question/$testpost/$input_question_type");
        }

        if ($number_responses_right<2){
            register_error(elgg_echo("test:only_one_pair"));
            forward("test/add_question/$testpost/$input_question_type");
        } 
    }

    if (strcmp($input_response_type,"dropdown")==0){
        $blank_response=false;
        $i=0;
        foreach($responses_dropdown as $one_response){
            if (strcmp($one_response,"")==0){
                $blank_response=true;
                break;
            }
            $i=$i+1;
        }            
        if ($blank_response){
            register_error(elgg_echo("test:option_dropdown_blank"));
            forward("test/add_question/$testpost/$input_question_type");
        }

        $same_response = false;
        $i = 0;
        $j = 0;
        $i_end = $numbers_responses_dropdowns_array[0];
        while (($i < count($numbers_responses_dropdowns_array)) && (!$same_response)) {
            while (($j<$i_end)&&(!$same_response)) {
                $k=$j+1;
                while ($k<$i_end) {
                    if (strcmp($responses_dropdown[$j],$responses_dropdown[$k])==0) {
                        $same_response = true;
                        break;
                    }
                    $k=$k+1;
                }
                $j=$j+1;
            }
            $i ++;
            $j = $i_end;
            $i_end = $j+$numbers_responses_dropdowns_array[$i];
        }

        if ($same_response) {
            register_error(elgg_echo("test:dropdown_repetition"));
            forward("test/add_question/$testpost/$input_question_type");
        }

         //There are always two options at least in each dropdown.
        if ($number_responses_option_dropdown<2){
            register_error(elgg_echo("test:dropdown_only_one_option"));
            forward("test/add_question/$testpost/$input_question_type");
        } 
    }

   // Correct responses
   switch ($input_response_type) {
      case 'radiobutton':
         $is_integer = true;
         $mask_integer='^([[:digit:]]+)$';
         if (ereg($mask_integer,$input_number_correct_response,$same)){
            if ((substr($same[1],0,1)==0)&&(strlen($same[1])!=1)){
               $is_integer=false;
            }
         } else {
            $is_integer=false;
         }
         if (!$is_integer){
            register_error(elgg_echo("test:bad_number_correct_response"));
            forward("test/add_question/$testpost/$input_question_type");
         }
         if (($input_number_correct_response==0)||($input_number_correct_response>$number_responses)){
            register_error(elgg_echo("test:bad_number_correct_responses"));
            forward("test/add_question/$testpost/$input_question_type");
         }
         $input_correct_responses = "";
         $i=1;
         foreach ($responses as $one_response){
            if ($i==$input_number_correct_response){
               $input_correct_responses .= $one_response;
               break;
            }
            $i=$i+1;
         }
         break;
      
      case 'checkbox':
         foreach($numbers_correct_responses as $one_number){
            $is_integer = true;
            $mask_integer='^([[:digit:]]+)$';
            if (ereg($mask_integer,$one_number,$same)){
               if ((substr($same[1],0,1)==0)&&(strlen($same[1])!=1)){
                  $is_integer=false;
               }
            } else {
               $is_integer=false;
            }
            if (!$is_integer){
               register_error(elgg_echo("test:bad_number_correct_response"));
               forward("test/add_question/$testpost/$input_question_type");
            }
            if ($one_number>$number_responses){
               register_error(elgg_echo("test:bad_number_correct_responses"));
               forward("test/add_question/$testpost/$input_question_type");
            }
         }
         $input_correct_responses = "";
         if ($number_correct_responses>0){
            $i=0;
            $j=0;
            foreach ($responses as $one_response){
               if (in_array($i+1,$numbers_correct_responses)){
                  if ($j==0) {
                     $input_correct_responses .= $one_response;
                  } else {
                     $input_correct_responses .= Chr(26) . $one_response;
                  }
                  $j=$j+1;
               }
               $i=$i+1;
            }
         } else {
             register_error(elgg_echo("test:bad_number_correct_responses"));
             forward("test/add_question/$testpost/$input_question_type");
         }   
         break;

      case 'grid':
         foreach ($numbers_correct_responses as $one_number) {
            $is_integer = true;
            $mask_integer = '^([[:digit:]]+)$';
            if (ereg($mask_integer, $one_number, $same)) {
               if ((substr($same[1], 0, 1) == 0) && (strlen($same[1]) != 1)) {
                  $is_integer = false;
               }
            } else {
               $is_integer = false;
            }
            if (!$is_integer) {
               register_error(elgg_echo("test:bad_number_correct_response"));
               forward("test/add_question/$testpost/$input_question_type");
            }
            if (($one_number==0)||($one_number > $number_responses_columns)) {
               register_error(elgg_echo("test:bad_number_correct_responses"));
               forward("test/add_question/$testpost/$input_question_type");
            }
         }
         $input_correct_responses = "";
         if ($number_correct_responses == $number_responses_rows) {
            $j = 0;
            foreach ($numbers_correct_responses as $one_number) {
               $i = 1;
               foreach ($responses_columns as $one_response) {
                  if ($i == $one_number) {
                     if ($j == 0) {
                        $input_correct_responses .= $one_response;
                     } else {
                        $input_correct_responses .= Chr(26) . $one_response;
                     }
                     $j = $j + 1;
                     break;
                  }
                  $i = $i + 1;
               }
            }
         } else {
            register_error(elgg_echo("test:bad_number_correct_responses"));
            forward("test/add_question/$testpost/$input_question_type");
         }
         break;
      case 'pairs':
         if ($number_correct_responses == 0) {
            register_error(elgg_echo("test:bad_number_correct_responses"));
            forward("test/add_question/$testpost/$input_question_type");
         }
         $input_correct_responses = "";
         $array_input_correct_responses = array();
         $array_one_number = array();
         $i=0;
         foreach ($numbers_correct_responses as $one_number){
            $array_one_number = explode('-', $one_number);
            $is_integer = true;
            $mask_integer='^([[:digit:]]+)$'; 
            if (ereg($mask_integer,$array_one_number[0],$same_left)&&ereg($mask_integer,$array_one_number[1],$same_right)){
               if ((substr($same_left[1],0,1)==0)&&(strlen($same_left[1])!=1)||(substr($same_right[1],0,1)==0)&&(strlen($same_right[1])!=1)){
                  $is_integer=false;
               }
            } else {
               $is_integer=false;
            }   
            if (!$is_integer){
               register_error(elgg_echo("test:bad_number_correct_response"));
               forward("test/add_question/$testpost/$input_question_type");
            }
            if (($array_one_number[0]==0)||($array_one_number[1]==0)){
               register_error(elgg_echo("test:bad_number_correct_responses"));
               forward("test/add_question/$testpost/$input_question_type");
            }
            if ($array_one_number[0]>$number_responses_right||$array_one_number[1]>$number_responses_right){
               register_error(elgg_echo("test:bad_number_correct_responses"));
               forward("test/add_question/$testpost/$input_question_type");
            }

            $j=1;
            foreach ($responses_left as $one_response_left) {
               if ($j==$array_one_number[0]) {
                  $new_input_correct_response = $one_response_left;
                  break;       
               }
               $j=$j+1;
            }
            $k=1;
            foreach ($responses_right as $one_response_right) {
               if ($k==$array_one_number[1]) {
                  $new_input_correct_response .= Chr(26) . $one_response_right;
                  break;       
               }
               $k=$k+1;
            }
            if (!in_array($new_input_correct_response,$array_input_correct_responses)){
               $array_input_correct_responses[$i] = $new_input_correct_response;
               $i=$i+1;
            } 
         }  
         $i=0;
         foreach ($array_input_correct_responses as $one_input_correct_response) {
            if ($i==0) {
               $input_correct_responses = $one_input_correct_response;
            } else {
               $input_correct_responses .= Chr(27) . $one_input_correct_response;    
            }
            $i=$i+1;
         } 
         break;
      case 'dropdown':
         $i=0;
         foreach($numbers_correct_responses as $one_number){
            $is_integer = true;
            $mask_integer='^([[:digit:]]+)$'; 
            if (ereg($mask_integer,$one_number,$same)){
               if ((substr($same[1],0,1)==0)&&(strlen($same[1])!=1)){
                  $is_integer=false;
               }
            } else {
               $is_integer=false;
            }   
            if (!$is_integer){
               register_error(elgg_echo("test:bad_number_correct_response"));
               forward("test/add_question/$testpost/$input_question_type");
            }
            if (($one_number==0)||($one_number>$numbers_responses_dropdowns_array[$i])){
               register_error(elgg_echo("test:bad_number_correct_responses"));
               forward("test/add_question/$testpost/$input_question_type");
            }
            $i++;
         }
         if($number_correct_responses!=$number_dropdowns){
            register_error(elgg_echo("test:bad_number_correct_responses"));
            forward("test/add_question/$testpost/$input_question_type");
         }
         $i=0;
         $input_correct_responses = "";
         foreach ($numbers_correct_responses as $one_number) {
            if ($i==0) {
               $input_correct_responses = $one_number;
            } else {
               $input_correct_responses .= Chr(26) . $one_number;    
            }
            $i=$i+1;
         } 
         break;
   }

   // Before we can set metadata, we need to save the test post
   if (!$test->save()) {
      register_error(elgg_echo("test:error_save"));
      forward("test/edit/$testpost");
   }

   if ((strcmp($input_question_type,"urls_files")==0)&&($_FILES['upload']['name'][0] == "")&&($number_question_urls==0)){
      register_error(elgg_echo('test:not_question_urls_files'));
      forward("test/add_question/$testpost/$input_question_type");
   }

   if (($file_counter>0)&&($_FILES['upload']['name'][0] != "")){
      $file_save_well=true;
      $file=array();
      for($i=0; $i<$file_counter; $i++){
         $file[$i] = new QuestionsTestPluginFile();
         $file[$i]->subtype = "test_question_file";
         $prefix = "file/";
	 $filestorename = elgg_strtolower(time().$_FILES['upload']['name'][$i]);
         $file[$i]->setFilename($prefix.$filestorename);
         $file[$i]->setMimeType($_FILES['upload']['type'][$i]);
         $file[$i]->originalfilename = $_FILES['upload']['name'][$i];
         $file[$i]->simpletype = elgg_get_file_simple_type($_FILES['upload']['type'][$i]);
	 $file[$i]->open("write");
	 if (isset($_FILES['upload']) && isset($_FILES['upload']['error'][$i])) {
            $uploaded_file = file_get_contents($_FILES['upload']['tmp_name'][$i]);
         } else {
            $uploaded_file = false;
         }
         $file[$i]->write($uploaded_file);
         $file[$i]->close();
         $file[$i]->title = $_FILES['upload']['name'][$i];
	 $file[$i]->owner_guid = $user_guid;
	 $file[$i]->container_guid = $test->container_guid;
         $file[$i]->access_id = $test->access_id;
         $file_save = $file[$i]->save();
         if (!$file_save) {
            $file_save_well=false;
	    break;
         }
      }
      if (!$file_save_well){
         foreach($file as $one_file){
	    $deleted=$one_file->delete();
	    if (!$deleted){
	       register_error(elgg_echo('test:filenotdeleted'));
	       forward("test/add_question/$testpost/$input_question_type");
	    }
	 }
	 register_error(elgg_echo('test:file_error_save'));
	 forward("test/add_question/$testpost/$input_question_type");
      }
   }

   ////////////////////////////////////////////////////////////////

   //Create new question
   $question = new ElggObject();
   $question->subtype = "test_question";
   $question->owner_guid = $user_guid;
   $question->container_guid = $test->container_guid;
   $question->access_id = $test->access_id;
   if (!$question->save()){
      if (($file_counter>0)&&($_FILES['upload']['name'][0] != "")){
         foreach($file as $one_file){
	    $deleted=$one_file->delete();
	    if (!$deleted){
	       register_error(elgg_echo('test:filenotdeleted'));
	       forward("test/add_question/$testpost/$input_question_type");
	    }
         }
      }
      register_error(elgg_echo('test:question_error_save'));
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
   if (($file_counter>0)&&($_FILES['upload']['name'][0] != "")){
      for($i=0; $i<$file_counter; $i++){
         add_entity_relationship($question->getGUID(),'question_file_link',$file[$i]->getGUID());
      }
   }

   if (($test->random_questions)&&($test->num_random_questions<($num_questions+1))){
      $num_questions = $test->num_random_questions;
   } else {
      $num_questions = $num_questions+1;
   }

   if (strcmp($test->type_grading,'test_type_grading_marks')==0){
         $question->grading = ($test->max_mark*1.0)/$num_questions;
	 foreach($questions as $one_question){
	    $one_question->grading = ($test->max_mark*1.0)/$num_questions;
	 }
      } else {
	 $question->grading = $test->question_max_game_points/$num_questions;
         foreach($questions as $one_question){
            $one_question->grading = $test->question_max_game_points/$num_questions;
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
         $question->question_text = $text_dropdown;
         $question->numbers_responses_dropdowns = $numbers_responses_dropdowns;
         $question->responses_dropdown = $input_responses_dropdown;
         break;
   }
   
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

   if (is_array($questiontagsarray)) {
      $question->tags = $questiontagsarray;
   }
   $question->index = $index;
   add_entity_relationship($test->getGUID(),'test_question',$question->getGUID());

   // Remove the test post cache
   elgg_clear_sticky_form('add_question_test');

   // Add to river
   //if ($test->created)
   //   add_to_river('river/object/test/update','update',$user_guid,$testpost);

   // Forward
   forward("test/view/$testpost");
}

?>
