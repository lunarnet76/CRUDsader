<?php
class Art_Data_Validator_Date {
    public static function isEmpty($value,$options=null){
        return empty($value) || $value=='00/00/0000' || $value=='0000-00-00';
    }
    public static function isValid($value,$options=null){
        return true;
    }

    public static function javascriptValid(){
        return '';
    }
}
?>
