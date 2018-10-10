<?php

define('APTO', 5);

function test_grading_output($test,$grading) {
   if (((strcmp($test->type_grading,'test_type_grading_marks')==0)&&(strcmp($test->type_mark,'test_type_mark_numerical')==0))||(strcmp($test->type_grading,'test_type_grading_marks')!=0)){
      $grading_output = $grading;
   } elseif ((strcmp($test->type_grading,'test_type_grading_marks')==0)&&(strcmp($test->type_mark,'test_type_mark_textual')==0)) {
      if ($grading >= HONOURS){
         $grading_output = elgg_echo('mark:honours');
      } elseif ($grading >= OUTSTANDING){
         $grading_output = elgg_echo('mark:outstanding');
      } elseif ($grading >= VERYGOOD){
         $grading_output = elgg_echo('mark:verygood');
      } elseif ($grading >= SUFFICIENT){
         $grading_output = elgg_echo('mark:sufficient');
      } else {
         $grading_output = elgg_echo('mark:insufficient');
      }
   } elseif ((strcmp($test->type_grading,'test_type_grading_marks')==0)&&(strcmp($test->type_mark,'test_type_mark_apto')==0)){
      if ($grading>=APTO)
         $grading_output = elgg_echo('mark:pass');
      else
         $grading_output = elgg_echo('mark:fail');
   }
   return $grading_output;
}

function test_my_sort($original, $field, $descending = false)
{
    if (!$original) {
        return $original;
    }
    $sortArr = array();
    foreach ($original as $key => $item) {
        $sortArr[$key] = $item->$field;
    }
    if ($descending) {
        arsort($sortArr);
    } else {
        asort($sortArr);
    }
    $resultArr = array();
    foreach ($sortArr as $key => $value) {
        $resultArr[$key] = $original[$key];
    }
    return $resultArr;
}

?>