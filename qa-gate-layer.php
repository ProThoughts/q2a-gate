<?php

class qa_html_theme_layer extends qa_html_theme_base {

	function head_css()
	{
		qa_html_theme_base::head_css();
		$this->output("<style type='text/css'> .bad-question{
background:url('data:image/svg+xml;utf8,<svg xmlns=\"http://www.w3.org/2000/svg\"><text x=\"5%\" y=\"25%\" font-size=\"12\" fill=\"blue\" opacity=\"0.5\">Badly Formed Question!</text></svg>')!important;}</style>");
	}
	function doctype(){
		global $qa_request;
		$request = qa_request_parts();
		$request = $request[0];
		$categoryslugs = qa_request_parts(1);
		qa_html_theme_base::doctype();
		if((strcmp($request,'questions') == 0) || (strcmp($request,'unanswered') == 0)) {
			$request='questions';
			if (isset($categoryslugs))
				foreach ($categoryslugs as $slug)
					$request.='/'.$slug;
			$this->content['navigation']['sub']['gate']= array(
					'label' => qa_lang_html('gate_lang/gate'),
					'url' => qa_path_html($request, array('sort' => 'gate')),
					'selected' => (qa_get('sort') === 'gate')

					);
		}

	}

	public function q_view_content($q_view)
	{
		$content = isset($q_view['content']) ? $q_view['content'] : '';
		$postid = $q_view['raw']['postid'];
		if(qa_db_postmeta_get($postid, "bad"))
			$this->output('<div class="qa-q-view-content bad-question">');
		else
			$this->output('<div class="qa-q-view-content">');
		$this->output_raw($content);
		$this->output('</div>');
	}
	public function a_item_content($a_item)
	{
		$postid = $a_item['raw']['postid'];
		if(qa_db_postmeta_get($postid, "wrong"))
			$this->output('<div class="qa-a-item-content wrong-answer">');
		else
		 $this->output('<div class="qa-a-item-content">');
                $this->output_raw($a_item['content']);
                $this->output('</div>');
	}

	public function q_view_buttons($q_view)
	{
		if (!empty($q_view['form'])) {
			$user_level = qa_get_logged_in_level();
			if($user_level >=  qa_opt('qa_gate_questions_level') )
			{

				$postid=$q_view['raw']['postid'];
				if(qa_db_postmeta_get($postid, "bad") == null)
				{
					$q_view['form']['buttons'][] = array("tags" => "name='bad-button' value='$postid' title='".qa_lang_html('gate_lang/bad_pop')."'", "label" => qa_lang_html('gate_lang/bad')); 
				}
				else{
					$q_view['form']['buttons'][] = array("tags" => "name='notbad-button' value='$postid' title='".qa_lang_html('gate_lang/notbad_pop')."'", "label" => qa_lang_html('gate_lang/notbad')); 
				}
			}

		}
		qa_html_theme_base::q_view_buttons($q_view);
	}
	public function a_item_buttons($q_view)
	{
		if (!empty($q_view['form'])) {
			$user_level = qa_get_logged_in_level();
			if($user_level >=  qa_opt('qa_gate_answers_level') )
			{

				$postid=$q_view['raw']['postid'];
				if(qa_db_postmeta_get($postid, "wrong") == null)
				{
					$q_view['form']['buttons'][] = array("tags" => "name='wrong-button' value='$postid' title='".qa_lang_html('gate_lang/wrong_pop')."'", "label" => qa_lang_html('gate_lang/wrong')); 
				}
				else{
					$q_view['form']['buttons'][] = array("tags" => "name='notwrong-button' value='$postid' title='".qa_lang_html('gate_lang/notwrong_pop')."'", "label" => qa_lang_html('gate_lang/notwrong')); 
				}
			}

		}
		qa_html_theme_base::a_item_buttons($q_view);
	}



}

