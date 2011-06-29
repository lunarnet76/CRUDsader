<?php
class Art_Data_Validator_Default {
    public static function isEmpty($value,$options=null){
        return empty($value);
    }
    public static function isValid($value,$options=null){
        return true;
    }

    public static function javascriptValid(){
        return '';
    }
}
?>
