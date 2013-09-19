<?php

	$plugin = elgg_extract("entity", $vars);
	
	if (elgg_is_active_plugin("simplesaml")) {
		//SAML source selection
		$sources = simplesaml_get_enabled_sources();
		$souce_options = array();
		if (!empty($sources)) {
			foreach ($sources as $source) {
				$label = simplesaml_get_source_label($source);
				$souce_options[$label] = $source;
			}
		}
		
		$title = elgg_echo("vocontent_modifications:settings:saml_source");
		
		$content = elgg_view("output/longtext", array("value" => elgg_echo("vocontent_modifications:settings:saml_source:description")));
		
		if (!empty($souce_options)) {
			$content .= elgg_view("input/radio", array("name" => "params[saml_source]", "value" => $plugin->saml_source, "options" => $souce_options));
		} else {
			$content .= elgg_view("output/longtext", array("value" => elgg_echo("notfound")));
		}
		
		echo elgg_view_module("inline", $title, $content);
		
		// SAML attribute vaidation
		$title = elgg_echo("vocontent_modifications:settings:saml");
		
		$content = elgg_view("output/longtext", array("value" => elgg_echo("vocontent_modifications:settings:saml:description")));
		
		$content .= "<div>";
		$content .= elgg_echo("vocontent_modifications:settings:saml:attribute");
		$content .= elgg_view("input/text", array("name" => "params[saml_attribute]", "value" => $plugin->saml_attribute));
		$content .= "</div>";
		
		$content .= "<div>";
		$content .= elgg_echo("vocontent_modifications:settings:saml:value");
		$content .= elgg_view("input/text", array("name" => "params[saml_value]", "value" => $plugin->saml_value));
		$content .= "</div>";
		
		echo elgg_view_module("inline", $title, $content);
		
		// online validation
		$title = elgg_echo("vocontent_modifications:settings:online:validation");
		
		$content = elgg_view("output/longtext", array("value" => elgg_echo("vocontent_modifications:settings:online:validation:description")));
		
		$content .= "<div>";
		$content .= elgg_echo("vocontent_modifications:settings:online:validation_url");
		$content .= elgg_view("input/url", array("name" => "params[validation_url]", "value" => $plugin->validation_url));
		$content .= "</div>";
		
		echo elgg_view_module("inline", $title, $content);
	} else {
		echo elgg_view("output/longtext", array("value" => elgg_echo("vocontent_modifications:error:simplesaml")));
	}