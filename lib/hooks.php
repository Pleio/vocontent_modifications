<?php

	function vocontent_modifications_write_hook($hook, $type, $return_value, $params) {
		$result = $return_value;
		
		if (!empty($params) && is_array($params)) {
			$user_guid = elgg_extract("user_id", $params);
			
			if (!empty($user_guid)) {
				// check if the user has access to the special content
				if (vocontent_modifications_has_access($user_guid)) {
					$access_id = vocontent_modifications_get_access_id();
					
					$result[$access_id] = elgg_echo("vocontent_modifications:access:label");
				}
			}
		}
		
		return $result;
	}