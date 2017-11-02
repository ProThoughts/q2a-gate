<?php
function qa_db_posts_basic_selectspec($voteuserid=null, $full=false, $user=true)
{
	$request = qa_request_parts();
	$request = $request[0];
	if(($request ===  'questions' || $request ===  'unanswered') && (qa_get('sort') == 'gate') )
	{
		$res = qa_db_posts_basic_selectspec_base($voteuserid, $full, $user);
		//$res['source'] .= " join ^posts gfeat on ^posts.postid = gfeat.postid and (strcmp(substring(gfeat.title,1,4), 'GATE') =0)";

		$res['source'] .= " join ^posts gfeat on ^posts.postid = gfeat.postid and gfeat.title regexp 'GATE[1-2][0-9][0-9][0-9].*'";
		//$res['source'] .= " join ^posts gfeat on ^posts.postid = gfeat.postid and gfeat.title regexp '.*'";
		return $res;
	}
	return  qa_db_posts_basic_selectspec_base($voteuserid, $full, $user);
}
function qa_q_list_page_content($questions, $pagesize, $start, $count, $sometitle, $nonetitle,
		$navcategories, $categoryid, $categoryqcount, $categorypathprefix, $feedpathprefix, $suggest,
		$pagelinkparams=null, $categoryparams=null, $dummy=null)
{
	$request = qa_request_parts();
	$request = $request[0];
	if(($request ===  'questions' || $request ===  'unanswered') && (qa_get('sort') == 'gate') )
	{
		$pagelinkparams= array("sort" => "gate");
		$categorytitlehtml = qa_html($navcategories[$categoryid]['title']);		 
		$sometitle = $categoryid != null ? qa_lang_html_sub('gate_lang/gate_qs_in_x', $categorytitlehtml) : qa_lang_html('gate_lang/gate_qs_title');
		$nonetitle = $categoryid != null ? qa_lang_html_sub('featured_lang/nogate_qs_in_x', $categorytitlehtml) : qa_lang_html('gate_lang/nogate_qs_title');
//		$feedpathprefix =  null;
		if(!$categoryid){
			$count=qa_opt('gate_qcount');
		}
		else{
			$count = qa_db_categorymeta_get($categoryid, 'gatecount');			
		}
	}

	return qa_q_list_page_content_base($questions, $pagesize, $start, $count, $sometitle, $nonetitle,
			$navcategories, $categoryid, $categoryqcount, $categorypathprefix, $feedpathprefix, $suggest,
			$pagelinkparams, $categoryparams, $dummy);
}
function category_path_gateqcount_update($postid)
{
	$pathq = "select categoryid, catidpath1, catidpath2, catidpath3 from ^posts where postid = #";
	$result = qa_db_query_sub($pathq, $postid);
	$path = qa_db_read_one_assoc($result, true);
	if($path){
	ifcategory_gateqcount_update($path['categoryid']); // requires QA_CATEGORY_DEPTH=4
	ifcategory_gateqcount_update($path['catidpath1']);
	ifcategory_gateqcount_update($path['catidpath2']);
	ifcategory_gateqcount_update($path['catidpath3']);
	}
}

function updategatecount($postid)
{
	$query = qa_db_query_sub("select count(*) from ^posts where title regexp 'GATE[1-2][0-9][0-9][0-9].*'");
	$count = qa_db_read_one_value($query);
	qa_opt('gate_qcount', $count);
	category_path_gateqcount_update($postid);
}



function ifcategory_gateqcount_update($categoryid)
{
	if (isset($categoryid)) {
		// This seemed like the most sensible approach which avoids explicitly calculating the category's depth in the hierarchy
		$filter = " and postid in (select postid from ^posts where title regexp 'GATE[1-2][0-9][0-9][0-9].*')";
		$query = qa_db_query_sub(
				"select GREATEST( (SELECT COUNT(*) FROM ^posts WHERE categoryid=# AND type='Q'".$filter."), (SELECT COUNT(*) FROM ^posts WHERE catidpath1=# AND type='Q'".$filter."), (SELECT COUNT(*) FROM ^posts WHERE catidpath2=# AND type='Q'".$filter."), (SELECT COUNT(*) FROM ^posts WHERE catidpath3=# AND type='Q'".$filter.") ) ",
				$categoryid, $categoryid, $categoryid, $categoryid
				); // requires QA_CATEGORY_DEPTH=4
		$count = qa_db_read_one_value($query);

		qa_db_categorymeta_set($categoryid, 'gatecount', $count);
	}
}






function qa_check_page_clicks()
{
	global $qa_page_error_html;
	global  $qa_request;

	if ( qa_is_http_post() ) {
		if(qa_get_logged_in_level()>=  qa_opt('qa_gate_questions_level'))
		{
			require_once QA_INCLUDE_DIR."qa-util-string.php";
			if(isset($_POST['bad-button'])  )
			{
				$postid = $_POST['bad-button'];	
				$update = "update ^posts set tags = $ where postid = #";
				$info = qa_post_get_full($postid);
				$tags = $info['tags'];
				$tag = qa_tagstring_to_tags($tags);
				if(!in_array("bad-question", $tag)){
					array_push($tag, "bad-question");
					$tags = qa_tags_to_tagstring($tag);
					qa_db_query_sub($update, $tags, $postid);
					
				}
				qa_db_postmeta_set($postid, "bad", "1");
				updategatecount($postid);
				qa_redirect( qa_request(), $_GET );
			}
			if(isset($_POST['notbad-button'])  )
			{
				$postid = $_POST['notbad-button'];	
				$update = "update ^posts set tags = $ where postid = #";
                                $info = qa_post_get_full($postid);
                                $tags = $info['tags'];
                                $tag = qa_tagstring_to_tags($tags);
                                if($key = array_search("bad-question", $tag)){
                                        array_splice($tag, $key, 1);
                                        $tags = qa_tags_to_tagstring($tag);
                                qa_db_query_sub($update, $tags, $postid);
                                        
                                }

				qa_db_postmeta_clear($postid, "bad");
				updategatecount($postid);
				qa_redirect( qa_request(), $_GET );
			}
		}
		if(qa_get_logged_in_level()>=  qa_opt('qa_gate_answers_level'))
		{
			if(isset($_POST['wrong-button'])  )
			{
				$postid = $_POST['wrong-button'];	
				qa_db_postmeta_set($postid, "wrong", "1");
				qa_redirect( qa_request(), $_GET );
			}
			if(isset($_POST['notwrong-button'])  )
			{
				$postid = $_POST['notwrong-button'];	
				qa_db_postmeta_clear($postid, "wrong");
				qa_redirect( qa_request(), $_GET );
			}
		}
	}

	qa_check_page_clicks_base();
}


?>
