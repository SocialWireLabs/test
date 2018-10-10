<?php

gatekeeper();

//Get the test entity
$testpost = get_input('testpost');
$test = get_entity($testpost);

if ($test->getSubtype() == "test") {
   $now = time();
   if (test_check_status($test)){

      $selected_action = get_input('submit');
      $container_guid  = $test->container_guid;
      $container = get_entity($container_guid);
      $user_guid = get_input('user_guid');
      $user = get_entity($user_guid);
      $index = get_input('index');

      if ($test->subgroups){
         $user_subgroup = elgg_get_entities_from_relationship(array('type_subtype_pairs' => array('group' => 'lbr_subgroup'),'container_guids' => $container_guid,'relationship' => 'member','inverse_relationship' => false,'relationship_guid' => $user_guid));
         $user_subgroup=$user_subgroup[0];
         $user_subgroup_guid=$user_subgroup->getGUID();
      }

      //Answers
      if (!$test->subgroups) {
         $options = array('relationship' => 'test_answer', 'relationship_guid' => $test->getGUID(),'inverse_relationship' => false, 'type' => 'object', 'subtype' => 'test_answer', 'order_by' => 'e.time_created desc', 'limit' => 0, 'owner_guid' => $user_guid);
      } else {
         $options = array('relationship' => 'test_answer', 'relationship_guid' => $test->getGUID(),'inverse_relationship' => false, 'type' => 'object', 'subtype' => 'test_answer', 'order_by' => 'e.time_created desc', 'limit' => 0, 'container_guid' => $user_subgroup_guid);
      }
      $user_responses=elgg_get_entities_from_relationship($options);
      if (!empty($user_responses)){
         $user_response=$user_responses[0];
      } else {
         $user_response="";
      }

      if (!$test->subgroups) {
         $options = array('relationship' => 'test_answer_draft', 'relationship_guid' => $test->getGUID(),'inverse_relationship' => false, 'type' => 'object', 'subtype' => 'test_answer_draft', 'order_by' => 'e.time_created desc', 'limit' => 0, 'owner_guid' => $user_guid);
      } else {
         $options = array('relationship' => 'test_answer_draft', 'relationship_guid' => $test->getGUID(),'inverse_relationship' => false, 'type' => 'object', 'subtype' => 'test_answer_draft', 'order_by' => 'e.time_created desc', 'limit' => 0, 'container_guid' => $user_subgroup_guid);
      }
      $user_responses_draft=elgg_get_entities_from_relationship($options);
      $user_response_draft=$user_responses_draft[0];
      $user_response_draft_content_array = explode(Chr(27),$user_response_draft->content);
      $user_response_draft_content_array = array_map('trim', $user_response_draft_content_array);
      $num_questions=$user_response_draft->num_questions;
      $answer_beginning_time = $user_response_draft->answer_beginning_time;

         
      $max_duration_minutes = $test->max_duration_minutes;
      $response_time = time()-$answer_beginning_time;
      $duration_minutes = $response_time/60;


      if ($test->random_questions){
         $selected_random_questions = explode(";",$user_response_draft->selected_random_questions);
         $selected_question_index = $selected_random_questions[$index];
      } else {
	 $selected_question_index = $index;
      }


      //Question
      $options = array('relationship' => 'test_question', 'relationship_guid' => $testpost,'inverse_relationship' => false, 'type' => 'object', 'subtype' => 'test_question', 'metadata_name_value_pairs' => array('name' => 'index', 'value' => $selected_question_index));
      $questions=elgg_get_entities_from_relationship($options);
      $one_question=$questions[0];   
      $grading=$one_question->grading;
      $response_type=$one_question->response_type;
      switch ($response_type){
        case 'radiobutton':
	 	case 'checkbox':
	    	$responses=$one_question->responses;
	    	$possible_response = explode(Chr(26),$responses);
            $possible_response = array_map('trim', $possible_response);
	    	break;
        case 'grid':
		    $responses_rows=$one_question->responses_rows;
		    $responses_rows_array=explode(Chr(26),$responses_rows);
		    $responses_rows_array=array_map('trim',$responses_rows_array);
		    $responses_columns=$one_question->responses_columns;
		    $possible_response = explode(Chr(26),$responses_columns);
            $possible_response = array_map('trim', $possible_response);
	    	break;
	    case 'pairs':
	    	$responses_left = explode(Chr(26),$one_question->responses_left);
			$responses_left = array_map('trim',$responses_left);
			$responses_right = explode(Chr(26),$one_question->responses_right);
			$possible_response = array_map('trim',$responses_right);
	    	break;
	    case 'dropdown':
			$responses_dropdown = explode(Chr(26),$one_question->responses_dropdown);
			$possible_response = array_map('trim',$responses_dropdown);
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
      
      $correct_responses=$one_question->correct_responses;
      if ((strcmp($response_type,"checkbox")==0)||(strcmp($response_type,"grid")==0)||(strcmp($response_type,"pairs")==0)||(strcmp($response_type,"dropdown")==0)){
        $correct_response = explode(Chr(26),$correct_responses);
        $correct_response = array_map('trim', $correct_response);
	 	if (strcmp($response_type,"grid")==0){
	    	$j=0;
	    	$correct_response_grid = array();
	    	foreach($correct_response as $one_correct_response){
	       		$correct_response_grid[$j]=$one_correct_response;
	       		$j=$j+1;
	    	}
	 	}
	 	if (strcmp($response_type,"pairs")==0){ 
	    	$j=0;
	    	$temp_correct_response = array();
	    	$temp_one_correct_response = array();
	    	foreach ($correct_response as $one_correct_response) {
	    		$temp_one_correct_response=explode(Chr(27), $one_correct_response);
	    		$temp_one_correct_response = array_map('trim', $temp_one_correct_response);
	    		$temp_correct_response=array_merge($temp_correct_response, $temp_one_correct_response);
	    	}
	    	$correct_response=$temp_correct_response;
	    	$correct_response_pairs = array();	
	    	foreach($correct_response as $one_correct_response){
	       		$correct_response_pairs[$j]=$one_correct_response;
	       		$j=$j+1;
	    	}
	 	}
	 	if (strcmp($response_type,"dropdown")==0){ 
	    	$j=0;
	    	$correct_response_dropdown = array();
	    	foreach($correct_response as $one_correct_response){
	       		$correct_response_dropdown[$j]=$one_correct_response;
	       		$j=$j+1;
	    	}
	 	}
      } else {
         $correct_response=$correct_responses;
      }   
   
      //Comments
      $comments=get_input('comments');
      if (strcmp($comments,"")==0)
         $comments="not_comments";
      
      //Response    
      switch ($response_type){
        case 'radiobutton':
	 	case 'checkbox':
       		$name_response="response";
                $response=get_input($name_response);
	    	break;
	 	case 'grid':
                $j=0;
	    	$response="";
	    	$response_grid = array();
	    	foreach($responses_rows_array as $one_row){
	       		$name_response = "grid_response_".$j;
	       		$one_response = get_input($name_response);
	       		if (empty($one_response))
	          		$one_response = "not_response";
	       		if ($j==0){
	          		$response .= $one_response;
	       		} else {
	          		$response .= Chr(26) . $one_response;
	       		}
	       		$response_grid[$j] = $one_response;
	       		$j=$j+1;
	    	}
	    	break;
	    case 'pairs':
	    	$j=0;
	    	$i=0;
	    	$response="";
	    	$response_pairs = array();
	    	$name_response= "respuestasOrdenadas";
       		$user_responses = explode(",",get_input($name_response));
       		$user_responses = array_map('trim', $user_responses);
	    	foreach($responses_left as $one_row){	       		       	
	       		$one_response=$user_responses[$i].Chr(26).$user_responses[$i+1];
	       		if (empty($one_response)||$one_response==Chr(26))
	          		$one_response = "not_response";
	       		if ($j==0){
	          		$response .= $one_response;
	       		} else {
	          		$response .= Chr(26) . $one_response;
	       		}
	       		$response_pairs[$j] = $user_responses[$i+1];
	       		$j=$j+1;
	       		$i+=2;
	    	}
	    	break;
	    case 'dropdown':
	    	$response="";
	    	$response_dropdown = array();
	    	$temp_number_responses=explode(",",$numbers_responses_dropdowns);                   
			$temp_number_responses = array_map('trim',$temp_number_responses);
	    	$number_selects = count($temp_number_responses);
	    	for($j=0;$j<$number_selects;$j++){
	       		$name_response = "dropdown_".($j+1);
	       		$one_response = get_input($name_response);
	       		if (empty($one_response))
	          		$one_response = "not_response";
	       		if ($j==0){
	          		$response .= $one_response;
	       		} else {
	          		$response .= Chr(26) . $one_response;
	       		}
	       		$response_dropdown[$j] = $one_response;
	    	}
	    	break;
      }     

      $content_fields = "";

      $i=0;
      $total_grading=0;
      $num_answers_bad = 0;
      $num_answers_bad_total = 0;

      while($i<$num_questions){
        //Prepare content_fields
	if ($test->random_questions){
	   $selected_question = $selected_random_questions[$i];
	} else {
	   $selected_question = $i;
	}
        if ($selected_question==$selected_question_index){
		    if ((strcmp($response_type,"radiobutton")==0)||(strcmp($response_type,"checkbox")==0)) {
	        	$this_response = "";
	            if (!empty($response)){
	            	if (is_array($response)){
		            	$first=true;
			     		foreach($response as $one_response){
			        		if ($first){
			           			$first=false;
				   				$this_response .= $one_response;
			        		} else {
			           			$this_response .= Chr(26) . $one_response;
			        		}
			     		}  
		          	} else {
		            	$this_response = $response;
		          	} 
		       	} else {
		        	$this_response = "not_response";
		       	}
		    }
	    	if (strcmp($response_type,"grid")==0||strcmp($response_type,"pairs")==0||strcmp($response_type,"dropdown")==0) { 
            	$this_response = $response;
	    	}
        } else {
        	$this_response = $user_response_draft_content_array[2*$i];
        }

        if (strcmp($content_fields,"")!=0) {
        	$content_fields .= Chr(27) . $this_response;
        } else {
	    	$content_fields .= $this_response;
        }	 

         //Check answers

	 if ($selected_question==$selected_question_index){
	   
	    $well=0;
	    $not_response = true;

	    switch ($response_type) {
	    	case 'radiobutton':
	    	case 'checkbox':
	    	case 'pairs':
	    		$possible=count($possible_response);
	    		break;
	    	case 'grid':
	    		$possible=count($responses_rows_array);
	    		break;
	    	case 'dropdown':
	    		$possible=$number_selects;
	    		break;
	    }

	    if ((strcmp($response_type,"radiobutton")==0)||(strcmp($response_type,"checkbox")==0)){	       
	       foreach ($possible_response as $one_possible_response){
	          if (strcmp($response_type,"checkbox")==0){
	             if (!empty($response)){		   
		        if (((in_array($one_possible_response,$response))&&(in_array($one_possible_response,$correct_response)))||((!in_array($one_possible_response,$response))&&(!in_array($one_possible_response,$correct_response)))){
		           $well=$well+1;
		        } 
		        $not_response = false;
		     } else {
		        if (!in_array($one_possible_response,$correct_response)){
		           $well=$well+1;
                         }
		     }
                  } else {
		     if (!empty($response)){		   
		        if (((strcmp($one_possible_response,$response)==0)&&(strcmp($one_possible_response,$correct_response)==0))){
		           $well=1;
		        }
		        $not_response = false;
		     } 
	          }
	       }
	    } else {
	       	if (strcmp($response_type,"grid")==0) { 
	        	$j=0;
	          	foreach ($responses_rows_array as $one_row){
	            	if (strcmp($response_grid[$j],'not_response')!=0)
		           $not_response = false;
	             	foreach ($possible_response as $one_possible_response){
                    	if ((strcmp($response_grid[$j],'not_response')!=0)&&(strcmp($one_possible_response,$response_grid[$j])==0)&&($one_possible_response==$correct_response_grid[$j])) {	             	                    		
	           	   $well = $well +1;
		   	   break;
		        }
		     }
		     $j = $j+1;
	          }
	    	}

	    	if (strcmp($response_type,"pairs")==0) { 
	        	$j=0;
	          	foreach ($responses_left as $one_left){
	            	if (strcmp($response_pairs[$j],'not_response')!=0)
		        		$not_response = false;
	             	foreach ($possible_response as $one_possible_response){                    
	             		if ((strcmp($possible_response[$response_pairs[$j]],'not_response')!=0)&&(strcmp($one_possible_response,$possible_response[$response_pairs[$j]/2-1])==0)) {
                    		$m=0;
                    		foreach ($correct_response_pairs as $one_correct_pair) {
                    			if($one_left==$one_correct_pair&&$one_possible_response==$correct_response_pairs[$m+1]){
                    			   $well = $well +1;
				           $break=true;
					   break;
                    			}                    						    
                    			$m++;
                    		}
                    		if($break){
			        	$break=false;
			        	break;
			        }		      
		        }
		     }
		     		$j = $j+1;
	          }
	    	}

	    	if (strcmp($response_type,"dropdown")==0) {
	    		$temp_numbers_responses_dropdowns=explode(",",$numbers_responses_dropdowns);            
				$temp_numbers_responses_dropdowns = array_map('trim',$temp_numbers_responses_dropdowns);
				$m=0;
				$j=0;
				$index_posible_responses=0;
	          	foreach ($temp_numbers_responses_dropdowns as $one_number){
	          		$temp_posible_responses=array();  
	          		for($p=0;$p<$one_number;$p++){
	          			$temp_posible_responses[$p]=$possible_response[$index_posible_responses];
	          			$index_posible_responses++;
	          		}
	          		$n=1;   
	             	foreach ($temp_posible_responses as $one_possible_response){	  
                    	if ((strcmp($response_dropdown[$j],'not_response')!=0)&&(strcmp($one_possible_response,$response_dropdown[$j])==0)&&($n==$correct_response_dropdown[$j])) {
		           			$well = $well +1;
			   				break;
		        		}
		        		$m++;		        				        	
		        		$n++;
		     		}
		     		$j = $j+1;
	          	}
	    	}
	    }
            				
	    if ((strcmp($response_type,"checkbox")==0)||(strcmp($response_type,"grid")==0)||(strcmp($response_type,"pairs")==0)||(strcmp($response_type,"dropdown")==0)){ 
	    	if($test->all_in_checkbox == 'proporcional'){
	        	$response_grading = ($grading*$well*1.0)/$possible;
	       	} else{
		  		if ($well != $possible){
		     		$response_grading = 0;
		  		} else{
		     		$response_grading =  $grading;
		  		}
	       }
	    } else { 
	       $response_grading = $grading*$well;
	    } 

	    if ($response_grading == 0 && !$not_response){
	       $num_answers_bad ++; 
	    }
	   
	    $total_grading = $total_grading+$response_grading; 
	    $content_fields .= Chr(27) . $response_grading;
	    if ($response_grading == 0){
               $num_answers_bad_total ++;
	    }
	 } else {
	    $other_response_grading = $user_response_draft_content_array[1+2*$i];
	    
	    $other_not_response = true;
	    $other_response = $user_response_draft_content_array[2*$i];
	    $other_response_array = explode(Chr(26),$other_response);
	    $other_response_array = array_map('trim',$other_response_array);
	    foreach ($other_response_array as $one_other_response){
	      if (strcmp($one_other_response,"not_response")!=0){
	         $other_not_response = false;
	      }
	    }
	    if ($other_response_grading == 0 && !$other_not_response){
	       $num_answers_bad ++;
	    }

	    $total_grading = $total_grading + $other_response_grading;
	    $content_fields .= Chr(27) . $other_response_grading;
	    if ($other_response_grading == 0)
	       $num_answers_bad_total ++;
         }
	 $i=$i+1;
      }

      if ((strcmp($selected_action,elgg_echo('test:answer'))==0)||(strcmp($selected_action,elgg_echo('test:answer_end'))==0)){
         if ($duration_minutes>$max_duration_minutes) {
	    system_message(elgg_echo("test:max_duration_exceeded"));
            forward(elgg_get_site_url() . 'test/group/' . $container_guid);
	 } else {
	    $found=false;
	    if (strcmp($selected_action,elgg_echo('test:answer_end')==0)){
	       if ($test->all_in_checkbox != 'proporcional'){
                  if (strcmp($test->num_cancel_questions,'0')!=0){ 
		     if ($test->penalty_not_response){
        	        $total_grading = $total_grading - ($grading/$test->num_cancel_questions) * $num_answers_bad_total;
		     } else { 
		      	$total_grading = $total_grading - ($grading/$test->num_cancel_questions) * $num_answers_bad;
		     }		
                  }
	       }
               if (strcmp($test->type_grading,'test_type_grading_game_points')==0){
        	  $total_grading = round($total_grading);
               }
               if ($total_grading < 0){
        	  $total_grading = 0;
               }
            }  
	    if (!empty($user_response)) {
	       $user_response->content=$content_fields;
	       $user_response->all_contents .= Chr(28).$content_fields;
	       $user_response->comments=$comments;
	       $user_response->all_comments .= Chr(28).$comments;
	       $user_response->grading = $total_grading;
	       $user_response->all_gradings .= ";".$total_grading;
	       $user_response->time = $response_time;
	       $user_response->all_times .=";".$response_time;
	       $attempts=$user_response->attempts+1;
	       $user_response->attempts=$attempts;
	       $found=true;      
	    }
	    if (!$found){
               // Initialise a new ElggObject to be the answer
	       $answer = new ElggObject();
	       $answer->subtype = "test_answer";
	       $answer->owner_guid = $user_guid;
	       if ($test->subgroups){
	          $answer->container_guid = $user_subgroup_guid;
		  $answer->access_id  = $user_subgroup->teachers_acl;
		  $answer->who_answers = 'subgroup';
	       } else { 
		  $answer->container_guid = $container_guid;
		  $answer->access_id = $container->teachers_acl;
		  $answer->who_answers = 'member';
	       }
	       if (!$answer->save()){
	          register_error(elgg_echo("test:answer_error_save"));
		  forward($_SERVER['HTTP_REFERER']);
	       }
		                
	       $answer->content = $content_fields;
	       $answer->all_contents = $content_fields;
	       $answer->comments = $comments;
	       $answer->all_comments = $comments;
	       $answer->num_questions = $num_questions;
	       $answer->grading = $total_grading;
	       $answer->all_gradings = $total_grading;
               $answer->time = $response_time;
	       $answer->all_times = $response_time;
	       $answer->attempts = 1;
	       if ($test->random_questions){
	          $answer->selected_random_questions = $user_response_draft->selected_random_questions; 
               }
	       add_entity_relationship($test->getGUID(),'test_answer',$answer->guid);
	       // Add response as an annotation
	       $test->annotate('all_responses', "1", $test->access_id); 
            }  

            // Delete draft
	    if (!empty($user_response_draft)){
	       $deleted=$user_response_draft->delete();
	       if (!$deleted){
	          register_error(elgg_echo('test:answernotdeleted'));
	          forward($_SERVER['HTTP_REFERER']);
	       }  
	    }

	    //System messages
	    system_message(elgg_echo("test:answered"));		
	    system_message(elgg_echo("test:your_mark"). ": " . $total_grading);	
            // Forward to the test listing page
	    forward(elgg_get_site_url() . 'test/group/' . $container_guid);
	 }
            
      } else {

	 if ($duration_minutes>$max_duration_minutes) {
	    system_message(elgg_echo("test:max_duration_exceeded"));
            forward(elgg_get_site_url() . 'test/group/' . $container_guid);
	 } else {
	    $user_response_draft->content=$content_fields;	 
	    $user_response_draft->comments = $comments;

	    if (strcmp($selected_action,elgg_echo('test:answer_next'))==0){
	       $index=$index+1;
	    } else {
	    	$index=$index-1;
	    }  
	    forward("test/view/$testpost/?index=$index&this_user_guid_answer=$user_guid");
	 }
      }
   } else {
      system_message(elgg_echo("test:closed"));
      forward($_SERVER['HTTP_REFERER']); 
   }
}

?>
