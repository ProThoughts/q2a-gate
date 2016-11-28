<?php
        
                        
    if (!defined('QA_VERSION')) { // don't allow this page to be requested directly from browser
                    header('Location: ../../');
                    exit;   
    }               

    qa_register_plugin_module('module', 'qa-gate-admin.php', 'qa_gate_admin', 'GATE Questions');
    qa_register_plugin_layer('qa-gate-layer.php', 'GATE Layer');
    qa_register_plugin_overrides('qa-gate-overrides.php', 'GATE Override');
	qa_register_plugin_phrases('qa-gate-lang-*.php', 'gate_lang');    
/*                              
    Omit PHP closing tag to help avoid accidental output
*/                              
                          

