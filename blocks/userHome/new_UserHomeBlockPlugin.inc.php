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


import('lib.pkp.classes.plugins.BlockPlugin');

class UserHomeBlockPlugin extends BlockPlugin {

         // register hooks and daos to the ojs system
	   function register($category, $path){
		    if (parent::register($category, $path)) {    
		        error_log('OJS - UHBP: der Haken wird registriert.');
		         Registry::set('UserHomeBlockPlugin', $this);
	                 HookRegistry::register('Templates::User::Index::MyAccount', array(&$this, 'callback'));
                     }
	     }

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
		if (!Config::getVar('general', 'installed')) return BLOCK_CONTEXT_HOMEPAGE;
		return parent::getBlockContext();
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
        function getSupportedContexts() {
               //TO DO: Diese Lösung hilft noch nix weiter.
	       //BLOCK_CONTEXT_HOMEPAGE zeigt nur den Block auf der Ausgangsseite an
	       return array(BLOCK_CONTEXT_LEFT_SIDEBAR, BLOCK_CONTEXT_RIGHT_SIDEBAR, BLOCK_CONTEXT_HOMEPAGE);
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
 
//          function getContents(&$templateMgr) {
         function getContents(){
	   $templateMgr =& TemplateManager::getManager();
           $user =& Request::getUser();
           
	   $isUserHome=false;
          $context = $this->curPageURL();
	  $pattern = '#.*/index.php/[^/]*/user/[0-9]+#';
	  if(preg_match($pattern , $context)){
	   $isUserHome=true;
	  }
           $isUserHome=true;
//TO DO:der preg_match funktioniert noch nciht richtig 
	  $templateMgr->assign('isUserHome',$isUserHome);

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
               $templateMgr->assign('viewLink', '/index.php/mr2/article/view/');
               $templateMgr->assign('editLink', '/index.php/mr2/manager/importexport/plugin/PaperPackageEditPlugin/article=');
               //$templateMgr->display($this->getTemplatePath() . 'userPackages.tpl');
//               $templateMgr->display('/var/www/plugins/generic/rpository/userPackages.tpl');
               //HookRegistry::register('Templates::User::Index::Site', array(&$this, &$templateMgr,'block.tpl'));               
	       return parent::getContents($templateMgr);
            }


	}


}

?>
