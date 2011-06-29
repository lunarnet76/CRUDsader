<?php

class Art_Data_Validator_Float {

    public static function isEmpty($value) {
        return empty($value);
    }

    public static function isValid($value, $dataOptions=array()) {
        $separator = isset($dataOptions['separator']) ? $dataOptions['separator'] : '.';
        $integerPartLength = isset($dataOptions['integerPartLength']) ? $dataOptions['integerPartLength'] : 10;
        $decimalPartLength = isset($dataOptions['decimalPartLength']) ? $dataOptions['decimalPartLength'] : 2;
        $ex = explode($separator, $value);
        return (count($ex) <= 2) && preg_match('|^[0-9]{1,' . $integerPartLength . '}$|', $ex[0]) && (empty($ex[1]) || preg_match('|^[0-9]{0,' . $decimalPartLength . '}$|', $ex[1])) ? true : 'not_float';
    }

    public static function javascriptValid(){
        return 'function(DOMItem){return /^[0-9]*\.?[0-9]*$/.test(DOMItem.val())?true:\'must be formatted like 17 or 17.50\';}';
    }

}