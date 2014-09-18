<?php

/**
 * @file plugins/importexport/paperPackageEdit/PaperPackageEditPlugin.inc.php
 *
 * Copyright (c) 2013 University of Potsdam, 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PaperPackageEditPlugin
 * @ingroup plugins_importexport_paperPackageEdit
 *
 * @brief Quick Submit one-page submission plugin
 */


import('classes.plugins.ImportExportPlugin');

class PaperPackageEditPlugin extends ImportExportPlugin {

      var $_articleID;

	/**
	 * Called as a plugin is registered to the registry
	 * @param $category String Name of category plugin was registered to
	 * @return boolean True iff plugin initialized successfully; if false,
	 * 	the plugin will not be registered.
	 */
	function register($category, $path) {
		$success = parent::register($category, $path);
		$this->addLocaleData();
		return $success;
	}

	/**
	 * Get the name of this plugin. The name must be unique within
	 * its category.
	 * @return String name of plugin
	 */
	function getName() {
		return 'PaperPackageEditPlugin';
	}

	function getDisplayName() {
		return __('plugins.importexport.paperPackageEdit.displayName');
	}

	function getDescription() {
		return __('plugins.importexport.paperPackageEdit.description');
	}

	function display(&$args) {
        if($this->_articleID == NULL){
             $currPageUrl = $_SERVER['REQUEST_URI'];
             $articleId = NULL;

             $pos = strrpos($currPageUrl , 'article=');
             $articleId = substr($currPageUrl , $pos + 8);
             if($pos = strpos($articleId, '/')){
                 $articleId = substr($articleId, 0, $pos);
                 //$this->_articleID=$articleId;
                }
              $this->_articleID=$articleId;
             // error_log('OJS - PPEP: Die ARTICELid: ' . $this->_articleID);
              $this->userIsEditor($this->_articleID);
	      //$userIsEditor= $this->userIsEditor($this->_articleID);
         }
 

	        $templateMgr =& TemplateManager::getManager();
		$templateMgr->register_function('plugin_url', array(&$this, 'smartyPluginUrl'));
                error_log('OJS PPEP: wie sei der Templatenpfad?: ' . $this->getTemplatePath());
		AppLocale::requireComponents(array(LOCALE_COMPONENT_OJS_AUTHOR, LOCALE_COMPONENT_OJS_EDITOR, LOCALE_COMPONENT_PKP_SUBMISSION));
		$this->setBreadcrumbs();

		if (array_shift($args) == 'saveSubmit') {
			$this->saveSubmit($args);
		} else {
			$this->import('PaperPackageEditForm');
			if (checkPhpVersion('5.0.0')) { // WARNING: This form needs $this in constructor
				$form = new PaperPackageEditForm($this);
			} else {
				$form =& new PaperPackageEditForm($this);
			}
			if ($form->isLocaleResubmit()) {
				$form->readInputData();
			} else {
				$form->initData();
			}
			$form->display($this->_articleID);
			//$form->display($articleId);
		}
	}
    
       function userIsEditor($articleId){
         $userIsEditor = false;
	 if($articleId != NULL){
              $user =& Request::getUser();
              $userId = $user->getId();
	      $articleDAO =& DAORegistry::getDAO('ArticleDAO');
              //TO DO: Fehlerbehandlung, falls einer die URL eingibt ohne articleId=xyz
	      //Wenn kein Article übergeben wird, gibt es auch keinen und damit keine aticleUserId...
	      $article =& $articleDAO->getArticle($articleId);
              $articleUserId = $article->getUserId();

              if($userId == $articleUserId){
                    $userIsEditor = true;
                   error_log('OJS - PPEP: UserID ' . $userId  . ' und ArticleUserID ' . $articleUserId . ' sind gleich.');
              }
               else{
//	          import('plugins.importexport.paperPackageEdit.accessDenied'); 
		   $templateMgr =& TemplateManager::getManager();

	           $templateMgr->assign('userIsNotEditor', true);
		   //$templateMgr->display($this->getTemplatePath() . 'accessDenied.tpl');
                   error_log('OJS - PPEP: UserID ' . $userId  . ' und ArticleUserID ' . $articleUserId . ' sind NICHT gleich.');
               }
         }
         //return $userIsEditor;
       }




	/**
	 * Save the submitted form
	 * @param $args array
	 */
	function saveSubmit($args) {
		error_log('OJS - PPEP: bei SaveSubmit ist die ArticleID: ' . $this->_articleID);
		$templateMgr =& TemplateManager::getManager();

		$this->import('PaperPackageEditForm');
		if (checkPhpVersion('5.0.0')) { // WARNING: This form needs $this in constructor
			$form = new PaperPackageEditForm($this);
		} else {
			$form =& new PaperPackageEditForm($this);
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
			$form->execute($this->_articleID);
			Request::redirect(null, 'manager', 'importexport', array('plugin', $this->getName()));
		} else if (!isset($editData) && $form->validate()) {
			$form->execute($this->_articleID);
			$templateMgr->display($this->getTemplatePath() . 'submitSuccess.tpl');
		} else {
			$form->display($this->_articleID);
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
		return $smarty->smartyUrl($params, $smarty) . '/article=' . $this->_articleID;
	}

}

?>
