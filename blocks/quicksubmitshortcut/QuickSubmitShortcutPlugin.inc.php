<?php
import('lib.pkp.classes.plugins.BlockPlugin');

class QuickSubmitShortcutPlugin extends BlockPlugin {
    /**
    * Install default settings on journal creation.
    * @return string
    */
    function getContextSpecificPluginSettingsFile(){
        return $this->getPluginPath() . '/settings.xml';
    }
    
   /**
     * Get the display name of this plugin.
     * @return String
     */
    function getDisplayName() {
            return "QuickSubmitShortcut";
    }

    /**
     * Get a description of the plugin.
     */
    function getDescription() {
            return "QuickSubmit Shortcut Plugin";
    }
    
    function curPageURL(){
        $pageURL = 'http';
        if($_SERVER["HTTPS"] == "on"){
            $pageURL .= "s";
        }
        $pageURL .= "://";
        if($_SERVER["SERVER_PORT"] != "80"){
            $pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
            }
            else{
                $pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
            }
        return $pageURL;
    }
        
    
    function getContents(&$templateMgr){
	$templateMgr->assign('editor', Validation::isEditor());
	return parent::getContents($templateMgr);
    }


}
?>
