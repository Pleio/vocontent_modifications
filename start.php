<?php

	require_once(dirname(__FILE__) .  "/lib/functions.php");
	require_once(dirname(__FILE__) .  "/lib/hooks.php");
	
	elgg_register_event_handler("init", "system", "vocontent_modifications_init");
	
	function vocontent_modifications_init() {
		
		// register events
		elgg_register_event_handler("ready", "system", "vocontent_modifications_ready");
		
		// register plugin hooks
		elgg_register_plugin_hook_handler("access:collections:write", "user", "vocontent_modifications_write_hook");
	}
	
	function vocontent_modifications_ready() {
		// validate if the user has special access
		if ($user = elgg_get_logged_in_user_entity()) {
			if (empty($_SESSION["vocontent_modifications_validation"])) {
				$_SESSION["vocontent_modifications_validation"] = true;
				
				vocontent_modifications_validate_special_access($user);
			}
		}
	}