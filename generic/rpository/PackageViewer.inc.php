<?php

import('lib.pkp.classes.form.Form');

class packageViewer extends Form {

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
	       $templateMgr->assign('editLink', '/index.php/mr2/manager/importexport/plugin/PaperPackageEditPlugin/article=');
	       //$templateMgr->display($this->getTemplatePath() . 'userPackages.tpl');
	       $templateMgr->display('/var/www/plugins/generic/rpository/userPackages.tpl');
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
            return $smarty->smartyUrl($params, $smarty) . '/userPackages';
         }


									    
    }
?>
