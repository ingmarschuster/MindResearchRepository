<?php


import('lib.pkp.classes.form.validation.FormValidator');

class FormValidatorHandle extends FormValidator {

        /*
        *Constructor
        * @param $form Form the associated form
        * @param $field string the name of the associated field
        * @param $type string the type of check, either "required" or "optional"
        * @param $message string the error message for validation failures (i18n key)
        */
        function FormValidatorHandle(&$form, $field, $type, $message){
	          parent::FormValidator($form, $field, $type, $message,$this);
        }

          /**
          * @see FormValidator::isValid()
          * @return boolean
          */

function isValid() {
      //Check with hdl.handle.net if handle is valid 
     $handle = $this->getFieldValue(); 
     //if(empty($handle)) return true;
     foreach($handle as $hdl){
     if(empty($hdl)) return true;
     $file = fopen("http://hdl.handle.net/" . $hdl, "r");
     if(empty($file)) return false;
     //error_log("OJS - FormValidatorHandle: Das kommt zurueck " . $file);
     return true;
     }
     
     }
	  
 }

?>
