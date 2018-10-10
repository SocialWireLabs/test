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
        //Delete attempt
        if (!$test->subgroups) {
          $options = array('relationship' => 'test_answer', 'relationship_guid' => $testpost, 'inverse_relationship' => false, 'type' => 'object', 'subtype' => 'test_answer', 'order_by' => 'e.time_created desc', 'limit' => 0, 'owner_guid' => $user_guid);
        } else {
          $options = array('relationship' => 'test_answer', 'relationship_guid' => $testpost, 'inverse_relationship' => false, 'type' => 'object', 'subtype' => 'test_answer', 'order_by' => 'e.time_created desc', 'limit' => 0, 'container_guid' => $user_guid);
        }
        $user_responses = elgg_get_entities_from_relationship($options);
        if (!empty($user_responses)){
          $user_response=$user_responses[0];       
          if(strcmp($test->type_max_attempts,"test_limited_attempts")==0){
             $attempts=$user_response->attempts;
             $attempts=$attempts-1;
             $user_response->attempts=$attempts;
	     if ($attempts==0){
	        $deleted=$user_response->delete();
		if (!$deleted) {
		   register_error('test:answernotdeleted');
		   forward($_SERVER['HTTP_REFERER']);
		}
	     } else {
	        $index_ant = $attempts-1;
	        $all_contents_array = explode(Chr(28),$user_response->all_contents);
		array_pop($all_contents_array);
		$user_response->content = $all_contents_array[$index_ant];
		$user_response->all_contents = implode(Chr(28),$all_contents_array);
		$all_comments_array = explode(Chr(28),$user_response->all_comments);
		array_pop($all_comments_array);
		$user_response->comments = $all_comments_array[$index_ant];
		$user_response->all_comments = implode(Chr(28),$all_comments_array);
	        $all_gradings_array = explode(";",$user_response->all_gradings);
		array_pop($all_gradings_array);
		$user_response->grading = $all_gradings_array[$index_ant];
		$user_response->all_gradings = implode(";",$all_gradings_array);
		$all_times_array = explode(";",$user_response->all_times);
		array_pop($all_times_array);
		$user_response->time = $all_times_array[$index_ant];
		$user_response->all_times = implode(";",$all_times_array); 
	     }
            //System message
            system_message(elgg_echo("test:attempt_added"));
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
