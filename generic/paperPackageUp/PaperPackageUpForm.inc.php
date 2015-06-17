<?php

/**
 * @file plugins/generic/paperPackageUp/PaperPackageUpForm.inc.php
 *
 * Copyright (c) 2013 University of Potsdam, 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PaperPackageUpForm
 * @ingroup plugins_generic_paperPackageUp
 *
 * @brief Form for PaperPackageUp one-page submission plugin
 */


import('lib.pkp.classes.form.Form');
class PaperPackageUpForm extends Form {

	/**
	 * Constructor
	 * @param $plugin object
	 */
	function PaperPackageUpForm(&$plugin) {
                parent::Form($plugin->getTemplatePath() . 'index.tpl');

		$journal =& Request::getJournal();

                import('plugins.generic.paperPackageUp.FormValidatorUpload');
                import('plugins.generic.paperPackageUp.FormValidatorFileType');
	        import('plugins.generic.paperPackageUp.FormValidatorHandle');
         	import('plugins.generic.paperPackageUp.FormValidatorHandleOrFile');
		$this->addCheck(new FormValidatorPost($this));
		$this->addCheck(new FormValidator($this, 'sectionId', 'required', 'author.submit.form.sectionRequired'));
                $this->addCheck(new FormValidatorUpload($this, 'submissionHandle', 'required', 'plugins.generic.paperPackageUpload.submissionHandleRequired', 'tempFileId'));
                $this->addCheck(new FormValidatorUpload($this, 'supplHandle', 'required', 'plugins.generic.paperPackageUpload.supplHandleRequired', 'tempSupplFileId'));
                $this->addCheck(new FormValidatorHandle($this, 'submissionHandle', 'required', 'plugins.generic.paperPackageUpload.submissionHandleIsWrong'));
                $this->addCheck(new FormValidatorHandle($this, 'supplHandle', 'required', 'plugins.generic.paperPackageUpload.supplHandleIsWrong'));
		$this->addCheck(new FormValidatorHandleOrFile($this, 'submissionHandle', 'required', 'plugins.generic.paperPackageUpload.submissionHandleOrFile', 'tempFileId'));
		$this->addCheck(new FormValidatorHandleOrFile($this, 'supplHandle', 'required', 'plugins.generic.paperPackageUpload.supplHandleOrFile', 'tempSupplFileId'));
		$this->addCheck(new FormValidatorFileType($this, 'tempSupplFileId', 'required', 'plugins.generic.paperPackageUpload.supplUnpackable'));
                $this->addCheck(new FormValidatorCustom($this, 'datePublished', 'required', 'plugins.generic.paperPackageUpload.dateRequired', create_function('$destination, $form', 'return is_int($form->getData(\'datePublished\'));'), array(&$this)));
		$this->addCheck(new FormValidatorCustom($this, 'sectionId', 'required', 'author.submit.form.sectionRequired', array(DAORegistry::getDAO('SectionDAO'), 'sectionExists'), array($journal->getId())));
		$this->addCheck(new FormValidatorCustom($this, 'authors', 'required', 'author.submit.form.authorRequired', create_function('$authors', 'return count($authors) > 0;')));
//		$this->addCheck(new FormValidatorCustom($this, 'destination', 'required', 'plugins.generic.paperPackageUpload.issueRequired', create_function('$destination, $form', 'return $destination == \'queue\'? true : ($form->getData(\'issueId\') > 0);'), array(&$this)));
		$this->addCheck(new FormValidatorArray($this, 'authors', 'required', 'plugins.generic.paperPackageUpload.authorRequiredFields', array('firstName', 'lastName')));
		//$this->addCheck(new FormValidator($this, 'authors-0-firstName', 'required', 'plugins.generic.paperPackageUpload.authorRequiredFirst'));
		//$this->addCheck(new FormValidator($this, 'authors-0-lastName', 'required', 'plugins.generic.paperPackageUpload.authorRequiredLast'));
		$this->addCheck(new FormValidatorArrayCustom($this, 'authors', 'required', 'user.profile.form.emailRequired', create_function('$email, $regExp', 'return empty($email) ? true : String::regexp_match($regExp, $email);'), array(ValidatorEmail::getRegexp()), false, array('email')));
		$this->addCheck(new FormValidatorArrayCustom($this, 'authors', 'required', 'user.profile.form.urlInvalid', create_function('$url, $regExp', 'return empty($url) ? true : String::regexp_match($regExp, $url);'), array(ValidatorUrl::getRegexp()), false, array('url')));
		$this->addCheck(new FormValidatorLocale($this, 'title', 'required', 'author.submit.form.titleRequired'));
// 		$this->addCheck(new FormValidatorLocale($this, 'originalJournal', 'required', 'plugins.generic.paperPackageUpload.originalJournalRequired'));

	}



	/**
	 * Get the names of fields for which data should be localized
	 * @return array
	 */
	function getLocaleFieldNames() {
		return array('tempFileId','tempSupplFileId', 'submissionHandle', 'supplHandle', 'title', 'abstract', 'originalJournal', 'discipline', 'subjectClass', 'subject', 'coverageGeo', 'coverageChron', 'coverageSample', 'type', 'sponsor');
	}

	/**
	 * Display the form.
	 */
	function display() {
                $templateMgr =& TemplateManager::getManager();
		$user =& Request::getUser();
		$journal =& Request::getJournal();
		$formLocale = $this->getFormLocale();

		$templateMgr->assign('journal', $journal);

		$sectionDao =& DAORegistry::getDAO('SectionDAO');
		$sections =& $sectionDao->getJournalSections($journal->getId());
		$sectionTitles = $sectionAbstractsRequired = array();
		while ($section =& $sections->next()) {
			$sectionTitles[$section->getId()] = $section->getLocalizedTitle();
			$sectionAbstractsRequired[(int) $section->getId()] = (int) (!$section->getAbstractsNotRequired());
			unset($section);
		}

		$templateMgr->assign('sectionOptions', array('0' => __('author.submit.selectSection')) + $sectionTitles);
		$templateMgr->assign('sectionAbstractsRequired', $sectionAbstractsRequired);

		$countryDao =& DAORegistry::getDAO('CountryDAO');
		$countries =& $countryDao->getCountries();
		$templateMgr->assign_by_ref('countries', $countries);

		import('classes.issue.IssueAction');
		$templateMgr->assign('issueOptions', IssueAction::getIssueOptions());

		import('classes.file.TemporaryFileManager');
		$temporaryFileManager = new TemporaryFileManager();
		$tempFileId = $this->getData('tempFileId');
		if (isset($tempFileId[$formLocale]) && $tempFileId[$formLocale] > 0) {
			$submissionFile = $temporaryFileManager->getFile($tempFileId[$formLocale], $user->getId());
			$templateMgr->assign_by_ref('submissionFile', $submissionFile);
		}
		$tempSupplFileId = $this->getData('tempSupplFileId');
		if (isset($tempSupplFileId[$formLocale]) && $tempSupplFileId[$formLocale] > 0) {
			$supplementaryFile = $temporaryFileManager->getFile($tempSupplFileId[$formLocale], $user->getId());
			$templateMgr->assign_by_ref('supplementaryFile', $supplementaryFile);
		}

		if (Request::getUserVar('addAuthor') || Request::getUserVar('delAuthor')  || Request::getUserVar('moveAuthor')) {
			$templateMgr->assign('scrollToAuthor', true);
		}

		if (Request::getUserVar('destination') == 'queue' ) {
			$templateMgr->assign('publishToIssue', false);
		} else {
			$templateMgr->assign('issueNumber', Request::getUserVar('issueId'));
			$templateMgr->assign('publishToIssue', true);
		}

		$templateMgr->assign('enablePageNumber', $journal->getSetting('enablePageNumber'));

		parent::display();
	}


	/**
	 * Assign form data to user-submitted data.
	 */
	function readInputData() {
		$this->readUserVars(
			array(
				'tempFileId',
				'tempSupplFileId',
				'submissionHandle',
		                'supplHandle',
				'destination',
				'issueId',
				'pages',
				'sectionId',
				'authors',
				'primaryContact',
				'title',
				'abstract', 
				'originalJournal',
				'discipline',
				'subjectClass',
				'subject',
				'coverageGeo',
				'coverageChron',
				'coverageSample',
				'type',
				'language',
				'sponsor',
				'citations',
				'title',
				'abstract'
			)
		);

		$this->readUserDateVars(array('datePublished'));

		$sectionDao =& DAORegistry::getDAO('SectionDAO');
		$section =& $sectionDao->getSection($this->getData('sectionId'));
		if ($section && !$section->getAbstractsNotRequired()) {
			$this->addCheck(new FormValidatorLocale($this, 'abstract', 'required', 'author.submit.form.abstractRequired'));
		}
	}

	/**
	 * Upload the submission file.
	 * @param $fileName string
	 * @return int TemporaryFile ID
	 */
	function uploadSubmissionFile($fileName) {
		import('classes.file.TemporaryFileManager');
		$temporaryFileManager = new TemporaryFileManager();
		$user =& Request::getUser();

		$temporaryFile = $temporaryFileManager->handleUpload($fileName, $user->getId());

		if ($temporaryFile) {
			return $temporaryFile->getId();
		} else {
			return false;
		}
	}
	
	/**
	 * Upload the supplementary file.
	 * @param $fileName string
	 * @return int TemporaryFile ID
	 */
	function uploadSupplementaryFile($fileName) {
		import('classes.file.TemporaryFileManager');
		$temporaryFileManager = new TemporaryFileManager();
		$user =& Request::getUser();

		$temporaryFile = $temporaryFileManager->handleUpload($fileName, $user->getId());
	      

		if ($temporaryFile) {
		        return $temporaryFile->getId();
		} else {
			return false;
		}
	}

      /**
      * Inserts files into database, which have not been a temporary file before.
      *@param $nameOfFile string
      *@param $fileStage constant, ARTICLE_FILE_SUBMISSION or ARTICLE_FILE_SUPP
      *@param $fileType string, txt/plain or application/pdf
      *@param $article_id string
      *@param $pathToFile string, path were to find the file which is inserted here
      */

       function insertArticleFile($nameOfFile, $fileStage, $fileType, $article_id, $pathToFile){
       
        import('classes.file.ArticleFileManager');
        $articleFileManager= new ArticleFileManager($article_id);
        $articleFileDao =& DAORegistry::getDAO('ArticleFileDAO');

        $fileTypePath = $articleFileManager->typeToPath($fileStage);
        $dir = $articleFileManager->filesDir . $fileTypePath . '/';

        $articleFile =& $articleFileManager->generateDummyFile($articleFileManager->article);
        $articleFile->setFileType($fileType);
        $articleFile->setOriginalFileName($nameOfFile);
        $articleFile->setType($fileTypePath);
        $articleFile->setRound($articleFileManager->article->getCurrentRound());
        $articleFile->setAssocId(null);


        $newFileName = $articleFileManager->generateFilename($articleFile, $fileStage, $articleFile->getOriginalFileName());
        if (!$articleFileManager->copyFile($pathToFile . $nameOfFile, $dir.$newFileName)) {
            // Delete the dummy file we inserted
              $articleFileDao->deleteArticleFileById($articleFile->getFileId());
              return false;
        }

         $articleFile->setFileSize(filesize($dir.$newFileName));
         $articleFileDao->updateArticleFile($articleFile);
         $articleFileManager->removePriorRevisions($articleFile->getFileId(), $articleFile->getRevision());
         
         return $articleFile->getFileId();

       }



       /**
       *Create a new PDF-file containing a direkt link to download the files metadata via the given handle
       *@param $handle string
       *@param $article_id string
       *@param $fileType string, type values should be 'submission' or 'supplementary' 
       */
      function createHandlePDF($handle, $article_id, $handleType){
        $nameOfFile;
	$fileStage;
	$endOfPath;
	$pdfInsertText;
        $fileType = 'application/pdf';

	if($handleType == 'submission'){
	    $nameOfFile = 'REMOTE_PAPER' . $article_id . '.pdf';
	    $fileStage = ARTICLE_FILE_SUBMISSION;
	    $endOfPath = '/submission/original/';
	    $pdfInsertText = 'preprint-files';
	}
	elseif($handleType == 'supplementary'){
	    $nameOfFile = 'REMOTE_SUPPLEMENTARY' . $article_id . '.pdf';
            $fileStage = ARTICLE_FILE_SUPP;
            $endOfPath = '/supp/';
            $pdfInsertText = 'supplementary-files';
	}
	else{
	    return false;
	}

       //create PDF with content
	import('plugins.generic.paperPackageUp.PDF');
	$pdf = new PDF();
        $pdf->AddPage();
	$pdf->SetFont('Arial','',13);
        $pdf->Write(5, "This PDF-file contains the " . $pdfInsertText  . " handle:  " . $handle);
	$pdf->Ln();
	$pdf->SetFont('','U');
	$pdf->SetTextColor(30,70,200);
        $link = $pdf->createHandleLink($handle);
	$pdf->Write(5,'Download ' . $pdfInsertText  . ' metadata',$link);
        $pdf->SetFont('');
	
	$journal =& Request::getJournal();
	$journal_id= $journal->getId();

       //make path and save pdf there
       $pathToArticleFile = Config::getVar('files', 'files_dir') . '/journals/' . $journal_id . '/articles/' . $article_id . $endOfPath;
       $pathToFile = Config::getVar('files', 'files_dir') . '/temp/';
       if(!file_exists($pathToArticleFile)){ 
	   mkdir($pathToArticleFile, 0777, true);
	}
	 $pdf->Output($pathToFile . $nameOfFile, 'F');
     
       //insert article into database
        $articleFileId = $this->insertArticleFile($nameOfFile, $fileStage, $fileType, $article_id, $pathToFile);
	return $articleFileId;
      }


	/**
	*Create a new txt-file with the given handle and insert it into database.
	*@param $handle string
	*@param $article_id string
	*@param $handleType string, type values should be 'submission' or 'supplementary' 
	*/
	function createHandleTXTFile($handle, $article_id, $handleType){
       
	   $journal =& Request::getJournal();
           $journal_id= $journal->getId();

           $nameOfFile;
           $fileStage;
           $endOfPath;
           $fileType = 'txt/plain';

      if($handleType == 'submission'){
             $nameOfFile = 'REMOTE_PAPER' . $article_id;
             $fileStage = ARTICLE_FILE_SUBMISSION;
             $endOfPath = '/submission/original/';
             $contentOfFile = 'Paper: ' . $handle;
      }
      elseif($handleType == 'supplementary'){
            $nameOfFile = 'REMOTE_SUPPLEMENTARY' . $article_id;
            $fileStage = ARTICLE_FILE_SUPP;
            $endOfPath = '/supp/'; 
	    $contentOfFile = 'Supplementary material: ' . $handle;
     }
     else{
            return false;
     }

       // create path to txt-file and the file with its content
       $pathToArticleFile = Config::getVar('files', 'files_dir') . '/journals/' . $journal_id . '/articles/' . $article_id . $endOfPath;
       $pathToFile = Config::getVar('files', 'files_dir') . '/temp/';
       if(!file_exists($pathToArticleFile)){
           mkdir($pathToArticleFile, 0777, true);
	}
	    $articleFile=fopen($pathToFile . $nameOfFile, "w");
            fwrite($articleFile, $contentOfFile);
            fclose($articleFile);


        //insert ArticleFile
            $articleFileId = $this->insertArticleFile($nameOfFile, $fileStage, $fileType, $article_id, $pathToFile);
            return $articleFileId;

	}



	/**
	 * Save settings.
	 */
	function execute() {
		$articleDao =& DAORegistry::getDAO('ArticleDAO');
		$signoffDao =& DAORegistry::getDAO('SignoffDAO');
		$sectionEditorSubmissionDao =& DAORegistry::getDAO('SectionEditorSubmissionDAO');

		$application =& PKPApplication::getApplication();
		$request =& $application->getRequest();
		$user =& $request->getUser();
		$router =& $request->getRouter();
		$journal =& $router->getContext($request);

		$article = new Article();
		$article->setLocale($journal->getPrimaryLocale()); // FIXME in bug #5543
		$article->setUserId($user->getId());
		$article->setJournalId($journal->getId());
		$article->setSectionId($this->getData('sectionId'));
		$article->setLanguage(String::substr($journal->getPrimaryLocale(), 0, 2));
		$article->setTitle($this->getData('title'), null); // Localized
	      //add Original Journal to Abstract  
	        $orig_journal = $this->getData('originalJournal');
		foreach (array_keys($orig_journal) as $locale){
		     if(empty($orig_journal[$locale])){
		          $orig_journal[$locale]= 'unpublished';
		     }
	        }
		$abstr = $this->getData('abstract');
	       foreach(array_keys($abstr) AS $abs_key){
		$abstr[$abs_key] .=  '  <p id="originalPub"> ' . $orig_journal[$abs_key]. ' </p> ';

		 $this->setData('abstract',$abstr);
		}
   	        $article->setAbstract($this->getData('abstract'), null); // Localized
	        $article->setDiscipline($this->getData('discipline'), null); // Localized
		$article->setSubjectClass($this->getData('subjectClass'), null); // Localized
		$article->setSubject($this->getData('subject'), null); // Localized
		$article->setCoverageGeo($this->getData('coverageGeo'), null); // Localized
		$article->setCoverageChron($this->getData('coverageChron'), null); // Localized
		$article->setCoverageSample($this->getData('coverageSample'), null); // Localized
		$article->setType($this->getData('type'), null); // Localized
		$article->setSponsor($this->getData('sponsor'), null); // Localized
		$article->setCitations($this->getData('citations'));
		$article->setPages($this->getData('pages'));

		// Set some default values so the ArticleDAO doesn't complain when adding this article
		$article->setDateSubmitted(Core::getCurrentDate());
		$article->setStatus(STATUS_PUBLISHED);
		$article->setSubmissionProgress(0);
		$article->stampStatusModified();
		$article->setCurrentRound(1);
		$article->setFastTracked(1);
		$article->setHideAuthor(0);
		$article->setCommentsStatus(0);

		// Insert the article to get it's ID
		$articleDao->insertArticle($article);
		$articleId = $article->getId();

		// Add authors
		$authors = $this->getData('authors');
		for ($i=0, $count=count($authors); $i < $count; $i++) {
			if ($authors[$i]['authorId'] > 0) {
				// Update an existing author
				$author =& $article->getAuthor($authors[$i]['authorId']);
				$isExistingAuthor = true;
			} else {
				// Create a new author
				$author = new Author();
				$isExistingAuthor = false;
			}

			if ($author != null) {
				$author->setSubmissionId($articleId);
				$author->setFirstName($authors[$i]['firstName']);
				$author->setMiddleName($authors[$i]['middleName']);
				$author->setLastName($authors[$i]['lastName']);
				if (array_key_exists('affiliation', $authors[$i])) {
					$author->setAffiliation($authors[$i]['affiliation'], null);
				}
				$author->setCountry($authors[$i]['country']);
				$author->setEmail($authors[$i]['email']);
				$author->setUrl($authors[$i]['url']);
				if (array_key_exists('competingInterests', $authors[$i])) {
					$author->setCompetingInterests($authors[$i]['competingInterests'], null);
				}
				$author->setBiography($authors[$i]['biography'], null);
				$author->setPrimaryContact($this->getData('primaryContact') == $i ? 1 : 0);
				$author->setSequence($authors[$i]['seq']);


				if ($isExistingAuthor == false) {
					$article->addAuthor($author);
				}
			}
		}

              // Check whether the user gave a handle and create a handleSubmissionFile in case
	       $submissionHandle=$this->getData('submissionHandle');
	       $handleSubmissionFileId;
               $handleCheck = FALSE;	    
	       
             //import FileManager before creating Files because otherwise naming of the copied files failes  
             import('classes.file.ArticleFileManager');

	       foreach (array_keys($submissionHandle) as $locale){
	           if(!empty($submissionHandle[$locale])){
		   $handleCheck = TRUE;
	           $handleSubmissionId = $this->createHandleTXTFile($submissionHandle[$locale], $articleId, 'submission');
	           $handleSubmissionPDFId = $this->createHandlePDF($submissionHandle[$locale], $articleId, 'submission');

		   //Add the handle submission files as galley
		   import('classes.article.ArticleGalley');
		   $galley = new ArticleGalley();
		     $galley->setArticleId($articleId);
		     $galley->setFileId($handleSubmissionPDFId);
		     $galley->setLocale($locale);
		     //$galley->setFileType('pdf');
		     $galley->setFileType('application/pdf');
		     $galley->setLabel('PDF');
		     
		     $galleyDao =& DAORegistry::getDAO('ArticleGalleyDAO');
		     $galleyDao->insertGalley($galley);
                     }
                   
	    if($handleCheck==TRUE){
		     if ($locale == $journal->getPrimaryLocale()) {
		     $article->setSubmissionFileId($handleSubmissionPDFId);
		     $article->SetReviewFileId($handleSubmissionPDFId);
		     }

		     // Update file search index
		     import('classes.search.ArticleSearchIndex');
		     if (isset($galley)) ArticleSearchIndex::updateFileIndex($galley->getArticleId(), ARTICLE_SEARCH_GALLEY_FILE, $galley->getFileId());
	       }
        }


		// Add the submission files as galleys
		import('classes.file.TemporaryFileManager');
		import('classes.file.ArticleFileManager');
		$tempFileIds = $this->getData('tempFileId');
		$temporaryFileManager = new TemporaryFileManager();
		$articleFileManager = new ArticleFileManager($articleId);
		$tempFileCheck=FALSE;

		foreach (array_keys($tempFileIds) as $locale) {
			$temporaryFile = $temporaryFileManager->getFile($tempFileIds[$locale], $user->getId());
			$fileId = null;
			if ($temporaryFile) {
			     $tempFileCheck=TRUE;
			        $fileId = $articleFileManager->temporaryFileToArticleFile($temporaryFile, ARTICLE_FILE_SUBMISSION);
				$fileType = $temporaryFile->getFileType();

				if (strstr($fileType, 'html')) {
					import('classes.article.ArticleHTMLGalley');
					$galley = new ArticleHTMLGalley();
				} else {
					import('classes.article.ArticleGalley');
					$galley = new ArticleGalley();
				}
				$galley->setArticleId($articleId);
				$galley->setFileId($fileId);
				$galley->setLocale($locale);

				if ($galley->isHTMLGalley()) {
					$galley->setLabel('HTML');
				} else {
					if (strstr($fileType, 'pdf')) {
						$galley->setLabel('PDF');
					} else if (strstr($fileType, 'postscript')) {
						$galley->setLabel('Postscript');
					} else if (strstr($fileType, 'xml')) {
						$galley->setLabel('XML');
					} else {
						$galley->setLabel(__('common.untitled'));
					}
				}

				$galleyDao =& DAORegistry::getDAO('ArticleGalleyDAO');
				$galleyDao->insertGalley($galley);

			}
               
               if($tempFileCheck==TRUE){
			if ($locale == $journal->getPrimaryLocale()) {
				$article->setSubmissionFileId($fileId);
				$article->SetReviewFileId($fileId);
			}

			// Update file search index
			import('classes.search.ArticleSearchIndex');
			if (isset($galley)) ArticleSearchIndex::updateFileIndex($galley->getArticleId(), ARTICLE_SEARCH_GALLEY_FILE, $galley->getFileId());
		
		}
		
              }  


                //Check whether the user gave a handle and create handleSupplFile in case

                 $supplHandle=$this->getData('supplHandle');
                 $handleSuppFileId = null;
	       
	       foreach (array_keys($supplHandle) as $locale){
                     if(!empty($supplHandle[$locale])){
                     $handleSuppFileId = $this->createHandleTXTFile($supplHandle[$locale], $articleId, 'supplementary');
                     $handleSupplPDFFileID = $this->createHandlePDF($submissionHandle[$locale], $articleId, 'supplementary'); 
		     }
               }

		//Add uploaded Supplementary file

		$tempSupplFileIds = $this->getData('tempSupplFileId');
		foreach (array_keys($tempSupplFileIds) as $locale) {
			$temporaryFile = $temporaryFileManager->getFile($tempSupplFileIds[$locale], $user->getId());
			$fileId = null;
			if ($temporaryFile) {
				$fileId = $articleFileManager->temporaryFileToArticleFile($temporaryFile, ARTICLE_FILE_SUPP);
				$fileType = $temporaryFile->getFileType();

			}


		}


		// Designate this as the review version by default.
		$authorSubmissionDao =& DAORegistry::getDAO('AuthorSubmissionDAO');
		$authorSubmission =& $authorSubmissionDao->getAuthorSubmission($articleId);
		import('classes.submission.author.AuthorAction');
		AuthorAction::designateReviewVersion($authorSubmission, true);

		// Accept the submission
		$sectionEditorSubmission =& $sectionEditorSubmissionDao->getSectionEditorSubmission($articleId);
		$articleFileManager = new ArticleFileManager($articleId);
		$sectionEditorSubmission->setReviewFile($articleFileManager->getFile($article->getSubmissionFileId()));
		import('classes.submission.sectionEditor.SectionEditorAction');
		SectionEditorAction::recordDecision($sectionEditorSubmission, SUBMISSION_EDITOR_DECISION_ACCEPT);

		// Create signoff infrastructure
		$copyeditInitialSignoff = $signoffDao->build('SIGNOFF_COPYEDITING_INITIAL', ASSOC_TYPE_ARTICLE, $articleId);
		$copyeditAuthorSignoff = $signoffDao->build('SIGNOFF_COPYEDITING_AUTHOR', ASSOC_TYPE_ARTICLE, $articleId);
		$copyeditFinalSignoff = $signoffDao->build('SIGNOFF_COPYEDITING_FINAL', ASSOC_TYPE_ARTICLE, $articleId);
		$copyeditInitialSignoff->setUserId(0);
		$copyeditAuthorSignoff->setUserId($user->getId());
		$copyeditFinalSignoff->setUserId(0);
		$signoffDao->updateObject($copyeditInitialSignoff);
		$signoffDao->updateObject($copyeditAuthorSignoff);
		$signoffDao->updateObject($copyeditFinalSignoff);

		$layoutSignoff = $signoffDao->build('SIGNOFF_LAYOUT', ASSOC_TYPE_ARTICLE, $articleId);
		$layoutSignoff->setUserId(0);
		$signoffDao->updateObject($layoutSignoff);

		$proofAuthorSignoff = $signoffDao->build('SIGNOFF_PROOFREADING_AUTHOR', ASSOC_TYPE_ARTICLE, $articleId);
		$proofProofreaderSignoff = $signoffDao->build('SIGNOFF_PROOFREADING_PROOFREADER', ASSOC_TYPE_ARTICLE, $articleId);
		$proofLayoutEditorSignoff = $signoffDao->build('SIGNOFF_PROOFREADING_LAYOUT', ASSOC_TYPE_ARTICLE, $articleId);
		$proofAuthorSignoff->setUserId($user->getId());
		$proofProofreaderSignoff->setUserId(0);
		$proofLayoutEditorSignoff->setUserId(0);
		$signoffDao->updateObject($proofAuthorSignoff);
		$signoffDao->updateObject($proofProofreaderSignoff);
		$signoffDao->updateObject($proofLayoutEditorSignoff);

		import('classes.author.form.submit.AuthorSubmitForm');
		AuthorSubmitForm::assignEditors($article);

		$articleDao->updateArticle($article);

		// Add to end of editing queue
		import('classes.submission.editor.EditorAction');
		if (isset($galley)) EditorAction::expediteSubmission($article);
                
		// Add to the current issue
		$issueDao =& DAORegistry::getDAO('IssueDAO');
		$issue =& $issueDao->getCurrentIssue($journal->getId());
                $issueId = $issue->getIssueId();
		//if ($this->getData('destination') == "issue") {
			// Add to an existing issue
		//	$issueId = $this->getData('issueId');
			$this->scheduleForPublication($articleId, $issueId);
		//}

		// Index article.
		import('classes.search.ArticleSearchIndex');
		ArticleSearchIndex::indexArticleMetadata($article);

		// Import the references list.
		$citationDao =& DAORegistry::getDAO('CitationDAO');
		$rawCitationList = $article->getCitations();
		$citationDao->importCitations($request, ASSOC_TYPE_ARTICLE, $articleId, $rawCitationList);
	}

	/**
	 * Schedule an article for publication in a given issue
	 */
	function scheduleForPublication($articleId, $issueId) {
		$sectionEditorSubmissionDao =& DAORegistry::getDAO('SectionEditorSubmissionDAO');
		$publishedArticleDao =& DAORegistry::getDAO('PublishedArticleDAO');
		$sectionDao =& DAORegistry::getDAO('SectionDAO');
		$issueDao =& DAORegistry::getDAO('IssueDAO');

		$journal =& Request::getJournal();
		$submission =& $sectionEditorSubmissionDao->getSectionEditorSubmission($articleId);
		$publishedArticle =& $publishedArticleDao->getPublishedArticleByArticleId($articleId);
		$issue =& $issueDao->getIssueById($issueId, $journal->getId());

		if ($issue) {
			// Schedule against an issue.
			if ($publishedArticle) {
				$publishedArticle->setIssueId($issueId);
				$publishedArticleDao->updatePublishedArticle($publishedArticle);
			} else {
				$publishedArticle = new PublishedArticle();
				$publishedArticle->setArticleId($submission->getArticleId());
				$publishedArticle->setIssueId($issueId);
				$publishedArticle->setDatePublished($this->getData('datePublished'));
				$publishedArticle->setSeq(REALLY_BIG_NUMBER);
				$publishedArticle->setViews(0);
				$publishedArticle->setAccessStatus(ARTICLE_ACCESS_ISSUE_DEFAULT);

				$publishedArticleDao->insertPublishedArticle($publishedArticle);

				// Resequence the articles.
				$publishedArticleDao->resequencePublishedArticles($submission->getSectionId(), $issueId);

				// If we're using custom section ordering, and if this is the first
				// article published in a section, make sure we enter a custom ordering
				// for it. (Default at the end of the list.)
				if ($sectionDao->customSectionOrderingExists($issueId)) {
					if ($sectionDao->getCustomSectionOrder($issueId, $submission->getSectionId()) === null) {
						$sectionDao->insertCustomSectionOrder($issueId, $submission->getSectionId(), REALLY_BIG_NUMBER);
						$sectionDao->resequenceCustomSectionOrders($issueId);
					}
				}
			}
		} else {
			if ($publishedArticle) {
				// This was published elsewhere; make sure we don't
				// mess up sequencing information.
				$publishedArticleDao->resequencePublishedArticles($submission->getSectionId(), $publishedArticle->getIssueId());
				$publishedArticleDao->deletePublishedArticleByArticleId($articleId);
			}
		}
		$submission->stampStatusModified();

		if ($issue && $issue->getPublished()) {
			$submission->setStatus(STATUS_PUBLISHED);
		} else {
			$submission->setStatus(STATUS_QUEUED);
		}

		$sectionEditorSubmissionDao->updateSectionEditorSubmission($submission);
	}
}

?>
