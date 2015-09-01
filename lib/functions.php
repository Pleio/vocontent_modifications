<?php

	function vocontent_modifications_validate_special_access(ElggUser $user) {
		$valid = false;
		
		if (!empty($user) && elgg_instanceof($user, "user")) {
			// remove access
			vocontent_modifications_revoke_access($user);
			
			// only SAML employee's are allowed special access
			if (vocontent_modifications_employee_validation($user)) {
				// online validation for emplyees
				$valid = vocontent_modifications_online_validation($user);
			}
			
			// validate SAML attribute / value combination
			if (!$valid) {
				$valid = vocontent_modifications_saml_attributes_validation($user);
			}
			
			// add the user to the access list
			if ($valid) {
				vocontent_modifications_grant_access($user);
			}
		}
	}
	
	function vocontent_modifications_employee_validation(ElggUser $user) {
		$result = false;
		
		if (!empty($user) && elgg_instanceof($user, "user")) {
			
			if (elgg_is_active_plugin("simplesaml")) {
				$saml_source = elgg_get_plugin_setting("saml_source", "vocontent_modifications");
				$affiliation_attribute = "eduPersonAffiliation";
				
				$affiliation_values = simplesaml_get_authentication_user_attribute($saml_source, $affiliation_attribute, $user->getGUID());
				
				// only staff is allowed this validation
				if (!empty($affiliation_values)) {
					$allowed_affiliations = array("employee", "staff");
					
					if (!is_array($affiliation_values)) {
						$affiliation_values = array($affiliation_values);
					}
					
					foreach ($affiliation_values as $affiliation_value) {
						if (in_array($affiliation_value, $allowed_affiliations)) {
							$result = true;
							break;
						}
					}
				}
			}
		}
		
		return $result;
	}
	
	function vocontent_modifications_saml_attributes_validation(ElggUser $user) {
		$result = false;
		
		if (!empty($user) && elgg_instanceof($user, "user")) {
			if (elgg_is_active_plugin("simplesaml")) {
				$saml_source = elgg_get_plugin_setting("saml_source", "vocontent_modifications");
				$attribute = elgg_get_plugin_setting("saml_attribute", "vocontent_modifications");
				$value = elgg_get_plugin_setting("saml_value", "vocontent_modifications");
				
				if (!empty($saml_source)  && !empty($attribute) && !empty($value)) {
					$saml_value = simplesaml_get_authentication_user_attribute($saml_source, $attribute, $user->getGUID());
					
					if (!is_array($saml_value)) {
						$saml_value = array($saml_value);
					}
					
					if (in_array($value, $saml_value)) {
						$result = true;
					}
				}
			}
		}
		
		return $result;
	}
	
	function vocontent_modifications_online_validation(ElggUser $user) {
		$result = false;
		
		if (!empty($user) && elgg_instanceof($user, "user")) {
			if (elgg_is_active_plugin("simplesaml")) {
				$saml_source = elgg_get_plugin_setting("saml_source", "vocontent_modifications");
				$brin_attribute = "nlEduPersonHomeOrganizationId";
				
				$validation_url = elgg_get_plugin_setting("validation_url", "vocontent_modifications");
				
				// can we validate against a source
				if (!empty($saml_source) && !empty($validation_url)) {
					$saml_value = simplesaml_get_authentication_user_attribute($saml_source, $brin_attribute, $user->getGUID());
					
					if (!empty($saml_value)) {
						if (!stristr($validation_url, "?id=")) {
							$validation_url .= "?id=" . $saml_value[0];
						} else {
							$validation_url .= $saml_value[0];
						}
						
						// prepare cURL for the call
						$ch = curl_init($validation_url);
						
						curl_setopt($ch, CURLOPT_TIMEOUT, 5); // wait max 5 seconds for response
						curl_setopt($ch, CURLOPT_USERAGENT, 'PHP ' . phpversion());

						// execute the cURL call
						curl_exec($ch);
						$curl_info = curl_getinfo($ch, CURLINFO_HTTP_CODE);
						
						// validate the reponse
						if (!empty($curl_info) && $curl_info == 200) {
							$result = true;
						}
					}
				}
			}
		}
		
		return $result;
	}
	
	function vocontent_modifications_grant_access(ElggUser $user) {
		$result = false;
		
		if (!empty($user) && elgg_instanceof($user, "user")) {
			$vocontent_access_id = vocontent_modifications_get_access_id();
			
			if (!empty($vocontent_access_id)) {
				$result = add_user_to_access_collection($user->getGUID(), $vocontent_access_id);
			}
		}
		
		return $result;
	}
	
	function vocontent_modifications_revoke_access(ElggUser $user) {
		$result = false;
		
		if (!empty($user) && elgg_instanceof($user, "user")) {
			$vocontent_access_id = vocontent_modifications_get_access_id();
				
			if (!empty($vocontent_access_id)) {
				$result = remove_user_from_access_collection($user->getGUID(), $vocontent_access_id);
			}
		}
		
		return $result;
	}
	
	function vocontent_modifications_get_access_id() {
		static $result;
		
		if (!isset($result)) {
			$result = false;
			
			if ($setting = elgg_get_plugin_setting("vocontent_access_id", "vocontent_modifications")) {
				$result = sanitise_int($setting, false);
			} elseif ($new_setting = vocontent_modifications_create_access_id()) {
				$result = sanitise_int($new_setting, false);
			}
		}
		
		return $result;
	}
	
	function vocontent_modifications_create_access_id() {
		$result = false;
		
		$plugin = elgg_get_plugin_from_id("vocontent_modifications");
		
		if (!empty($plugin)) {
			$id = create_access_collection("vo-content-special-access", $plugin->getGUID(), $plugin->site_guid);
			
			if (!empty($id)) {
				if (elgg_set_plugin_setting("vocontent_access_id", $id, "vocontent_modifications")) {
					$result = $id;
				}
			}
		}
		
		return $result;
	}
	
	function vocontent_modifications_has_access($user_guid = 0) {
		$result = false;
		
		$user_guid = sanitise_int($user_guid, false);
		
		if (empty($user_guid)) {
			$user_guid = elgg_get_logged_in_user_guid();
		}
		
		if (!empty($user_guid)) {
			// get the user
			if ($user = get_user($user_guid)) {
				if ($user->isAdmin()) {
					// the user is an admin, so always allowed
					$result = true;
				} elseif ($access_id = vocontent_modifications_get_access_id()) {
					// get the access collection
					$member_guids = get_members_of_access_collection($access_id, true);
					
					if (!empty($member_guids) && is_array($member_guids)) {
						// is this user present in the members of the access collection
						if (in_array($user->getGUID(), $member_guids)) {
							$result = true;
						}
					}
				}
			}
		}
		
		return $result;
	}
	