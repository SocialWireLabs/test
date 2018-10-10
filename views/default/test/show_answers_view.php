<?php

elgg_load_library('test');

$test=$vars['entity'];
$user=$vars['user'];
$attempts=$vars['attempts'];
$time=$vars['time'];
$total_time=$vars['total_time'];
$grading=$vars['grading'];

$attempts_label=elgg_echo('test:attempts');
if (strcmp($test->type_grading,'test_type_grading_marks')==0)
   $grading_label=elgg_echo('test:mark');
else
   $grading_label=elgg_echo('test:game_points');

$info = "<div class=\"test_options\">";
if (strcmp($grading,"not_qualified")!=0){
   if (strcmp($test->type_grading,'test_type_grading_marks')==0)
      $grading=number_format($grading,2);
   $grading_output=test_grading_output($test,$grading);
   $info .= $grading_label . ": " . $grading_output;
} else {
   $info .= elgg_echo('test:not_qualified');
}   
$info .= "</div>";

$icon = elgg_view_entity_icon($user,'small');

$time_label = elgg_echo('test:last_time');
$total_time_label = elgg_echo('test:total_time');

$info .= $vars['link'] . "<br>";
$info .= $attempts_label . ": " . $attempts . "; " . $time_label . ": " . $time . "; " . $total_time_label . ": " . $total_time;

echo elgg_view_image_block($icon,$info);

?>