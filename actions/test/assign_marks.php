<?php

gatekeeper();

$testpost = get_input('testpost');
$user_guid = elgg_get_logged_in_user_guid();
	
$test = get_entity($testpost);
$container = get_entity($test->container_guid);

$owner = $test->getOwnerEntity();
$owner_guid = $owner->getGUID();
$group_guid=$container->guid;
$group = get_entity($group_guid);
$group_owner_guid = $group->owner_guid;         

if (!$test->subgroups){
   $members = $group->getMembers(array('limit'=>false));
} else {
   $members=elgg_get_entities(array('type_subtype_pairs' => array('group' => 'lbr_subgroup'),'limit' => 0,'container_guids' => $group_guid));
}

$i=0;
$membersarray=array();
foreach ($members as $member){
   $member_guid=$member->getGUID();
   if (($member_guid!=$owner_guid)&&($group_owner_guid!=$member_guid)&&(!check_entity_relationship($member_guid,'group_admin',$group_guid))){
      $membersarray[$i]=$member_guid;
      $i=$i+1;
   }
}

foreach ($membersarray as $member_guid){
   if (!$test->subgroups){
      $options = array('relationship' => 'test_answer', 'relationship_guid' => $testpost,'inverse_relationship' => false, 'type' => 'object', 'subtype' => 'test_answer', 'order_by' => 'e.time_created desc', 'limit' => 0, 'owner_guid' => $member_guid);
   } else {
      $options = array('relationship' => 'test_answer', 'relationship_guid' => $testpost,'inverse_relationship' => false, 'type' => 'object', 'subtype' => 'test_answer', 'order_by' => 'e.time_created desc', 'limit' => 0, 'container_guid' => $member_guid);
   }
   $user_responses=elgg_get_entities_from_relationship($options);
   $user_response=$user_responses[0];

   if (!empty($user_response)){
      $access = elgg_set_ignore_access(true);
      $marks = socialwire_marks_get_marks(null, $member_guid, $testpost);
      if ($marks){
         socialwire_marks_update_mark($marks[0]->guid, $user_response->grading, $test->mark_type);
      } else {
	 if (strcmp($user_response->grading,"not_qualified")!=0) {
	    if (!$test->subgroups){
               $result = socialwire_marks_create_mark($user_guid,$user_response->owner_guid,$testpost,$user_response->grading);
	    } else {
	       $result = socialwire_marks_create_mark($user_guid,$member_guid,$testpost,$user_response->grading);
	    }
	 }
      }
      elgg_set_ignore_access($access);
   }
}

//System message
system_message(elgg_echo("test:marks_assigned"));

//Forward
forward("test/view/$testpost");   

?>