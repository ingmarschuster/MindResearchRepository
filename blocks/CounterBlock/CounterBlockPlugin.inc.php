<?php
import('lib.pkp.classes.plugins.BlockPlugin');

class CountBlockPlugin extends BlockPlugin {
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
            return "CountBlock";
    }

    /**
     * Get a description of the plugin.
     */
    function getDescription() {
            return "CountBlock Plugin";
    }
        
    
    function getContents(&$templateMgr){        
        $templateMgr->assign('packageCount', 0);
        $result = $this->retrieve("select count(*) as c from published_articles WHERE date_published IS NOT NULL", array());
        if(!$result->EOF){
            $templateMgr->assign('packageCount', intval($row['c']));
        }
        
        return parent::getContents($templateMgr);
    }


}
?>
