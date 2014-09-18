<?php

/**
* This class is related to the MODS Scheme for providing metadata.
* Some elements are not included. Additional elements declarations you can 
* read about at http://www.clarin.eu/cmd/
*/

class OAIMetadataFormat_MODS extends OAIMetadataFormat {
        /**
        * @see OAIMetadataFormat#toXml
	*/
	function toXml(&$record, $format = null) {
            error_log("OAIMetadataFormat_MODS: toXml wird aufgerufen");
                           $article =& $record->getData('article');
			   $journal =& $record->getData('journal');
			   $section =& $record->getData('section');
			   $issue =& $record->getData('issue');
	                   $galleys =& $record->getData('galleys');
       

                           $articleId = $article->getArticleId();

                           //date the article was published
			   $datePublished = $article->getDatePublished();
			   if (!$datePublished) $datePublished = $issue->getDatePublished();
			   if ($datePublished) $datePublished = strtotime($datePublished);
														           
			   //date the article was last modified
	    		   $dateLastModified = $article->getLastModified();
    			   $dateLastModified = strtotime($dateLastModified);


                            // PaperPackage Name for direct Link to the Package
			    $daos    =& DAORegistry::getDAOs();
			    $rpositoryDao  =& $daos['RpositoryDAO'];
		            $packageName = htmlspecialchars(Core::cleanVar(strip_tags($rpositoryDao->getPackageName($articleId))));

                         // All Versions of the Package
			 $packages = $rpositoryDao->getAllPackagesByArticle($articleId);
			 error_log('OJS - MODS: Die Pakete: ' . json_encode($packages));

		       //PID 
		        $pid = $rpositoryDao->getPID($articleId);
		      //Name of the Package without tar.gz-Ending 
                       $filename = ereg_replace('(_)[0-9].[0-9](.tar.gz)','',$packageName);

		       //PackageType
                       $packageType = $this->getPackageType($articleId);
			
			//check if $pid is set
			if($pid==NULL) {
			$pidOutPut = 'NOT_ISSUED';
			}
			else{
			$pidOutPut = $pid;
			}


/*            $response = "<?xml version=\"1.0\" enconding=\"UTF-8\"?>" . */
  //    	   $response = "<CMD_ComponentSpec isProfile=\"true\" xsi:schemaLocation=\"http://www.clarin.eu/cmd https://infra.clarin.eu/cmd/general-component-schema.xsd\" xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\">\n" .
	  $response= "<CMD CMDVersion=\"1.1\" xmlns=\"http://www.clarin.eu/cmd/\" \n" . 
            "\txmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xsi:schemaLocation=\"http://www.clarin.eu/cmd/  http://catalog.clarin.eu/ds/ComponentRegistry/rest/registry/profiles/clarin.eu:cr1:p_1375880372976/xsd\">\n" . 
            //"<CMD_ComponentSpec isProfile=\"true\">\n" . 
	    //"\t<Header>\n" .
	    //"\t\t<ID>clarin.eu:cr1:p_1375880372976</ID>\n" . 
	   // "\t\t<Name>singlePaperPackage</Name>\n" .
	    //"\t\t<Description>Profile for single PaperPackage</Description>\n" . 
	   // "\t</Header>\n" . 
	    //"\t<CMD_Component CardinalityMax=\"1\" CardinalityMin=\"1\" name=\"singlePaperPackage\">\n" . 
	    //"\t\t<CMD_Component CardinalityMax=\"1\" CardinalityMin=\"1\" name=\"paperPackage\"/>\n" . 
	    //"\t</CMD_Component>\n" . 
	    //"</CMD_ComponentSpec>\n" . 

	    "\t<Header>\n" . 
	    "\t\t<MdCreator>Mind Research Repository CMDI Creator 1.0</MdCreator>\n" .   
	    "\t\t\t<MdCreationDate>" . date('Y-m-d', $dateLastModified) . "</MdCreationDate>\n" .  
            "\t\t\t<MdSelfLink>" . $pidOutPut . "</MdSelfLink>\n" . 
	    "\t\t\t<MdProfile>clarin.eu:cr1:p_1375880372976</MdProfile>\n" . 
	    "\t\t\t<MdCollectionDisplayName>Mind Research Repository</MdCollectionDisplayName>\n" . 
	    "\t</Header>\n" . 
	    "\t<Resources>\n" . 
	    "\t\t<ResourceProxyList>\n" ; 
	    foreach($packages as $package){  

	      if($package['pidv1'] == NULL){
	         $pid = $package['pidv2'];
	      }else{
	         $pid = $package['pidv1'];
	      }
              $current = NULL;
              if($package['current'] == 1){
	         $current = '-current';
	      } 
	      $response .= "\t\t\t<ResourceProxy id=\"mrr-" . htmlspecialchars(Core::cleanVar($pid)) . "-resource" . $current . "\">\n" . 
	       "\t\t\t\t<ResourceType mimetype=\"application/x-gzip\">Resource</ResourceType>\n" . 
	       "\t\t\t\t<ResourceRef>" . htmlspecialchars(Core::cleanVar(PKPRequest::getBaseUrl() . "/Rpository/src/contrib/" . $package['filename'])) . "</ResourceRef>\n" .  
	       "\t\t\t</ResourceProxy>\n" ; 
	       $n++;
	     }
	   
	   $response .= "\t\t</ResourceProxyList>\n" . 
	    "\t\t<JournalFileProxyList/>\n" . 
	    "\t\t<ResourceRelationList/>\n" . 
	    "\t</Resources>\n" . 
	    //"\t<Components>\n" . 
            "\t<CMD_ComponentSpec isProfile=\"true\">\n" .
            "\t\t<Header>\n" .
            "\t\t\t<ID>clarin.eu:cr1:p_1375880372976</ID>\n" .
            "\t\t\t<Name>singlePaperPackage</Name>\n" .
            "\t\t\t<Description>Profile for single PaperPackage</Description>\n" .
            "\t\t</Header>\n" .
            "\t\t<CMD_Component CardinalityMax=\"1\" CardinalityMin=\"1\" name=\"singlePaperPackage\">\n" .
            "\t\t\t<CMD_Component CardinalityMax=\"1\" CardinalityMin=\"1\" name=\"paperPackage\"/>\n" .
            //"\t\t</CMD_Component>\n" .
            //"\t</CMD_ComponentSpec>\n" .
	    "\t\t<paperPackage>\n" . 
            "\t\t\t<type>$packageType</type>\n" . 
            
	    "<mods ID=\"$filename\">\n" .
	    "\t<titleInfo>\n" . 
	    "\t\t<title>" . htmlspecialchars(Core::cleanVar(strip_tags($article->getArticleTitle()))) . "</title>\n" . 
 	    "\t</titleInfo>\n";
																		                
	// authors 
                foreach ($article->getAuthors() as $author) {
	                $response .= 
			"\t<name type=\"personal\">\n" . 
			"\t\t<namePart type=\"given\">" . htmlspecialchars(Core::cleanVar($author->getFirstName()) . (($s = $author->getMiddleName()) != ''?" $s":'')) . "</namePart>\n" . 
		        "\t\t<namePart type=\"family\">" . htmlspecialchars(Core::cleanVar($author->getLastName())) . "</namePart>\n" .  
			"\t\t<role>\n" . 
			"\t\t\t<roleTerm authority=\"marcrelator\" type=\"text\">author</roleTerm>\n" . 
			"\t\t</role>\n" .    
			"\t</name>\n";
			}

       //date published
	 $response .=
	"\t<originInfo>\n" . 
	"\t\t<dateIssued>" . strftime('%Y', $datePublished) . "</dateIssued>\n" .
        "\t</originInfo>\n" .

	"\t<typeOfResource>text</typeOfResource>\n" . 
	"\t<genre>journal article</genre>\n";
			
	 // Include abstract
         $abstract = htmlspecialchars(Core::cleanVar(strip_tags($article->getArticleAbstract())));
         if (!empty($abstract)) {
         $response .= "\t<abstract>$abstract</abstract>\n";
         }
    
	$response .=
	"\t<identifier type=\"hdl\">$pidOutPut</identifier>\n" . 
	"\t<location>\n" . 
	"\t\t<url displayLabel=\"Electronic full text\" access=\"raw object\">" .  htmlspecialchars(Core::cleanVar(PKPRequest::getBaseUrl() . "/Rpository/src/contrib/$packageName")) . "\" />\n" . 
	 "\t\t</url>\n" . 
	 "\t</location>\n"; 
																															    
	    $response .= "</mods>\n" . 
	                 "\t\t</paperPackage>\n" . 
	                 "\t\t</CMD_Component>\n" .
	                 "\t</CMD_ComponentSpec>\n" .
	      //           "\t</Components>\n" . 
		         "</CMD>\n" ;

	return $response;
   }                                                                                                                                                              

    function getPackageType($articleId){
      $daos    =& DAORegistry::getDAOs();
      $rpositoryDao  =& $daos['RpositoryDAO'];
      $filename = $rpositoryDao->getPackageName($articleId);
      if(!$filename == NULL){
        return 'legacy_r_package';
      }
      return 'NO_PACKAGE_UPLOADED';

    }


}
?>
