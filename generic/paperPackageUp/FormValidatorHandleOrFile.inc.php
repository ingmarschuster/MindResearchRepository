<?php


import('lib.pkp.classes.form.validation.FormValidator');

class FormValidatorHandleOrFile extends FormValidator {

    var $_assocField;
    /*
    *Constructor
    * @param $form Form the associated form
    * @param $field string the name of the associated field
    * @param $type string the type of check, either "required" or "optional"
    * @param $message string the error message for validation failures (i18n key)
    * @param $asField string name of a field associated to the $field-field as it is necessary to check both fields here 
    */
    function FormValidatorHandleOrFile(&$form, $field, $type, $message, $asField){
    $this->_assocField = $asField;
    parent::FormValidator($form, $field, $type, $message, $this);
    }

       //
      // Public methods
     //
    /**
    * @see FormValidator::isValid()
    * Value is valid if it is empty and optional or validated by user-supplied function.
    * @return boolean
    */
     function isValid() {
     //ist was im Feld , wenn nein true, wenn ja, dann schauen ist was im anderen Feld, wenn ja false, wenn nein true
     $fieldValue=$this->getFieldValue();
     $form =& $this->getForm();
     foreach($fieldValue as $fVal){
	     if(!empty($fVal)){
	     $assocFieldValue=$form->getData($this->_assocField);
             foreach($assocFieldValue as $aFV){
	     if(is_numeric($aFV)) {
	     return false;
	              }
	           }
	     }
	 }
     return true;
									
    }
}

?>
