<?php
require_once('PackageDescription.php');
require_once('Tar.php');
require_once('descr_escape.php');
require_once('PackedSupplFile.php');


class OJSPackager{
    private $filesPath;
    private $rpositorydao;
    private $unpacker;
    
    // constructor
    public function __construct($filesPath, PackedSupplFile $unpacker){
        $this->filesPath    = $filesPath;//hostname+rpositorypath
        $daos               =& DAORegistry::getDAOs();
        $this->rpositorydao =& $daos['RpositoryDAO'];
	$this->unpacker =& $unpacker;
    }
    
    // returns true if $str begins with $sub
    private function beginsWith($str, $sub){
        return(substr($str, 0, strlen($sub)) == $sub);
    }
    // generates random string of variable length
    function randomStringGen($length){
        $random= "";
        srand((double)microtime()*1000000);
        $char_list = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $char_list .= "abcdefghijklmnopqrstuvwxyz";
        // Add the special characters to $char_list if needed
        for($i = 0; $i < $length; $i++){    
            $random .= substr($char_list,(rand()%(strlen($char_list))), 1);  
        }  
        return $random;
    }

    private function tmpDir() {
	$randomDirName = 'rpo-' . $this->randomStringGen(20);
        mkdir(sys_get_temp_dir() . '/' . $randomDirName);
        $tempDir = sys_get_temp_dir() . '/' . $randomDirName;
	return $tempDir;
    }
    
    // removes $dir recursively
    function deleteDirectory($dir){
        if(!file_exists($dir))
            return true;
        if(!is_dir($dir)) 
            return unlink($dir);
        foreach(scandir($dir) as $item){
            if($item == '.' || $item == '..')
                continue;
            if(!$this->deleteDirectory($dir.DIRECTORY_SEPARATOR.$item))
                return false;
        }
        return rmdir($dir);
    }


    // create R-style package for given $article_id
    public function writePackage($article_id, $suffix=''){
        error_log('OJS - OJSPackager: writePackage() wird aufgerufen und damit auch Tar.php');
	$suppPath = $this->filesPath    . "/" . $article_id . "/supp/";
        $preprPath = $this->filesPath   . "/" . $article_id . "/public/";
        $pd = new PackageDescription();
        $authors = "c(";
        $pkgName = "";        
        
        // get the handle used by OJS for the journal $article_id was published in
        #$journal_path = $this->rpositorydao->getJournalPath($article_id);
        $pd->set("Repository", "OpenScienceRepository");        
        $pd->set("Depends", "R (>= 2.14)");                
        
        // get the date the article was published and its title and description
        $result_artStmt = $this->rpositorydao->getArtStatement($article_id);
        $pd->set("Date", $result_artStmt['date_published']);
        $pd->set("Title", $result_artStmt['sv1']);
        $pd->set("Description", $result_artStmt['sv2']);
        
        // get author details of $article_id and put them into the DESCRIPTION file
        $result_authorStmt = $this->rpositorydao->getAuthorStatement($article_id);
        $numberOfAuthors = count($result_authorStmt);
	error_log('OJS - OJSPackager: Vor der Schleife: Welchen Wert hat number of Authors? ' . $numberOfAuthors . " " . json_encode($this->rpositorydao->getAuthorStatement($article_id)));
	
	foreach($result_authorStmt as $row_authorStmt){
	    if(strlen($pd->get("Author"))){
	    	$pd->set("Author", $pd->get("Author"). " and ");
	    }
	    $pd->set("Author", $pd->get("Author") .  $row_authorStmt['first_name'] . " " . $row_authorStmt['last_name']);

            if($this->beginsWith($authors, "c(person(")){
                $authors.=", ";
            }
            $authors .= 'person(';
            $authors .= 'given ="'. $row_authorStmt['first_name'] . '", family = "' . $row_authorStmt['last_name'] . '"';
            if($numberOfAuthors<=2){
	       $authorNamePkg = $row_authorStmt['last_name'];
	       $cleanedAuthorName = str_replace(' ','',$authorNamePkg);
	       $pkgName .= $cleanedAuthorName;
	                      error_log('OJS - OJSPackager: Hier kommt was zum Autor dazu? Im Ifzweig kommt ' . $pkgName);
            }
	    else{
	                   error_log('OJS - OJSPackager: Bin im Elsezweig, aber es stimmt etwas mit der naechsten If abfrage nicht...');
	        if($row_authorStmt['primary_contact'] == 1){
		  $authorNamePkg = $row_authorStmt['last_name'];
                  $cleanedAuthorName = str_replace(' ','',$authorNamePkg);
                  $pkgName .= $cleanedAuthorName;
	       error_log('OJS - OJSPackager wird hier was zum Autor hinzugefuegt? Im Elsezweig kommt ' . $pkgName);
	       }
	    }

	    if($row_authorStmt['middle_name'] != NULL && strlen($row_authorStmt['middle_name']) > 0){
                $authors.=', middle = "' . $row_authorStmt['middle_name'] . '"';
            }
            if($row_authorStmt['email'] != NULL && strlen($row_authorStmt['email']) > 0){
                $authors.=', email = "' . $row_authorStmt['email'] . '"';
            }
            $authors.=', role = c("aut"';
            if($row_authorStmt['primary_contact'] == 1){
                // primary_contact
                $authors.=', "cre"';
		$contMail = "";
		if($row_authorStmt['email'] != NULL && strlen($row_authorStmt['email']) > 0){
		    $contMail =  " <" . $row_authorStmt['email'] . ">";
		}
		$pd->set("Maintainer", $row_authorStmt['first_name'] . " " . $row_authorStmt['last_name'] . $contMail);
            }
            $authors.='))';
        }
	$authors.=')';
        $pd->set("Authors@R", $authors);
        $temp = explode('-', $pd->get("Date"));

	$pkgName_and_ver = $this->rpositorydao->getNameNew($article_id,$pkgName); 
        $pkgName = $pkgName_and_ver[0] . "_" . $pkgName_and_ver[1];
	#$pkgName = $pkgName. $versnumb['major'] .  '.' $versnumb['minor']; 
	
        unset($temp);
        $pd->set("Package", $pkgName_and_ver[0]);
       
        // path to write the package to
        $archive = array();
	$archive['name'] = sys_get_temp_dir() . '/' . $pkgName;
	$archive['targz'] = sys_get_temp_dir() . '/' . $pkgName . '.tar';
        $archive['zip'] = sys_get_temp_dir() . '/' . $pkgName . '.zip';

        error_log('OJS - OJSPackager: welchen Wert hat $archive: ' . $archive);

        $pd->set("Version", $pkgName_and_ver[1]);
        $pd->set("License", "CC BY-NC (http://creativecommons.org/licenses/by-nc/3.0/de/)");
        
        // create a directory under the system temp dir for and copy the article and its supplementary files to there
        $tempDirRoot = $this->tmpDir();
	error_log("name and version".json_encode($pkgName_and_ver));
	$tempDir = $tempDirRoot . "/" . $pkgName . "_" . $pkgName_and_ver[1];
	mkdir($tempDir);
	error_log("OJSPackager Tmpdir" . $tempDir);

	//$pdfile = $pdfile;
	//error_log("OJS - Rpository: ". $pdfile);
        rename($pd->toTempFile(), $tempDir .'/' . 'DESCRIPTION');
        $pw = new Archive_Tar($archive['targz'], 'gz');
        //$pw = new PharData($archive['targz']);
        $pharData = new PharData($archive['zip']);
	$result_fileStmt = $this->rpositorydao->getFileStatement($article_id);
        $submissionPreprintName = '';

	$suppCount = 0;
	foreach($result_fileStmt as $row_fileStmt) {
	    if($row_fileStmt['type'] == 'supp') {
		$suppCount++;
	    }
	}

        foreach($result_fileStmt as $row_fileStmt){
            $name       = $row_fileStmt['file_name'];
            $origName   = $row_fileStmt['original_file_name'];
            $type       = $row_fileStmt['type'];
        
            if($type == 'supp' ){
		if ($suppCount != 1 || !$this->unpacker->canHandle($suppPath . $name)) {
		    if(!is_dir($tempDir . '/' . 'inst')){
			mkdir($tempDir . '/' . 'inst', 0777, TRUE);
		    }
		    if(!copy($suppPath . $name, trim($tempDir) . '/' . 'inst' . '/' . $origName)){
		        error_log('OJS - rpository: error copying file: ' .$suppPath . $name . ' to: ' . trim($tempDir . '/' . 'inst' . '/' . $origName));
		    }
		} elseif ($this->unpacker->canHandle($suppPath . $name)) {
		    if(!is_dir($tempDir . '/' . 'inst')){       
			mkdir($tempDir . '/' . 'inst', 0777, TRUE);
		    }
		    $unpackDir = $this->tmpDir();
		    $this->unpacker->unpackInto($suppPath . $name, $unpackDir);
		    $contentDir = get_content_dir($unpackDir);
		    move_dir_contents($contentDir, $tempDir . '/' . 'inst');
		    $this->deleteDirectory($unpackDir);
		}
            }
            elseif($type == 'submission/original'){
                // TODO: pdf name wird nicht ermittelt // verzeichnisstruktur weicht von java version ab
                $submissionPreprintName = $origName;

                if(!is_dir($tempDir . '/' . 'inst' . '/' . 'preprint')){
                    mkdir($tempDir . '/' . 'inst' . '/' . 'preprint', 0777, TRUE);
                    }

                copy($this->filesPath   . "/" . $article_id . "/submission/original/" . $name, trim($tempDir) . '/' . 'inst' . '/' . 'preprint' . '/' . $submissionPreprintName);
            }
            elseif($type == 'public'){
                $submissionPreprintName = $origName;
                
                if(!is_dir($tempDir . '/' . 'inst' . '/' . 'preprint')){
                                        mkdir($tempDir . '/' . 'inst' . '/' . 'preprint', 0777, TRUE);
                                       }

                copy($preprPath . $name, trim($tempDir) . '/' . 'inst' . '/' . 'preprint' . '/' . $submissionPreprintName);
            }
        }
        error_log('OJS - OJSPackager: der Wert von $tempDir ' . $tempDir . ' und von pkgName ' . $pkgName); 
	//PhardataDirectory
        $pharData->buildFromDirectory($tempDir);
	//$archive['zip']=$pharData->buildFromDirectory($tempDir);
	// create the archive with the temp directory we created above
       //$pw->buildFromDirectory($tempDir);
       //$pw->compress(Phar::GZ);
        if(!$pw->createModify($tempDir, "$pkgName" . '/', $tempDir)){
            error_log("OJS - rpository: error writing archive");
        }
        
        // delete temp directory
        $this->deleteDirectory($tempDirRoot);
        
        // return the name of created archive
        error_log('OJS - OJSPackager: Ein Archive wurde erfolgreich zustande gebracht! mit dem archive ' . $archive);
	return $archive;
    }
 
    function &getUnpacker(){
         return $this->unpacker;
    }
 
 }
?>
