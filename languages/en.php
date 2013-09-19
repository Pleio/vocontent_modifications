<?php

	$english = array(
		'vocontent_modifications:error:simplesaml' => "This plugin won't work without the SimpleSAML plugin enabled",
		
		'vocontent_modifications:settings:saml_source' => "SAML source selection",
		'vocontent_modifications:settings:saml_source:description' => "On which SAML source data should we apply the validation",
		
		'vocontent_modifications:settings:online:validation' => "Online validation settings",
		'vocontent_modifications:settings:online:validation:description' => "If the user doesn't have the above combination, an online check will be done on the BRIN number of the user",
		'vocontent_modifications:settings:online:validation_url' => "Please provide the URL to where the BRIN number can be validated",
		
		'vocontent_modifications:settings:saml' => "SAML attribute validation settings",
		'vocontent_modifications:settings:saml:description' => "Verify if the user has a certain SAML attribute and value",
		'vocontent_modifications:settings:saml:attribute' => "Specify the SAML attribute to check",
		'vocontent_modifications:settings:saml:value' => "What value must the SAML attribute have to be valid",
		
		'vocontent_modifications:access:label' => "VO-Content special members",
		'' => "",
	);
	
	add_translation("en", $english);