<?php


import('lib.pkp.classes.form.validation.FormValidator');

class FormValidatorUpload extends FormValidator {

       var $_assocField;

        /*
	*Constructor
        * @param $form Form the associated form
	* @param $field string the name of the associated field
        * @param $type string the type of check, either "required" or "optional"
        * @param $message string the error message for validation failures (i18n key)
        * @param $asField string name of a field associated to the $field-field, necessary as both fields should be checked
        */
	function FormValidatorUpload(&$form, $field, $type, $message, $asField){
         $this->_assocField = $asField;
	 parent::FormValidator($form, $field, $type, $message, $this);
        }

         /**
         * @see FormValidator::isValid()
         * @return boolean
         */
	 function isValid(){
	 
         $fieldValue = $this->getFieldValue();
         $form =& $this->getForm();
        foreach($fieldValue as $fVal){
	if(empty($fVal)){
        
	    $assocFieldId = $form->getData($this->_assocField);

	foreach ($assocFieldId as $value) {
	    if (!is_numeric($value)){
		return false;

	              }
	            return true;
                }
        
	   }
	}
	 return true;
    }

}
?>
