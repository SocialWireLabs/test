<?php

gatekeeper();

$testpost = get_input('testpost');
$test = get_entity($testpost);

$this_user_guid = elgg_get_logged_in_user_guid();

if ($test->getSubtype() == "test") {
    $user_guid = get_input('user_guid');
    $user = get_entity($user_guid);
    $offset = get_input('offset');

    $opened = test_check_status($test);

    $owner = $test->getOwnerEntity();
    $group_guid = $test->container_guid;
    $group = get_entity($group_guid);
    $group_owner_guid = $group->owner_guid;

    $operator = false;
    if (($owner_guid == $this_user_guid) || ($group_owner_guid == $this_user_guid) || (check_entity_relationship($this_user_guid, 'group_admin', $group_guid))) {
        $operator = true;
    }

    if (!$opened) {     
       if ($operator) {
          //Delete answer
          if (!$test->subgroups) {
             $options = array('relationship' => 'test_answer', 'relationship_guid' => $testpost, 'inverse_relationship' => false, 'type' => 'object', 'subtype' => 'test_answer', 'order_by' => 'e.time_created desc', 'limit' => 0, 'owner_guid' => $user_guid);
          } else {
             $options = array('relationship' => 'test_answer', 'relationship_guid' => $testpost, 'inverse_relationship' => false, 'type' => 'object', 'subtype' => 'test_answer', 'order_by' => 'e.time_created desc', 'limit' => 0, 'container_guid' => $user_guid);
          }
          $user_responses = elgg_get_entities_from_relationship($options);
          if (!empty($user_responses)) {
	     $user_response = $user_responses[0];
	     if (strcmp($test->type_grading,'test_type_grading_game_points')){
                $access = elgg_set_ignore_access(true);
                $game_points = gamepoints_get_entity($user_response->getGUID());
                if ($game_points) {
                   $deleted=$game_points->delete();
                   if (!$deleted){
                      register_error(elgg_echo("test:gamepointsnotdeleted"));
		      if (empty($offset))
	                 forward("test/view/$testpost/");
	              else
	                 forward("test/view/$testpost/?offset=$offset");
                   }
                }  
	        elgg_set_ignore_access($access);   
             } else {
	        $access = elgg_set_ignore_access(true);
                $marks = socialwire_marks_get_marks(null,$user_guid,$testpost);
                foreach ($marks as $mark) {
                   $deleted=$mark->delete();
                   if (!$deleted){
                      register_error(elgg_echo("test:marknotdeleted"));
		      if (empty($offset))
	                 forward("test/view/$testpost/");
	              else
	                 forward("test/view/$testpost/?offset=$offset");          
                   }
                }   
                elgg_set_ignore_access($access); 
	     }
	     $deleted = $user_response->delete();
	     if (!$deleted) {
	        register_error(elgg_echo("test:answernotdeleted"));
	        if (empty($offset))
	           forward("test/view/$testpost/");
	        else
	           forward("test/view/$testpost/?offset=$offset");
	     }
	     //System message
	     system_message(elgg_echo("test:answerdeleted"));
          }  
          //Delete answer draft
	  if (!$test->subgroups) {
             $options = array('relationship' => 'test_answer_draft', 'relationship_guid' => $testpost, 'inverse_relationship' => false, 'type' => 'object', 'subtype' => 'test_answer_draft', 'order_by' => 'e.time_created desc', 'limit' => 0, 'owner_guid' => $user_guid);
          } else {
             $options = array('relationship' => 'test_answer_draft', 'relationship_guid' => $testpost, 'inverse_relationship' => false, 'type' => 'object', 'subtype' => 'test_answer_draft', 'order_by' => 'e.time_created desc', 'limit' => 0, 'container_guid' => $user_guid);
          }
	  $user_responses_draft=elgg_get_entities_from_relationship($options);

	  if (!empty($user_responses_draft)) {
             $deleted=$user_responses_draft[0]->delete();
             if (!$deleted){
                register_error(elgg_echo("test:answernotdeleted"));
		if (empty($offset))
	           forward("test/view/$testpost/");
	        else
	           forward("test/view/$testpost/?offset=$offset");
             }	     
	  }
       }		
    } else {
        register_error(elgg_echo("test:opened")); 
    }
    //Forward
    if (empty($offset))
        forward("test/view/$testpost/");
    else
        forward("test/view/$testpost/?offset=$offset");
}

?>
