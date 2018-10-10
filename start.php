<?php

/**
* Override the ElggFile 
*/

class QuestionsTestPluginFile extends ElggFile
{
	protected function initialiseAttributes()
	{
		parent::initialise_attributes();
		$this->attributes['subtype'] = "test_question_file";
                $this->attributes['class'] = "ElggFile";
	}

	public function __construct($guid = null)
	{
           if ($guid && !is_object($guid)) {
              $guid = get_entity_as_row($guid);
           }
	   parent::__construct($guid);
	}
}

function test_init() {
		
// Extend system CSS with our own styles, which are defined in the test/css view
   elgg_extend_view('css/elgg','test/css');

// Register a page handler, so we can have nice URLs
   elgg_register_page_handler('test','test_page_handler');
                
// Register entity type
   elgg_register_entity_type('object','test');

// Register a URL handler for test posts
   elgg_register_plugin_hook_handler('entity:url', 'object', 'test_url');

// Register a URL handler for test_answer posts
   elgg_register_plugin_hook_handler('entity:url', 'object', 'test_answer_url');
                                                                                
// Advanced permissions
   elgg_register_plugin_hook_handler('permissions_check', 'object', 'test_permissions_check');

// Show tests in groups
   add_group_tool_option('test', elgg_echo('test:enable_group_tests'));
   elgg_extend_view('groups/tool_latest', 'test/group_module');

   elgg_register_plugin_hook_handler('register', 'menu:owner_block', 'test_owner_block_menu');
   
 // Register library
   elgg_register_library('test', elgg_get_plugins_path() . 'test/lib/test_lib.php');

   run_function_once("test_question_file_add_subtype_run_once");  

}

function test_question_file_add_subtype_run_once(){
   add_subtype("object","test_question_file","QuestionsTestPluginFile");
}

function test_permissions_check($hook, $type, $return, $params) {
   $user_guid = elgg_get_logged_in_user_guid();
   $group_guid = $params['entity']->container_guid;
   $group = get_entity($group_guid);
   $group_owner_guid = $group->owner_guid;
   $operator=false;
   if (($group_owner_guid==$user_guid)||(check_entity_relationship($user_guid,'group_admin',$group_guid))){
      $operator=true;
   }
   if ((($params['entity']->getSubtype() == 'test')||($params['entity']->getSubtype() == 'test_question')||($params['entity']->getSubtype() == 'test_question_file')||($params['entity']->getSubtype() == 'test_answer'))&&($operator)) {
      return true;
   }    
}

/**
 * Add a menu item to the user ownerblock
*/
function test_owner_block_menu($hook, $type, $return, $params) {
   if (elgg_instanceof($params['entity'], 'group')) {
      if ($params['entity']->test_enable != "no") {
         $url = "test/group/{$params['entity']->guid}/all";
         $item = new ElggMenuItem('test', elgg_echo('test:group'), $url);
         $return[] = $item;
      }
   }
   return $return;
}

/**
* Test page handler; allows the use of fancy URLs
*
* @param array $page from the page_handler function
* @return true|false depending on success
*/
function test_page_handler($page) {
   if (isset($page[0])) {
         elgg_push_breadcrumb(elgg_echo('tests'));
         $base_dir = elgg_get_plugins_path() . 'test/pages/test';
         switch($page[0]) {
            case "add":   
               set_input('container_guid',$page[1]);
               include "$base_dir/add.php"; 
               break;
            case "edit":  
               set_input('testpost',$page[1]);
               include "$base_dir/edit.php"; 
               break;
	    case "add_question":
               set_input('testpost', $page[1]);
               include "$base_dir/add_question.php";
               break;
            case "edit_question":
               set_input('testpost', $page[1]);
               set_input('index',$page[2]);
               include "$base_dir/edit_question.php";
               break;
	    case "export_questionsbank":
               set_input('testpost', $page[1]);
	       set_input('index',$page[2]);
               include "$base_dir/export_questionsbank.php";
               break;
	    case "import_questionsbank":
               set_input('testpost', $page[1]);
               include "$base_dir/import_questionsbank.php";
               break;
	    case "select_questions_questionsbank":
               set_input('testpost', $page[1]);
	       set_input('questionsbankpost',$page[2]);
               include "$base_dir/select_questions_questionsbank.php";
               break;
	    case "show_question_questionsbank":
               set_input('testpost', $page[1]);
	       set_input('questionsbankpost',$page[2]);
	       set_input('index',$page[3]);
               include "$base_dir/show_question_questionsbank.php";
               break;
            case "view":  
               set_input('guid', $page[1]);
               $test = get_entity($page[1]);
               $container = get_entity($test->container_guid);
               set_input('username', $container->username);
               include "$base_dir/read.php"; 
               break;
            case 'group':
               set_input('container_guid',$page[1]);
               include "$base_dir/index.php";
            default:
               return false;
         }
   } else {
      forward();
   }
   return true;
}

/**
 * Returns the URL from a test entity
 *
 * @param string $hook   'entity:url'
 * @param string $type   'object'
 * @param string $url    The current URL
 * @param array  $params Hook parameters
 * @return string
 */
function test_url($hook, $type, $url, $params) {
   $test = $params['entity'];
   if ($test->getSubtype() !== 'test') {
        return;
   }
   $title = elgg_get_friendly_title($test->title);
   return $url . "test/view/" . $test->getGUID() . "/" . $title;
}

/**
 * Returns the URL from a test_answer entity
 *
 * @param string $hook   'entity:url'
 * @param string $type   'object'
 * @param string $url    The current URL
 * @param array  $params Hook parameters
 * @return string
 */
function test_answer_url($hook, $type, $url, $params) {
   $test_answer = $params['entity'];
   if ($test_answer->getSubtype() !== 'test_answer') {
        return;
   }
   $options = array('relationship' => 'test_answer', 'relationship_guid' => $test_answer->getGUID(),'inverse_relationship' => true, 'type' => 'object', 'subtype' => 'test');
   $tests = elgg_get_entities_from_relationship($options);
   if (!empty($test)){
      $test=$test[0];
      $title = elgg_get_friendly_title($test->title);
      return $url . "test/view/" . $test->getGUID() . "/" . $title;
   } else
      return false;
}

// Test opened or closed?
function test_check_status($test) {
   if (strcmp($test->option_close_value,'test_close_date')==0){
      $now=time();
      if (($now>=$test->activate_time)&&($now<$test->close_time)){
         return true;
      } else {
         if ($test->action == true){
            $test->option_close_value ='';
            $test->action = false;
            $test->opened = true;
            return true;
         }
         return false;
      }
   } else {
      $test->action = false;
      return $test->opened;
   } 
}

// Make sure the test initialisation function is called on initialisation
elgg_register_event_handler('init','system','test_init');
		
// Register actions
$action_base = elgg_get_plugins_path() . 'test/actions/test';
elgg_register_action("test/add","$action_base/add.php");
elgg_register_action("test/edit","$action_base/edit.php");
elgg_register_action("test/delete","$action_base/delete.php");
elgg_register_action("test/add_question","$action_base/add_question.php");
elgg_register_action("test/edit_question","$action_base/edit_question.php");
elgg_register_action("test/delete_question","$action_base/delete_question.php");
elgg_register_action("test/move_question","$action_base/move_question.php");
elgg_register_action("test/export_questionsbank","$action_base/export_questionsbank.php");
elgg_register_action("test/import_questionsbank","$action_base/import_questionsbank.php");
elgg_register_action("test/select_questions_questionsbank","$action_base/select_questions_questionsbank.php");
elgg_register_action("test/open","$action_base/open.php");
elgg_register_action("test/close","$action_base/close.php");
elgg_register_action("test/publish","$action_base/publish.php");
elgg_register_action("test/answer","$action_base/answer.php");
elgg_register_action("test/show_answer","$action_base/show_answer.php");
elgg_register_action("test/show_question","$action_base/show_question.php");
elgg_register_action("test/assign_marks","$action_base/assign_marks.php");
elgg_register_action("test/assign_game_points","$action_base/assign_game_points.php");
elgg_register_action("test/delete_answer","$action_base/delete_answer.php");
elgg_register_action("test/add_attempt","$action_base/add_attempt.php");
elgg_register_action("test/export_statistics_pdf","$action_base/export_statistics_pdf.php");
?>