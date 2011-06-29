<?php
class Art_Data_Validator_Object {
    public static function isEmpty($value,$options=null){
        return empty($value);
    }
    public static function isValid($value,$options=null){
        return ctype_digit($value);
    }

    public static function javascriptValid(){
        return 'function(jObj){return \' \';}';
    }
}
?>
