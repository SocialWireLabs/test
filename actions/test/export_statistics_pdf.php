<?php 

elgg_load_library('test');

$testpost = get_input('testpost');
$test = get_entity($testpost);	

if($test){

	$format = get_input('format', 'A4');
	$font = get_input('font', 'times');

	$html .= "<h2><p>".htmlentities($test->title,false,'UTF-8',true)."</p></h2>";
	$html .= "<hr><hr><hr>";
  
  $group = get_entity($test->container_guid);
  $group_guid=$group->guid;

  $owner = $test->getOwnerEntity();
  $owner_guid = $owner->getGUID();
  $group_owner_guid = $group->owner_guid; 

  $type_max_attempts_label = elgg_echo("test:type_max_attempts_label");
  $grading_label = elgg_echo("test:grading_label");
  $total_time_label = elgg_echo("test:total_time");
  $time_label = elgg_echo("test:time");
  $grading_statistics_label = elgg_echo("test:grading_statistics");
  $max_grading_label = elgg_echo("test:max_grading");
  $min_grading_label = elgg_echo("test:min_grading");
  $time_statistics_label = elgg_echo("test:time_statistics");
  $max_time_label = elgg_echo("test:max_time");
  $min_time_label = elgg_echo("test:min_time");
  $questions_statistics_label = elgg_echo("test:questions_statistics");

  if (!$test->subgroups){
    $members = $group->getMembers(array('limit'=>false));
    $html .= "<h3>". elgg_echo("test:students")."  ";
    //$html .= "<hr>";
  } else {
    $members=elgg_get_entities(array('type_subtype_pairs' => array('group' => 'lbr_subgroup'),'limit' => 0,'container_guids' => $group_guid));
    $html .= "<h3>". elgg_echo("test:groups")."  ";
    //$html .= "<hr>";
  }

  $i = 0;
  $membersarray = array();
  foreach ($members as $member){
     $member_guid = $member->getGUID();
     if (($member_guid != $owner_guid) && ($group_owner_guid != $member_guid) && (!check_entity_relationship($member_guid,'group_admin',$group_guid))){
        $membersarray[$i] = $member;
        $i = $i + 1;
     }
  }

  if (strcmp($test->type_grading,'test_type_grading_marks')==0){
    $html .= elgg_echo("test:type_grading_marks")."</h3><hr><br>";
  }else{
    $html .= elgg_echo("test:type_grading_game_points")."</h3><hr><br>";
  }

  $options = array('relationship' => 'test_question', 'relationship_guid' => $testpost, 'inverse_relationship' => false, 'type' => 'object', 'subtype' => 'test_question', 'limit' =>0);
  $questions = elgg_get_entities_from_relationship($options);
 
  $questionsarray[] = array();
  $membersarray = test_my_sort($membersarray, "name", false);
  $i = 0;
  $marksarray = array();
  $timesarray = array();

  foreach ($membersarray as $member) {
    $member_guid = $member->getGUID();
    if (!$test->subgroups){
      $options = array('relationship' => 'test_answer', 'relationship_guid' => $testpost,'inverse_relationship' => false, 'type' => 'object', 'subtype' => 'test_answer', 'order_by' => 'e.time_created desc', 'limit' => 0, 'owner_guid' => $member_guid);
    } else {
      $options = array('relationship' => 'test_answer', 'relationship_guid' => $testpost,'inverse_relationship' => false, 'type' => 'object', 'subtype' => 'test_answer', 'order_by' => 'e.time_created desc', 'limit' => 0, 'container_guid' => $member_guid);
    }
    $user_responses = elgg_get_entities_from_relationship($options);
    $user_response = $user_responses[0];

    if (!empty($user_response)) {
      $mark = $user_response->grading;
      $marksarray[$i] = $mark;
      $all_times_array = array_map('trim',explode(";",$user_response->all_times));
      $timesarray[$i] = array_sum($all_times_array);
      $i = $i + 1;

      $user_response_content_array = array_map('trim',explode(Chr(27),$user_response->content));
      $num_questions = $user_response->num_questions;

      if($test->random_questions){
        $question_order = array_map('trim',explode(";",$user_response->selected_random_questions));
        $l = 0;
        foreach ($question_order as $j){
          $questionsarray[$j][]= $user_response_content_array[($l*2)+1];
          if ($l == ($num_questions-1)) break;
          $l = $l + 1;
        }
      }else{ 
        for ($j = 0; $j <= ($num_questions-1); $j++) {
          $questionsarray[$j][] = $user_response_content_array[($j*2)+1];
        }
      }
    } else $mark = elgg_echo("test:noanswer"); 

    $html .= "<p>". htmlentities($member->name,false,'UTF-8',true).": ".$mark ;

    if (!empty($user_response)) {
       $intentos = $user_response->attempts;
       $total_time = array_sum(array_map('trim',explode(";",$user_response->all_times)));
       $total_time_minutes = $total_time/60;
       $total_time_minutes = number_format($total_time_minutes,2) . "'";
       $html .= "<br>".htmlentities($type_max_attempts_label,false,'UTF-8',true).": ". $intentos." ". htmlentities($grading_label,false,'UTF-8',true).": ".$user_response->all_gradings." ".htmlentities($total_time_label,false,'UTF-8',true).": ".$total_time_minutes. "'</p><br>";
    }else{
      $html .= "</p><br>" ;
    }
  
    if ($test->subgroups) {
      $subgroup_guid = $member_guid;
      $subgroup_members = elgg_get_entities_from_relationship(array('relationship' => 'member', 'inverse_relationship' => true, 'type' => 'user', 'relationship_guid' => $subgroup_guid, 'limit' => 0));
      $html .="<p>". elgg_echo("test:members_of_subgroup").":<br>";
      foreach ($subgroup_members as $stu) {
        $html .= htmlentities($stu->name,false,'UTF-8',true) . " ";
      }
      $html .= "</p><br>";
    }
    $html .= "<hr>";
  }

  $nmarks = count($marksarray);
  $mean_global = array_sum($marksarray)/$nmarks;

  $max_global = max($marksarray);
  $min_global = min($marksarray);

  $freqarray = array_count_values($marksarray);
  arsort($freqarray);
  $moda_global = key($freqarray);
  $count_moda_global = $freqarray[$moda_global];
  $moda_global_array = $moda_global;
  foreach($marksarray as $one_mark){
     if($one_mark!=$moda_global){
        if ($freqarray[$one_mark]==$count_moda_global) {
	   $moda_global_array .= ";". $one_mark;
	}
     }
  }

  sort($marksarray);
  $median_global = 0;
  if ($nmarks%2==0){
    $half = $nmarks/2;
    $median_global = ($marksarray[$half-1] +  $marksarray[$half])/2;
  }else{
    $half = ($nmarks+1)/2;
    $median_global = $marksarray[$half-1];
  }

  $html .= "<hr><hr><hr><h3>". htmlentities($grading_statistics_label,false,'UTF-8',true) ."</h3><hr><br>";
  $html .= elgg_echo("test:mean").": ". number_format($mean_global,2) ."<br>";
  $html .= htmlentities($max_grading_label,false,'UTF-8',true).": ". $max_global ."<br>";
  $html .= htmlentities($min_grading_label,false,'UTF-8',true).": ". $min_global ."<br>";
  $html .= elgg_echo("test:mode").": ". $moda_global_array ."<br>";
  $html .= elgg_echo("test:median").": ". $median_global ."<br><br>";

  $mean_times = array_sum($timesarray)/count($timesarray);
  $max_times = max($timesarray);
  $min_times = min($timesarray);

  $mean_times_minutes = $mean_times/60;
  $mean_times_minutes = number_format($mean_times_minutes,2) . "'";
  $html .= "<hr><hr><hr><h3>". htmlentities($time_statistics_label,false,'UTF-8',true) ."</h3><hr><br>";
  $html .= elgg_echo("test:mean").": ". $mean_times_minutes ."<br>";
  $max_times_minutes = $max_times/60;
  $max_times_minutes = number_format($max_times_minutes,2) . "'";
  $min_times_minutes = $min_times/60;
  $min_times_minutes = number_format($min_times_minutes,2) . "'";
  $html .= htmlentities($max_time_label,false,'UTF-8',true).": ". $max_times_minutes ."<br>";
  $html .= htmlentities($min_time_label,false,'UTF-8',true).": ". $min_times_minutes ."<br><br>";

  $html .= "<hr><hr><hr><h3>". htmlentities($questions_statistics_label,false,'UTF-8',true) ."</h3><hr><br>";
  $i = 0;
 
  ksort($questionsarray);
  foreach($questionsarray as $auxarray){
    $auxarray = array_filter($auxarray, "strlen");
    $max_question = max($auxarray);
    $min_question = min($auxarray);
    $mean_question = array_sum($auxarray)/count($auxarray);
    
    $html .= "<br>".elgg_echo("test:showquestionpost"). " ".($i+1).": "."<br>";
    $html .= elgg_echo("test:mean").": ". number_format($mean_question,2) ."<br>";
    $html .= htmlentities($max_grading_label,false,'UTF-8',true).": ". $max_question ."<br>";
    $html .= htmlentities($min_grading_label,false,'UTF-8',true).": ". $min_question ."<br>";
    $i = $i + 1; 
  }


	$pdf = new HTML2FPDF('P', 'mm', $format);
	$pdf->AddPage();
	$pdf->WriteHTML($html);
	$pdf->Output("test_statistics.pdf", 'D');
	exit;
} else {
   register_error(elgg_echo("test:notfound"));
   forward($_SERVER['HTTP_REFERER']);
}