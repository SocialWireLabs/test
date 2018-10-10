<?php

elgg_load_library('test');

$full = elgg_extract('full_view', $vars, FALSE);
$test = elgg_extract('entity', $vars, FALSE);
$user_type = elgg_extract('user_type', $vars, FALSE);
if (strcmp($user_type,"operator")!=0){
   $attempts =  elgg_extract('attempts', $vars, FALSE);
   $grading =  elgg_extract('grading', $vars, FALSE);
   $time =  elgg_extract('time', $vars, FALSE);
   $total_time = elgg_extract('total_time', $vars, FALSE);
   $time_minutes = $time/60;
   $total_time_minutes = $total_time/60;
}

if (!$test) {
   return TRUE;
}

$owner = $test->getOwnerEntity();
$owner_icon = elgg_view_entity_icon($owner, 'tiny');
$owner_link = elgg_view('output/url', array('href' => $owner->getURL(),'text' => $owner->name,'is_trusted' => true));
$author_text = elgg_echo('byline', array($owner_link));
$tags = elgg_view('output/tags', array('tags' => $test->tags));
$date = elgg_view_friendly_time($test->time_created);
$metadata = elgg_view_menu('entity', array('entity' => $test,'handler' => 'test','sort_by' => 'priority','class' => 'elgg-menu-hz'));
$subtitle = "$author_text $date $comments_link";

//////////////////////////////////////////////////
//Test information

$owner_guid = $owner->getGUID();
$user_guid = elgg_get_logged_in_user_guid();
$user = get_entity($user_guid);
$group_guid=$test->container_guid;
$group = get_entity($group_guid);
$group_owner_guid = $group->owner_guid;
$testpost = $test->getGUID();
$created = $test->created;
$opened = test_check_status($test);

$operator=false;
if (($owner_guid==$user_guid)||($group_owner_guid==$user_guid)||(check_entity_relationship($user_guid,'group_admin',$group_guid))){
   $operator=true;
}

//Open interval
if ($opened){
   if ((strcmp($test->option_activate_value,'test_activate_date')==0)&&(strcmp($test->option_close_value,'test_close_date')==0)){

      $friendlytime_from=date("d/m/Y",$test->activate_time) . " " . elgg_echo("test:at") . " " . date("G:i",$test->activate_time);
      $friendlytime_to=date("d/m/Y",$test->close_time) . " " . elgg_echo("test:at") . " " . date("G:i",$test->close_time);
      $open_interval=elgg_echo('test:opened_from') . ": " . $friendlytime_from . " " . elgg_echo('test:to') . ": " . $friendlytime_to;

   } elseif (strcmp($test->option_activate_value,'test_activate_date')==0) {
       $friendlytime_from=date("d/m/Y",$test->activate_time) . " " . elgg_echo("test:at") . " " . date("G:i",$test->activate_time);
       $open_interval=elgg_echo('test:opened_from') . ": " . $friendlytime_from;
   } elseif (strcmp($test->option_close_value,'test_close_date')==0) {
       $friendlytime_to=date("d/m/Y",$test->close_time) . " " . elgg_echo("test:at") . " " . date("G:i",$test->close_time);
       $open_interval=elgg_echo('test:opened_to') . ": " . $friendlytime_to;
   } else {
      $open_interval = elgg_echo('test:is_opened');
   }
} else {
   $open_interval = elgg_echo('test:is_closed');
   if (elgg_is_active_plugin('event_manager')) {
        $event_guid = $test->event_guid;
        if ($event = get_entity($event_guid)) {
            $now = time();
            if ($now > $test->close_time)
                $deleted = $event->delete();
        }
    }

   //Delete answers drafts

   $container_guid=$test->container_guid;
   $options = array('relationship' => 'test_answer_draft', 'relationship_guid' => $testpost,'inverse_relationship' => false, 'type' => 'object', 'subtype' => 'test_answer_draft','limit'=>0);
   $users_responses_draft=elgg_get_entities_from_relationship($options);

   if (!empty($users_responses_draft)) {
      foreach($users_responses_draft as $one_response_draft){
         $deleted=$one_response_draft->delete();
      }
   }
}

///////////////////////////////////////////////////////////////////
//Links to actions
if (($test->canEdit())&&($operator)) {
   if ($created) {
      if ($opened) {
         //Close
         $url_close = elgg_add_action_tokens_to_url(elgg_get_site_url() . "action/test/close?edit=no&testpost=" . $testpost);
         $word_close = elgg_echo("test:close_in_listing");
         $link_open_close = "<a href=\"{$url_close}\">{$word_close}</a>";
      } else {
         //Open
         $url_open = elgg_add_action_tokens_to_url(elgg_get_site_url() . "action/test/open?testpost=" . $testpost);
         $word_open = elgg_echo("test:open_in_listing");
         $link_open_close = "<a href=\"{$url_open}\">{$word_open}</a>";
      }
   } else {
      $url_publish = elgg_add_action_tokens_to_url(elgg_get_site_url() . "action/test/publish?testpost=" . $testpost);
      $word_publish = elgg_echo("test:publish");
      $link_publish = "<a href=\"{$url_publish}\">{$word_publish}</a>";
   }
}

if (($operator)&&(empty($vars['first_user_guid']))&&(empty($vars['this_user_guid_show_answer']))){
   if (!$test->subgroups){
      $members = $group->getMembers(array('limit'=>false));
   } else {
      $members=elgg_get_entities(array('type_subtype_pairs' => array('group' => 'lbr_subgroup'),'limit' => 0,'container_guids' => $group_guid));
   }
   $members = test_my_sort($members,"name",false);
   $i=0;
   $membersarray=array();
   foreach ($members as $member){
      $member_guid=$member->getGUID();
      if (($member_guid!=$owner_guid)&&($group_owner_guid!=$member_guid)&&(!check_entity_relationship($member_guid,'group_admin',$group_guid))){
         if (!$test->subgroups){
	    $options = array('relationship' => 'test_answer', 'relationship_guid' => $testpost,'inverse_relationship' => false, 'type' => 'object', 'subtype' => 'test_answer', 'limit' => 0, 'owner_guid' => $member_guid);
         } else {
	    $options = array('relationship' => 'test_answer', 'relationship_guid' => $testpost,'inverse_relationship' => false, 'type' => 'object', 'subtype' => 'test_answer', 'limit' => 0, 'container_guid' => $member_guid);
	 }
	 $user_responses=elgg_get_entities_from_relationship($options);
      	 if (!empty($user_responses)){
            $membersarray[$i] = $member;
	    $i=$i+1;
      	 }
      }
   }

   $num_responses = $i;
   if ($num_responses != 1) {
      $label_num_responses = elgg_echo('test:num_responses');
   } else {
      $label_num_responses = elgg_echo('test:num_response');
   }
}

$num_comments = $test->countComments();
if ($num_comments != 1) {
   $label_num_comments = elgg_echo('test:num_comments');
} else {
   $label_num_comments = elgg_echo('test:num_comment');
}

if ($full) {
   if (!test_check_status($test)) {
      $title="<div class=\"test_title\"><a class=\"closed_title_test\" href=\"{$test->getURL()}\">{$test->title}</a></div>";
   } else {
      $title="<div class=\"test_title\"><a class=\"opened_title_test\" href=\"{$test->getURL()}\">{$test->title}</a></div>";
   }
   $params = array('entity' => $test,'title' => $title,'metadata' => $metadata,'subtitle' => $subtitle,'tags' => $tags);
   $params = $params + $vars;
   $summary = elgg_view('object/elements/summary', $params);
   $body = "";

   $body .= $open_interval;

   if ($test->assessable){
      $body .= "<br>" . elgg_echo('test:assessable');
   } else {
      $body .= "<br>" . elgg_echo('test:notassessable');
   }

   //Links to actions
    if ($test->canEdit() && $operator) {
      if ($created) {
         $body .= "<br>" . $link_open_close;
      } else {
         $body .= "<br>" . $link_publish;
      }
    }
   $body .= "<br><br>";

   ///////////////////////////////////////////////////////////

   if (($operator)&&(empty($vars['first_user_guid']))&&(empty($vars['this_user_guid_show_answer']))) {

      $body .= "<div class=\"test_frame\">";

      $options = array('relationship' => 'test_question', 'relationship_guid' => $testpost,'inverse_relationship' => false, 'type' => 'object', 'subtype' => 'test_question','limit'=>0);
      $questions=elgg_get_entities_from_relationship($options);

      if (empty($questions)) {
         $num_questions = 0;
      } else {
         $num_questions = count($questions);
         $only_simple_choice = true;
	 foreach($questions as $one_question) {
	    if (strcmp($one_question->response_type,'radiobutton')!=0){
	       $only_simple_choice = false;
	       break;
	    }
	 }
      }

      if (strcmp($test->description,"")!=0){
         $body .= "<p>" . "<b>" . elgg_echo('test:test_description_label') . "</b>" . "</p>";
	 $body .= "<div class=\"test_question_frame\">";
         $body .= elgg_view('output/longtext', array('value' => $test->description));
	 $body .= "</div>";
      }

      if ($test->max_duration_minutes == 999999999){
         $max_duration_minutes_text = '<b>' . elgg_echo('test:max_duration_label' ) . ": " . '</b>' . elgg_echo('test:unlimited');
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

      if (($test->random_questions)&&($test->num_random_questions<$num_questions)){
         $num_questions_text = '<b>' . elgg_echo('test:num_questions_label') . " " . '</b>' . $test->num_random_questions;
      } else {
         $num_questions_text = '<b>' . elgg_echo('test:num_questions_label') . " " . '</b>' . $num_questions;
      }

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

      switch($test->type_max_attempts){
         case 'test_unlimited_attempts':
            $attempts_text = '<b>' . elgg_echo('test:attempts_unlimited') . elgg_echo('test:mark:warning') . '</b>';
            break;
         case 'test_limited_attempts':
           $attempts_text = '<b>' . elgg_echo('test:max_attempts_label') . ": " . '</b>' . $test->max_attempts . '<b>' . elgg_echo('test:mark:warning') . '</b>';
            break;
         case 'test_max_grading_limited_attempts':
            $attempts_text = '<b>' . elgg_echo('test:attempts_limited_max_grading') . elgg_echo('test:mark:warning') .'</b>' ;
            break;
      }

      $body .= $max_duration_text . "<br>";
      $body .= $max_grading_text . "<br>";
      $body .= $num_questions_text . "<br>";
      $body .= $num_cancel_questions_text . "<br>";
      if (strcmp($test->num_cancel_questions,'0')!=0){
         $body .= $penalty_not_response_text . "<br>";
      }
      if (!$only_simple_choice)
         $body .= $all_in_checkbox_text . "<br>";
      $body .= $attempts_text . "<br>";

      $body .= "<br>";

      //Add question
      $url_add_question = elgg_add_action_tokens_to_url(elgg_get_site_url() . "test/add_question/" . $testpost);
      $word_add_question = elgg_echo("test:add_question");
      $link_add_question = "<a href=\"{$url_add_question}\">{$word_add_question}</a>";

      //Import question
      $url_import_questionsbank = elgg_add_action_tokens_to_url(elgg_get_site_url() . "test/import_questionsbank/" . $testpost);
      $word_import_questionsbank = elgg_echo("test:import_questionsbank");
      $link_import_questionsbank = "<a href=\"{$url_import_questionsbank}\">{$word_import_questionsbank}</a>";

      //Export questions
      $url_export_all_questions = elgg_add_action_tokens_to_url(elgg_get_site_url() . "test/export_questionsbank/" . $testpost . "/all");
      $word_export_all_questions = elgg_echo('test:export_all_questions');
      $link_export_all_questions .= "<a href=\"{$url_export_all_questions}\">{$word_export_all_questions}</a>";

      $count_responses = $test->countAnnotations('all_responses');
      $count_responses_draft = $test->countAnnotations('all_responses_draft');
      $count_responses = $count_responses + $count_responses_draft;

      $test_questions_label = elgg_echo('test:questions');

      if ((!$opened)||(!$test->created)) {
         if ($num_questions>0){
            $body .= "<div class=\"contentWrapper\">";
            $body .= "<p align=\"left\"><a onclick=\"view_questions();\" style=\"cursor:hand;\">$test_questions_label</a></p>";
            $body .= "<div id=\"viewquestionsDiv\">";
            $body .= elgg_view('test/test_questions_table', array('entity' => $test));
            $body .= "<br>";
            if (count_responses>0) {
               $body .= "|";
            } else {
               $body .= "|".$link_add_question."|".$link_import_questionsbank."|";
            }
            $body .= $link_export_all_questions."|";
            $body .= "<br>";
            $body .= "</div><br>";
            $body .= "</div>";
         } else {
            $body .= "<b>".$test_questions_label."</b><br>";
            $body .= "|".$link_add_question."|".$link_import_questionsbank."|";
	    $body .= "<br><br>";
         }
      }

      $body .= "</div><br>";

      //View test
      if ($num_questions>0) {
         $body .= "<div class=\"test_question_frame\">";
         $view_test_label = elgg_echo('test:view_test');
         $body .= "<div class=\"contentWrapper\">";
         $body .= "<p align=\"left\"><a onclick=\"view_test();\" style=\"cursor:hand;\">$view_test_label</a></p>";
         $body .= "<div id=\"viewtestDiv\">";
         $body .= "<div class=\"test_question_frame\">";
         $body .= elgg_view('forms/test/show_question', array('entity' => $test, 'this_index' => $vars['index']));
         $body .= "</div>";
         $body .= "</div>";
         $body .= "</div>";
	 $body .= "</div><br>";
      }


      $body .= elgg_view('test/show_answers', array('entity' => $test, 'offset' => $vars['offset'], 'membersarray' => $membersarray, 'num_responses' => $num_responses));

   } else {

      if ((empty($vars['first_user_guid']))&&(empty($vars['this_user_guid_answer']))&&(empty($vars['this_user_guid_show_answer']))){

         $user_guid_not_subgroup = $user_guid;

         $container_guid  = $test->container_guid;
         $container = get_entity($container_guid);

         if ($test->subgroups){
            $user_subgroup = elgg_get_entities_from_relationship(array('type_subtype_pairs' => array('group' => 'lbr_subgroup'),'container_guids' => $container_guid,'relationship' => 'member','inverse_relationship' => false,'relationship_guid' => $user_guid));
            $user_guid=$user_subgroup[0]->getGUID();
	    $user_subgroup=$user_subgroup[0];
         }

         if (!$test->subgroups){
            $options = array('relationship' => 'test_answer', 'relationship_guid' => $testpost,'inverse_relationship' => false, 'type' => 'object', 'subtype' => 'test_answer', 'limit' => 0, 'owner_guid' => $user_guid);
         } else {
	    $options = array('relationship' => 'test_answer', 'relationship_guid' => $testpost,'inverse_relationship' => false, 'type' => 'object', 'subtype' => 'test_answer', 'container_guid' => $user_guid, 'limit' => 0);
         }
         $user_responses=elgg_get_entities_from_relationship($options);
         if (!empty($user_responses)){
            $user_response=$user_responses[0];
            $attempts=$user_response->attempts;
            $grading=number_format($user_response->grading);
         } else {
            $user_response="";
            $attempts=0;
            $grading=0;
         }
         if (strcmp($test->type_grading,'test_type_grading_marks')==0){
            $max_grading=$test->max_mark;
         } else {
            $max_grading=$test->question_max_game_points;
         }

         if ((((strcmp($test->type_max_attempts,'test_limited_attempts')==0)&&($attempts<$test->max_attempts))||((strcmp($test->type_max_attempts,'test_max_grading_limited_attempts')==0)&&($grading<$max_grading))||(strcmp($test->type_max_attempts,'test_unlimited_attempts')==0))&&($opened)){

            if (!$test->subgroups){
               $options = array('relationship' => 'test_answer_draft', 'relationship_guid' => $testpost,'inverse_relationship' => false, 'type' => 'object', 'subtype' => 'test_answer_draft', 'limit' => 0, 'owner_guid' => $user_guid);
            } else {
	       $options = array('relationship' => 'test_answer_draft', 'relationship_guid' => $testpost,'inverse_relationship' => false, 'type' => 'object', 'subtype' => 'test_answer_draft', 'container_guid' => $user_guid, 'limit' => 0);
            }
            $user_responses_draft=elgg_get_entities_from_relationship($options);
            if (!empty($user_responses_draft)){
               $user_response_draft=$user_responses_draft[0];
            } else {
               $user_response_draft="";
            }

            if ((empty($user_response_draft))&&(!$operator)){

               $options = array('relationship' => 'test_question', 'relationship_guid' => $testpost,'inverse_relationship' => false, 'type' => 'object', 'subtype' => 'test_question','limit'=>0);
               $questions=elgg_get_entities_from_relationship($options);
               if (empty($questions)){
                  $num_questions = 0;
               } else {
                  $num_questions = count($questions);
	       }

               if (empty($user_response)){
                  $test->annotate('all_responses_draft', "1", $test->access_id);
               }
	       // Initialise a new ElggObject to be the answer draft
               $answer_draft = new ElggObject();
               $answer_draft->subtype = "test_answer_draft";
               $answer_draft->owner_guid = $user_guid_not_subgroup;
               if (!empty($user_response)){
	          $answer_draft->container_guid = $user_response->container_guid;
                  $answer_draft->access_id = $user_response->access_id;
               } else {
                  if ($test->subgroups) {
		     // user es el subgrupo
		     $answer_draft->container_guid = $user_guid;
                     $answer_draft->access_id = $user_subgroup->teachers_acl;
                  } else {
		     $answer_draft->container_guid = $container_guid;
                     $answer_draft->access_id = $container->teachers_acl;
	          }
               }
	       $answer_draft->save();
               $answer_draft->first_time = true;
               if (empty($user_response)){
                  $answer_draft->comments = "not_comments";
		  if ($test->random_questions) {
		     $max_question = $num_questions-1;
		     if ($max_question > 0) {
		        $numbers = range(0,$max_question);
		        shuffle($numbers);
		        $answer_draft->selected_random_questions = implode(";",$numbers);
                     } else {
		        $answer_draft->selected_random_questions = "0";
		     }
		     if ($test->num_random_questions<$num_questions){
		        $num_questions = $test->num_random_questions;
		        $answer_draft->num_questions = $num_questions;
		     } else {
		        $answer_draft->num_questions = $num_questions;
		     }
		  } else {
                     $answer_draft->num_questions = $num_questions;
		  }
                  $i=0;
                  $response_fields = "";
                  while ($i<$num_questions){
                     if ($i!=0) {
                        $response_fields .= Chr(27) . "not_response";
                     } else {
                        $response_fields .= "not_response";
		     }
                     $response_fields .= Chr(27) . '0';
		     $i=$i+1;
                  }
                  $answer_draft->content = $response_fields;
               } else {
                   $answer_draft->comments = $user_response->comments;
                   $answer_draft->num_questions = $user_response->num_questions;
		   $answer_draft->selected_random_questions = $user_response->selected_random_questions;
                   $answer_draft->content = $user_response->content;
                }
                add_entity_relationship($test->getGUID(),'test_answer_draft',$answer_draft->getGUID());
             }
	     $body .= elgg_view('forms/test/answer', array('entity' => $test, 'user_guid' => $user_guid_not_subgroup, 'index' => $vars['index']));
          } else {
             $body .= elgg_view('forms/test/show_answer', array('entity' => $test, 'user_guid' => $user_guid, 'index' => $vars['index']));
          }
    } else {
          if (!empty($vars['this_user_guid_answer'])){
             $user_guid = $vars['this_user_guid_answer'];
             $body .= elgg_view('forms/test/answer', array('entity' => $test, 'user_guid' => $user_guid, 'index' => $vars['index']));
          } else {
             if (!empty($vars['first_user_guid'])) {
                $user_guid = $vars['first_user_guid'];
             } else {
                $user_guid = $vars['this_user_guid_show_answer'];
             }
	     $body .= elgg_view('forms/test/show_answer', array('entity' => $test, 'user_guid' => $user_guid, 'index' => $vars['index']));
          }
       }
   }
   echo elgg_view('object/elements/full', array('summary' => $summary,'icon' => $owner_icon,'body' => $body));

} else {
   if (!test_check_status($test)) {
      $title="<div class=\"test_title\"><a class=\"closed_title_test\" href=\"{$test->getURL()}\">{$test->title}</a></div>";
   } else {
      $title="<div class=\"test_title\"><a class=\"opened_title_test\" href=\"{$test->getURL()}\">{$test->title}</a></div>";
   }
   $params = array('entity' => $test,'title' => $title,'metadata' => $metadata,'subtitle' => $subtitle,'tags' => $tags);
   $params = $params + $vars;
   $list_body = elgg_view('object/elements/summary', $params);

   $body = "";

   ///////////////////////////////////////////////////////////////
   //Test information
   $body .= $open_interval;

   if ($test->assessable){
      $body .= "<br>" . elgg_echo('test:assessable') . "<br>";
   } else {
      $body .= "<br>" . elgg_echo('test:notassessable') . "<br>";
   }

   if ($operator){
      $body .= $num_responses . " " . $label_num_responses . ", ";
   }

   $body .= $num_comments . " " . $label_num_comments;

   if (!$operator){
      if (strcmp($test->type_grading,'test_type_grading_marks')==0){
         $grading = number_format($grading,2);
         $label_last_grading = elgg_echo('test:last_mark');
         $label_grading = elgg_echo('test:mark');
      } else {
         $label_last_grading = elgg_echo('test:last_game_points');
         $label_grading = elgg_echo('test:game_points');
      }
      if (strcmp($user_type,"repeatable")==0){
         if ($attempts!=0){
            $body .= "<br>" . elgg_echo('test:repeatable') . " (" . $label_last_grading . ": " . $grading . ", " . elgg_echo('test:last_time') . ": " . number_format($time_minutes,2) . "'" . ", " . elgg_echo('test:total_time') . ": " . number_format($total_time_minutes,2) . "')";
         }
      } else {
         $body .= "<br>" . elgg_echo('test:finished') . " (" . $label_grading . ": " . $grading . ", " . elgg_echo('test:last_time') . ": " . number_format($time_minutes,2) . "'" . ", " . elgg_echo('test:total_time') . ": " . number_format($total_time_minutes,2) . "')";
      }
   }

   //Links to actions
   if (($test->canEdit())&&($operator)) {
      if ($created){
         $body .= "<br>" . $link_open_close;
      } else {
         $body .= "<br>" . $link_publish;
      }
   }

   $list_body .= $body;

   echo elgg_view_image_block($owner_icon, $list_body);
}

?>

<script type="text/javascript">
   function view_test(){
      var viewtestDiv = document.getElementById('viewtestDiv');
      if (viewtestDiv.style.display == 'none'){
         viewtestDiv.style.display = 'block';
      } else {
         viewtestDiv.style.display = 'none';
      }
   }

    function view_questions(){
      var viewquestionsDiv = document.getElementById('viewquestionsDiv');
      if (viewquestionsDiv.style.display == 'none'){
         viewquestionsDiv.style.display = 'block';
      } else {
         viewquestionsDiv.style.display = 'none';
      }
   }

</script>
