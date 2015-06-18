<?php

/**
 * @file plugins/generic/packages/PackagesPlugin.inc.php
 *
 * Copyright (c) 2013 University of Potsdam, 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PackagesPlugin
 * @ingroup plugins_generic_packages
 *
 */


import('lib.pkp.classes.plugins.GenericPlugin');

class PackagesPlugin extends GenericPlugin {
	
    /**
     * Called as a plugin is registered to the registry
     * @param $category String Name of category plugin was registered to
     * @return boolean True iff plugin initialized successfully; if false,
     *      the plugin will not be registered.
     */
    function register($category, $path) {
            if (parent::register($category, $path)) {
                   if ($this->getEnabled()) {
                      $this->addLocaleData();
                      HookRegistry::register('LoadHandler', array(&$this, 'callbackView'));
                    }
                    return true;
            }
            return false;
    }
	
    function curPageURL(){
         $pageURL = 'http';
        if(isset($_SERVER["HTTPS"]) AND $_SERVER["HTTPS"] == "on"){
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
	
    /**
    *callback function when the Loadhandler hook is registered. Checks if the current page is PaperPackageUpPlugin 
    *and displays it if true.
    *@hookname string name of registered hook
    *@$args array
    *@return true if current page is PaperPackageUpPlugin and will show its page. If false the page called before the hook will be loaded
    */
     function callbackView($hookName, $args){
        $templateMgr =& TemplateManager::getManager();
        $page =& $args[1];
        $context=$this->curPageURL();
        $pattern='/PackagesPlugin/';

      if(preg_match($pattern , $context)){
                  define('HANDLER_CLASS', 'PackagesHandler');
                  $this->import('PackagesHandler');
                  $packagesHandler= new PackagesHandler();
                  $packagesHandler->view(&$this);
                  
                  return true;
         }
         return false;

     }
	
     /**
      * Get the name of this plugin. The name must be unique within
      * its category.
      * @return String name of plugin
      */
     function getName() {
             return 'PackagesPlugin';
     }

     function getDisplayName() {
             return __('plugins.generic.packages.displayName');
     }

     function getDescription() {
             return __('plugins.generic.packages.description');
     }
	
     /**
      * Set the page's breadcrumbs, given the plugin's tree of items
      * to append.
      * @param $crumbs Array ($url, $name, $isTranslated)
      * @param $subclass boolean
      */
     function setBreadcrumbs($crumbs = array(), $isSubclass = false) {
             $templateMgr =& TemplateManager::getManager();
             $pageCrumbs = array(
                     array(
                             Request::url(null, 'user'),
                             'navigation.user'
                     ),
                    
             );
             if ($isSubclass) $pageCrumbs[] = array(
                     Request::url(null, 'manager', 'generic', array('plugin', $this->getName())),
                     $this->getDisplayName(),
                     true
             );

             $templateMgr->assign('pageHierarchy', array_merge($pageCrumbs, $crumbs));
     }
	
     function displayPackages(){
           //Wer ist der aktuelle USer
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
               $templateMgr->assign('viewLink', '/index.php/mr2/article/view/');
               $templateMgr->assign('editLink', '/index.php/mr2/PaperPackageEdPlugin/view/article=');
               $templateMgr->assign('uploadLink', '/index.php/mr2/PaperPackageUpPlugin/view/');               
	       //$templateMgr->display($this->getTemplatePath() . 'userPackages.tpl');
               $templateMgr->display('/var/www/plugins/generic/packages/userPackages.tpl');
           }

       }
	
       /**
        * Extend the {url ...} for smarty to support this plugin.
        */
       function smartyPluginUrl($params, &$smarty) {
               $path = array('plugin',$this->getName());
               if (is_array($params['path'])) {
                       $params['path'] = array_merge($path, $params['path']);
               } elseif (!empty($params['path'])) {
                       $params['path'] = array_merge($path, array($params['path']));
               } else {
                       $params['path'] = $path;
               }

               if (!empty($params['id'])) {
                       $params['path'] = array_merge($params['path'], array($params['id']));
                       unset($params['id']);
               }
               return $smarty->smartyUrl($params, $smarty);
       }

}
?>
