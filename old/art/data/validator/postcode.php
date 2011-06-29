<?php
class Art_Data_Validator_Postcode {
    public static function isEmpty($value,$options=null){
        return empty($value);
    }
    public static function isValid($value,$options=null){
        return true;//preg_match('/^[a-zA-Z0-9]+[ ]?[a-zA-Z0-9]+$|^EIRE$|^[0-9]{5}$/',$value);
    }

    public static function javascriptValid(){
        return '';//function(jObj){return /^[a-zA-Z0-9]*[ ]?[a-zA-Z0-9]*$|^EIRE$|^[0-9]{5}$/.test(jObj.val())?true:\'must be alphanumeric, with spaces\';}';
    }
}
?>
