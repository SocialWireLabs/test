<?php

$test = $vars['entity'];
$testpost = $test->getGUID();

$options = array('relationship' => 'test_question', 'relationship_guid' => $testpost,'inverse_relationship' => false, 'type' => 'object', 'subtype' => 'test_question','limit'=>0,'order_by_metadata' => array('name' => 'index', 'direction' => 'asc', 'as' => 'integer'));
$questions = elgg_get_entities_from_relationship($options);

if (empty($questions))
   $num_questions = 0;
else
   $num_questions = count($questions);
  
$edit_msg = elgg_echo('test:edit_question');
$delete_msg = elgg_echo('test:delete_question');
$export_question_msg = elgg_echo('test:export_questionsbank');
$delete_confirm_msg = elgg_echo('test:delete_question_confirm');
$moveup_msg = elgg_echo('test:up');
$movedown_msg = elgg_echo('test:down');
$movetop_msg = elgg_echo('test:top');
$movebottom_msg = elgg_echo('test:bottom');
	
$img_template = '<img border="0" width="16" height="16" alt="%s" title="%s" src="'.elgg_get_config('wwwroot').'mod/test/graphics/%s" />';
$edit_img = sprintf($img_template,$edit_msg,$edit_msg,"edit_question.jpeg");
$delete_img = sprintf($img_template,$delete_msg,$delete_msg,"delete.gif");
$export_question_img = sprintf($img_template,$expor_question_msg,$export_question_msg,"export_question.jpeg");
	
$question_txt = elgg_echo('test:questions');
$edit_txt = elgg_echo('test:edit_question');
$delete_txt = elgg_echo('test:delete_question');
$export_question_txt = elgg_echo('test:export_questionsbank');
$up_txt = elgg_echo('test:up');
$down_txt = elgg_echo('test:down');
$top_txt = elgg_echo('test:top');
$bottom_txt = elgg_echo('test:bottom');
	
	$body .= <<<EOF
			<SCRIPT>
				function call_mouse_over_function(object){
					$(object).css("background","#E3F1FF");
				}
				function call_mouse_out_function(object){
					$(object).css("background","");
				}
			</SCRIPT>
			<table class="test_questions_list_table">
				
EOF;
	
foreach($questions as $question){
        $index=$question->index;
    	$question_guid = $question->getGUID();
    	$class = $class == "test_questions_list_table_odd" ? "test_questions_list_table_even" : "test_questions_list_table_odd";
        $moveup_img = sprintf($img_template,$moveup_msg,$moveup_msg,"up.png");
	$movedown_img = sprintf($img_template,$movedown_msg,$movedown_msg,"down.png");
	$movetop_img = sprintf($img_template,$movetop_msg,$movetop_msg,"top.png");
	$movebottom_img = sprintf($img_template,$movebottom_msg,$movebottom_msg,"bottom.png");
	$up_script = "";
    	$top_script = "";
    	$down_script = "";
    	$bottom_script = "";
	$url_delete=elgg_add_action_tokens_to_url(elgg_get_site_url() . "action/test/delete_question?testpost=" . $testpost . "&index=" . $index);
	$url_edit = elgg_add_action_tokens_to_url(elgg_get_site_url() . "test/edit_question/" . $testpost . "/" . $index);
	$url_export_question = elgg_add_action_tokens_to_url(elgg_get_site_url() . "test/export_questionsbank/" . $testpost . "/" . $index);
	$url_up = elgg_add_action_tokens_to_url(elgg_get_site_url() . "action/test/move_question?testpost=" . $testpost . "&ac=up" . "&index=" . $index);
	$url_down = elgg_add_action_tokens_to_url(elgg_get_site_url() . "action/test/move_question?testpost=" . $testpost . "&ac=down" . "&index=" . $index);
	$url_top = elgg_add_action_tokens_to_url(elgg_get_site_url() . "action/test/move_question?testpost=" . $testpost . "&ac=top" . "&index=" . $index);
	$url_bottom = elgg_add_action_tokens_to_url(elgg_get_site_url() . "action/test/move_question?testpost=" . $testpost . "&ac=bottom" . "&index=" . $index);

	$text_question = elgg_get_excerpt($question->question,45);
	
    	if($num_questions == 1){
    		$up_script = 'Onclick="javascript:return false;"';
        	$top_script = 'Onclick="javascript:return false;"';
        	$down_script = 'Onclick="javascript:return false;"';
        	$bottom_script = 'Onclick="javascript:return false;"';
        	$moveup_img = sprintf($img_template,$moveup_msg,$moveup_msg,"up_dis.png");
        	$movetop_img = sprintf($img_template,$movetop_msg,$movetop_msg,"top_dis.png");
        	$movedown_img = sprintf($img_template,$movedown_msg,$movedown_msg,"down_dis.png");
        	$movebottom_img = sprintf($img_template,$movebottom_msg,$movebottom_msg,"bottom_dis.png");
        	
	}elseif($num_questions == 2){
        	$top_script = 'Onclick="javascript:return false;"';
        	$bottom_script = 'Onclick="javascript:return false;"';
        	$movetop_img = sprintf($img_template,$movetop_msg,$movetop_msg,"top_dis.png");
        	$movebottom_img = sprintf($img_template,$movebottom_msg,$movebottom_msg,"bottom_dis.png");
		if ($question->index==0){
		   $up_script = 'Onclick="javascript:return false;"';
		   $moveup_img = sprintf($img_template,$moveup_msg,$moveup_msg,"up_dis.png");
		} else {
		   $up_script = "";
		}
		if ($question->index+1 == $num_questions){
		   $down_script = 'Onclick="javascript:return false;"';
		   $movedown_img = sprintf($img_template,$movedown_msg,$movedown_msg,"down_dis.png");
		} else {
		   $down_script = "";
		}
    	}elseif($question->index == 0){
        	$up_script = 'Onclick="javascript:return false;"';
        	$top_script = 'Onclick="javascript:return false;"';
        	$down_script = "";
        	$bottom_script = "";
        	$moveup_img = sprintf($img_template,$moveup_msg,$moveup_msg,"up_dis.png");
        	$movetop_img = sprintf($img_template,$movetop_msg,$movetop_msg,"top_dis.png");
        }elseif ($question->index+1 == $num_questions){
        	$up_script = "";
        	$top_script = "";
        	$down_script = 'Onclick="javascript:return false;"';
        	$bottom_script = 'Onclick="javascript:return false;"';
        	$movedown_img = sprintf($img_template,$movedown_msg,$movedown_msg,"down_dis.png");
        	$movebottom_img = sprintf($img_template,$movebottom_msg,$movebottom_msg,"bottom_dis.png");
        }
        
        $field_template = <<<END
        	<tr class="%s" onmouseover="call_mouse_over_function(this)" onmouseout="call_mouse_out_function(this)">
				<td style="width:345px;">%s</td>
				<td style="text-align:center"><a href="{$url_edit}">$edit_img</a></td>
				<td style="text-align:center"><a href="{$url_up}" %s>$moveup_img</a></td>
				<td style="text-align:center"><a href="{$url_down}" %s>$movedown_img</a></td>
				<td style="text-align:center"><a href="{$url_top}" %s >$movetop_img</a></td>
				<td style="text-align:center"><a href="{$url_bottom} %s">$movebottom_img</a></td>
				<td style="text-align:center"><a href="{$url_export_question}">$export_question_img</a></td>
				<td style="text-align:center"><a onclick="return confirm('$delete_confirm_msg')" href="{$url_delete}">$delete_img</a></td>
			</tr>
END;
        
$body .= sprintf($field_template,$class,$text_question,$up_script,$down_script,$top_script,$bottom_script);

}

$body .= "</table>";


echo $body;

?>
