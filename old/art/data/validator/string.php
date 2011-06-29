<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of string
 *
 * @author jb
 */
class Art_Data_Validator_String {

    /**
     * Wether or not the value is empty
     * 	@acces public
     * 	@return bool
     */
    public static function isEmpty($value) {
        $value=trim((string)$value);
        return empty($value);
    }

    public static function isValid($value, $dataOptions=array()) {
        $mask = isset($dataOptions['mask']) ? $dataOptions['mask'] : false;
        $maxLength = isset($dataOptions['maxLength']) ? $dataOptions['maxLength'] : 32;
        $minLength = isset($dataOptions['minLength']) ? $dataOptions['minLength'] : 2;
        $strlen = strlen($value);
        if ($maxLength && $strlen > $maxLength)
            return 'max_character_' . $maxLength;
        if ($minLength && $strlen < $minLength)
            return 'min_character_' . $minLength;
        if ($mask && !preg_match($mask, $value))
            return 'invalid character_' . $mask;
        return true;
    }

    public static function javascriptValid($dataOptions=array()){
        if(isset($dataOptions['mask'])){
            return 'function(jObj){return /'.str_replace(array('\''),array('\\\''),$dataOptions['mask']).'/.test(jObj.val())?true:\''.$dataOptions['maskError'].'\'}';
        }
        return '';
    }
}
?>
