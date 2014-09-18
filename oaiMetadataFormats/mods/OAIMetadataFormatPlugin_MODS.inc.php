<?php
import('classes.plugins.OAIMetadataFormatPlugin');
class OAIMetadataFormatPlugin_MODS extends OAIMetadataFormatPlugin
{
        function getName(){
	return 'OAIMetadataFormatPlugin_MODS';
	}
	
        function getDisplayName(){
	return __('plugins.oaiMetadata.mods.displayName');
	}

	function getDescription(){
	return __('plugins.oaiMetadata.mods.description');
	}

	function getFormatClass(){
	return 'OAIMetadataFormat_MODS';
	}

	function getMetadataPrefix(){
	return 'mods';
	}

	function getSchema(){
	return 'http://catalog.clarin.eu/ds/ComponentRegistry/rest/registry/profiles/clarin.eu:cr1:p_1360931019822/xsd';
	}

	function getNamespace(){
	return 'http://www.clarin.eu/cmd/';
	}

 }
?>
