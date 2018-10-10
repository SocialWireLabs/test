<?php

gatekeeper();

$testpost = get_input('testpost');
$test = get_entity($testpost);

if ($test->getSubtype() == "test") {
      $selected_action = get_input('submit');
      $index = get_input('index');
      
      if (strcmp($selected_action,elgg_echo('test:show_question_next'))==0) {
	 $index=$index+1;
      } else {
	 $index=$index-1;
      } 
  
      forward("test/view/$testpost/?index=$index");
}

?>