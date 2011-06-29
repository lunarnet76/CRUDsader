<?php

class Art_Data_Validator_Integer{

    public static function isEmpty($value) {
        return empty($value);
    }

    public static function isValid($value, $dataOptions=array()) {
        return preg_match('|^[0-9]*|',$value) ? true : 'not_int';
    }

    public static function javascriptValid(){
        return 'function(DOMItem){return /^[0-9]*$/.test(DOMItem.val())?true:\'must be an integer\';}';
    }

}
?>

