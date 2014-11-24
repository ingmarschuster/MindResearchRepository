<?php

/**
 * @file plugins/generic/paperPackageUp/PaperPackageUpPlugin.inc.php
 *
 * Copyright (c) 2013 University of Potsdam, 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PaperPackageUpPlugin
 * @ingroup plugins_generic_paperPackageUp
 *
 * @brief Quick Submit one-page submission plugin
 */


import('lib.pkp.classes.plugins.GenericPlugin');

class PaperPackageUpPlugin extends GenericPlugin {

	/**
	 * Called as a plugin is registered to the registry
	 * @param $category String Name of category plugin was registered to
	 * @return boolean True iff plugin initialized successfully; if false,
	 * 	the plugin will not be registered.
	 */
	function register($category, $path) {
                if (parent::register($category, $path)) {
	               if ($this->getEnabled()) {
		          $this->addLocaleData();
			  HookRegistry::register('LoadHandler', array(&$this, 'callbackHandlePluginForm'));
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
        function callbackHandlePluginForm($hookName, $args){
	   $templateMgr =& TemplateManager::getManager();
	   $page =& $args[1];
	   $context=$this->curPageURL();
           $pattern='/PaperPackageUpPlugin/';
	   $pattern2='/saveSubmit/';

         if(preg_match($pattern , $context)){
                     define('HANDLER_CLASS', 'PaperPackageUpHandler');
                     $this->import('PaperPackageUpHandler');
                     $ppUpHandler= new PaperPackageUpHandler();
		    if(!preg_match($pattern2, $context)){
			  $ppUpHandler->view(&$this,$args);
		     }else{
		          $args[0]='saveSubmit';
			  $ppUpHandler->view(&$this,$args);
		     }
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
		return 'PaperPackageUpPlugin';
	}

	function getDisplayName() {
		return __('plugins.generic.paperPackageUpload.displayName');
	}

	function getDescription() {
		return __('plugins.generic.paperPackageUpload.description');
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
                   // array(
                     //       Request::url(null, 'manager'),
                       //     'user.role.manager'
                    //),
                    //array (
                      //      Request::url(null, 'manager', 'generic'),
                        //    'manager.generic'
                    //)
            );
            if ($isSubclass) $pageCrumbs[] = array(
                    Request::url(null, 'manager', 'generic', array('plugin', $this->getName())),
                    $this->getDisplayName(),
                    true
            );

            $templateMgr->assign('pageHierarchy', array_merge($pageCrumbs, $crumbs));
    }    



	function display(&$args) {
		$templateMgr =& TemplateManager::getManager();
		$templateMgr->register_function('plugin_url', array(&$this, 'smartyPluginUrl'));
		AppLocale::requireComponents(array(LOCALE_COMPONENT_OJS_AUTHOR, LOCALE_COMPONENT_OJS_EDITOR, LOCALE_COMPONENT_PKP_SUBMISSION));
		$this->setBreadcrumbs();

		if (array_shift($args) == 'saveSubmit') {
			$this->saveSubmit($args);
		} else {
		        $this->import('PaperPackageUpForm');
			//$this->import('plugins.generic.paperPackageUp.PaperPackageUpForm');
			if (checkPhpVersion('5.0.0')) { // WARNING: This form needs $this in constructor
				$form = new PaperPackageUpForm($this);
			} else {
				$form =& new PaperPackageUpForm($this);
			}
			if ($form->isLocaleResubmit()) {
				$form->readInputData();
			} else {
				$form->initData();
			}
			$form->display();
		}
	}


    // functions getManagmentVerbs() and manage() are kept as they are in GenericPlugin


	/**
	 * Save the submitted form
	 * @param $args array
	 */
	function saveSubmit($args) {
		$templateMgr =& TemplateManager::getManager();

		$this->import('PaperPackageUpForm');
		if (checkPhpVersion('5.0.0')) { // WARNING: This form needs $this in constructor
			$form = new PaperPackageUpForm($this);
		} else {
			$form =& new PaperPackageUpForm($this);
		}
		$form->readInputData();
		$formLocale = $form->getFormLocale();

		if (Request::getUserVar('addAuthor')) {
			$editData = true;
			$authors = $form->getData('authors');
			$authors[] = array();
			$form->setData('authors', $authors);
		} else if (($delAuthor = Request::getUserVar('delAuthor')) && count($delAuthor) == 1) {
			$editData = true;
			list($delAuthor) = array_keys($delAuthor);
			$delAuthor = (int) $delAuthor;
			$authors = $form->getData('authors');
			if (isset($authors[$delAuthor]['authorId']) && !empty($authors[$delAuthor]['authorId'])) {
				$deletedAuthors = explode(':', $form->getData('deletedAuthors'));
				array_push($deletedAuthors, $authors[$delAuthor]['authorId']);
				$form->setData('deletedAuthors', join(':', $deletedAuthors));
			}
			array_splice($authors, $delAuthor, 1);
			$form->setData('authors', $authors);

			if ($form->getData('primaryContact') == $delAuthor) {
				$form->setData('primaryContact', 0);
			}
		} else if (Request::getUserVar('moveAuthor')) {
			$editData = true;
			$moveAuthorDir = Request::getUserVar('moveAuthorDir');
			$moveAuthorDir = $moveAuthorDir == 'u' ? 'u' : 'd';
			$moveAuthorIndex = (int) Request::getUserVar('moveAuthorIndex');
			$authors = $form->getData('authors');

			if (!(($moveAuthorDir == 'u' && $moveAuthorIndex <= 0) || ($moveAuthorDir == 'd' && $moveAuthorIndex >= count($authors) - 1))) {
				$tmpAuthor = $authors[$moveAuthorIndex];
				$primaryContact = $form->getData('primaryContact');
				if ($moveAuthorDir == 'u') {
					$authors[$moveAuthorIndex] = $authors[$moveAuthorIndex - 1];
					$authors[$moveAuthorIndex - 1] = $tmpAuthor;
					if ($primaryContact == $moveAuthorIndex) {
						$form->setData('primaryContact', $moveAuthorIndex - 1);
					} else if ($primaryContact == ($moveAuthorIndex - 1)) {
						$form->setData('primaryContact', $moveAuthorIndex);
					}
				} else {
					$authors[$moveAuthorIndex] = $authors[$moveAuthorIndex + 1];
					$authors[$moveAuthorIndex + 1] = $tmpAuthor;
					if ($primaryContact == $moveAuthorIndex) {
						$form->setData('primaryContact', $moveAuthorIndex + 1);
					} else if ($primaryContact == ($moveAuthorIndex + 1)) {
						$form->setData('primaryContact', $moveAuthorIndex);
					}
				}
			}
			$form->setData('authors', $authors);
		} else if (Request::getUserVar('uploadSubmissionFile')) {
			$editData = true;
			$tempFileId = $form->getData('tempFileId');
			$tempFileId[$formLocale] = $form->uploadSubmissionFile('submissionFile');
			$form->setData('tempFileId', $tempFileId);
		}  else if (Request::getUserVar('uploadSupplementaryFile')) {
		        $editData = true;
			$tempSupplFileId = $form->getData('tempSupplFileId');
			
			$tempSupplFileId[$formLocale] = $form->uploadSupplementaryFile('supplementaryFile');
			$form->setData('tempSupplFileId', $tempSupplFileId);
		}

		if (Request::getUserVar('createAnother') && $form->validate()) {
			$form->execute();
			Request::redirect(null, 'manager', 'generic', array('plugin', $this->getName()));
		} else if (!isset($editData) && $form->validate()) {
			$form->execute();
			$templateMgr->display($this->getTemplatePath() . 'submitSuccess.tpl');
		} else {
			$form->display();
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
		error_log('OJS - PPUpP: smartyPluginUrl = ' . $smarty->smartyUrl($params, $smarty));
		return $smarty->smartyUrl($params, $smarty);
	}

}

?>
