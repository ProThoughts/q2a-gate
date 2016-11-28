<?php
class qa_gate_admin {

	function option_default($option) {

		switch($option) {
			case 'qa_gate_questions_level': 
				return QA_USER_LEVEL_MODERATOR;
			case 'qa_gate_answers_level': 
				return QA_USER_LEVEL_MODERATOR;
			default:
				return null;				
		}

	}

	function allow_template($template)
	{
		return ($template!='admin');
	}       

	function admin_form(&$qa_content)
	{                       

		// Process form input

		$ok = null;

		if (qa_clicked('qa_gate_questions_save')) {
			qa_opt('qa_gate_questions_level',qa_post_text('qa_gate_questions_level'));
			qa_opt('qa_gate_answers_level',qa_post_text('qa_gate_answers_level'));
			$ok = qa_lang('admin/options_saved');
		}
		$showoptions = array(
				QA_USER_LEVEL_EXPERT => "Experts",
				QA_USER_LEVEL_EDITOR => "Editors",
				QA_USER_LEVEL_MODERATOR =>      "Moderators",
				QA_USER_LEVEL_ADMIN =>  "Admins",
				QA_USER_LEVEL_SUPER =>  "Super Admins",
				);

		// Create the form for display

		$fields = array();
		$fields[] = array(

				'label' => 'Min. User Level Required for Bad Question Marking',
				'tags' => 'name="qa_gate_questions_level"',
				'value' => @$showoptions[qa_opt('qa_gate_questions_level')],
				'type' => 'select',
				'options' => $showoptions,
				);
		$fields[] = array(

				'label' => 'Min. User Level Required for Wrong Answer Marking',
				'tags' => 'name="qa_gate_answers_level"',
				'value' => @$showoptions[qa_opt('qa_gate_answers_level')],
				'type' => 'select',
				'options' => $showoptions,
				);

		return array(           
				'ok' => ($ok && !isset($error)) ? $ok : null,

				'fields' => $fields,

				'buttons' => array(
					array(
						'label' => qa_lang_html('main/save_button'),
						'tags' => 'NAME="qa_gate_questions_save"',
					     ),
					),
			    );
	}
}

