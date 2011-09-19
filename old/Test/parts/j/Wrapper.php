<?php
namespace Art\Object\Attribute\Wrapper{
    class Test extends \Art\Object\Attribute\Wrapper{
         public static function formatForDatabase($value,$options){
             return 'd'.$value;
         }
         public static function formatFromDatabase($value,$options){
             return '2'.$value;
         }
         public static function isValid($value,$options){
             return $value!=='error';
         }
         public static function isEmpty($value,$options){
             return empty($value);
         }
         public static function HTMLInput($value,$id,$options,$htmlAttributes){
             return '<input type="text" name="'.$id.'" value="'.$value.'" '.$htmlAttributes.'/>';   
         }
         public static function javascriptValidator($options){
             return '';
         }
         public static function generateRandom($options){
             return base_convert(rand(10e16, 10e20), 10, 36);
         }
    }
}
