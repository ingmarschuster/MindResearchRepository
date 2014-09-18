<?php


import('lib.pkp.classes.form.validation.FormValidator');

class FormValidatorFileType extends FormValidator {

        /*
	*Constructor
        * @param $form Form the associated form
	* @param $field string the name of the associated field
        * @param $type string the type of check, either "required" or "optional"
        * @param $message string the error message for validation failures (i18n key)
        * @param $
	*/
	function FormValidatorFileType(&$form, $field, $type, $message) {
           parent::FormValidator($form, $field, $type, $message, $this);
	}

        function isValid(){
        $filePathId = $this->getFieldValue();
        import('lib.pkp.classes.file.TemporaryFileDAO');
	$tempFileDAO = new TemporaryFileDAO();
        $finfo = new finfo(FILEINFO_MIME);

	   foreach($filePathId as $fId){
		
	     if(is_numeric($fId)){

	       $user =& Request::getUser();
               $tempFile = $tempFileDAO->getTemporaryFile($fId,$user->getId());
	       $file_path= $tempFile->getFilePath();
               $fType = $finfo->file($file_path);
               $pattern = "/application\/zip/";
               $patternPDF = "/application\/pdf/";

               if(preg_match($pattern, $fType)) return true;
               
	       if(preg_match($patternPDF, $fType)) return true; 
               return false;
              }
	      return true;
	    }
       }
  }	
?>
