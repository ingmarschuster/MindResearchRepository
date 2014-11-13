<?php

/**
 * @file UserHomeBlockPlugin.inc.php
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class UserHomeBlockPlugin
 * @ingroup plugins_blocks_userHome
 *
 * @brief Class for "user home" block plugin
  */

  // $Id$

define('BLOCK_CONTEXT_MYACCOUNT',            0x00000004);

import('lib.pkp.classes.plugins.BlockPlugin');

class UserHomeBlockPlugin extends BlockPlugin {
    
    /**
     * Determine whether the plugin is enabled. Overrides parent so that
     * the plugin will be displayed during install.
     */
    function getEnabled() {
	    if (!Config::getVar('general', 'installed')) return true;
	    return parent::getEnabled();
    }

    /**
     * Install default settings on system install.
     * @return string
     */
     function getInstallSitePluginSettingsFile() {
	    return $this->getPluginPath() . '/settings.xml';
     }

    /**
     * Install default settings on journal creation.
     * @return string
     */
     function getContextSpecificPluginSettingsFile() {
	    return $this->getPluginPath() . '/settings.xml';
		}

    /**
     * Get the block context. Overrides parent so that the plugin will be
     * displayed during install.
     * @return int
     */
      function getBlockContext() {
	   //if (!Config::getVar('general', 'installed')) return BLOCK_CONTEXT_HOMEPAGE;
	   //return parent::getBlockContext();
          return BLOCK_CONTEXT_MYACCOUNT;
      }
      
      function &getContextMap() {
           static $contextMap = array(
                           BLOCK_CONTEXT_MYACCOUNT => 'Templates::User::Index::MyAccount', 
			   BLOCK_CONTEXT_LEFT_SIDEBAR => 'Templates::Common::LeftSidebar',
                           BLOCK_CONTEXT_RIGHT_SIDEBAR => 'Templates::Common::RightSidebar',
                    );

           $homepageHook = $this->_getContextSpecificHomepageHook();
           if ($homepageHook) $contextMap[BLOCK_CONTEXT_HOMEPAGE] = $homepageHook;

           HookRegistry::call('BlockPlugin::getContextMap', array(&$this, &$contextMap));
           return $contextMap;
       }


      /**
	 * Determine the plugin sequence. Overrides parent so that
	 * the plugin will be displayed during install.
	*/
       function getSeq() {
	   if (!Config::getVar('general', 'installed')) return 1;
	   return parent::getSeq();
       }

     /**
     * Get the display name of this plugin.
     * @return String
     */
      function getDisplayName() {
	   return __('plugins.block.userHome.displayName');
      }

      /**
      * Get a description of the plugin.
      */
      function getDescription() {
	   return __('plugins.block.userHome.description');
      }

       /**
       * Get the supported contexts (e.g. BLOCK_CONTEXT_...) for this block.
       *
       * @return array
       */
      /* function getSupportedContexts() {
            return array(BLOCK_CONTEXT_LEFT_SIDEBAR, BLOCK_CONTEXT_RIGHT_SIDEBAR, BLOCK_CONTEXT_HOMEPAGE);
       }*/

        function getContents(&$templateMgr) {
	   $templateMgr =& TemplateManager::getManager();
	   $user =& Request::getUser();
            $locale = 'en_US';
            $articles = NULL;
            $templateMgr->assign('user',$user);
            if(isset($user)){
                   $userId = $user->getId();
                   $articleDAO =& DAORegistry::getDAO('ArticleDAO');
                   $articles =& $articleDAO->getArticlesByUserId($userId, $journalId = null);
                   $titles=array();
                   $authors=array();
                   $articleIds=array();
                   $n=0;
	              $templateMgr->assign('articles',$articles);
		      $templateMgr->assign('locale',$locale);
		      $templateMgr->assign('uploadLink', '/index.php/mr2/PaperPackageUpPlugin/view/');
		      //$templateMgr->assign('uploadLink', '/index.php/mr2/generic/plugin/PaperPackageUpPlugin');
		      $templateMgr->assign('viewLink', '/index.php/mr2/article/view/');
		      $templateMgr->assign('editLink', '/index.php/mr2/PaperPackageEdPlugin/view/article=');
		      //$templateMgr->assign('editLink', '/index.php/mr2/manager/importexport/plugin/PaperPackageEditPlugin/article=');
                      return parent::getContents($templateMgr);
             }


    }

}

?>
