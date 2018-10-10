<?php

gatekeeper();
if (is_callable('group_gatekeeper')) 
   group_gatekeeper();

$testpost = get_input('guid');
$test = get_entity($testpost);
$index = get_input('index');
if (!isset($index))
   $index="none";
$offset = get_input('offset');
if (empty($offset))
   $offset = 0;

//Next or previous answer and show_results
$this_user_guid_answer = get_input('this_user_guid_answer');
$this_user_guid_show_answer = get_input('this_user_guid_show_answer');
//Show answers by operator
$first_user_guid = get_input('first_user_guid');

if ($test) {
   $container_guid = $test->container_guid;
   $container = get_entity($container_guid);
   elgg_set_page_owner_guid($container_guid);

   $owner = $test->getOwnerEntity();
   $owner_guid = $owner->getGUID();
   $user_guid = elgg_get_logged_in_user_guid();
   $user = get_entity($user_guid);
   $group_guid=$test->container_guid;
   $group = get_entity($group_guid);
   $group_owner_guid = $group->owner_guid;

   $operator=false;
   if (($owner_guid==$user_guid)||($group_owner_guid==$user_guid)||(check_entity_relationship($user_guid,'group_admin',$group_guid))){
      $operator=true;
   }

   elgg_push_breadcrumb($container->name, "test/group/$container_guid");
   if ((!$operator)||((empty($first_user_guid))&&(empty($this_user_guid_show_answer))))
      elgg_push_breadcrumb($test->title);
   else
      elgg_push_breadcrumb($test->title,$test->getURL()."?offset=".$offset);

   if (!$operator){
      //$title = elgg_echo('test:answerpost');
      $title = elgg_echo('test:response');
   } else {
      if ((empty($first_user_guid))&&(empty($this_user_guid_show_answer)))
         $title = elgg_echo('test:showresultspost');     
      else
         $title = elgg_echo('test:response');
   }
  
   $content = elgg_view("object/test",array('full_view' => true, 'entity' => $test ,'entity_owner' => $container,'index'=>$index,'offset'=>$offset,'this_user_guid_answer'=>$this_user_guid_answer,'this_user_guid_show_answer'=>$this_user_guid_show_answer,'first_user_guid'=>$first_user_guid));
   $body = elgg_view_layout('content', array('filter' => '','content' => $content,'title' => $title));
   echo elgg_view_page($title, $body);

} else {
   register_error( elgg_echo('test:notfound'));
   forward();
}

?>