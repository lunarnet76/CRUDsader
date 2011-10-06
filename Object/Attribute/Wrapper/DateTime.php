<?php
namespace CRUDsader\Object\Attribute\Wrapper{
    class DateTime extends \CRUDsader\Object\Attribute\Wrapper{
        
        
         public function formatForDatabase($value){
             return $value;
         }
         
         public function formatFromDatabase($value){
             if(preg_match('|([0-9]{4})-([0-9]{2})-([0-9]{2})\s([0-9]{2}):([0-9]{2}):[0-9]{2}|', $value,$match)){
                return $match[3].'/'.$match[2].'/'.substr($match[1],2).' '.$match[4].':'.$match[5];
             }
             return $value;
         }
         public function _isValid($value){
             return preg_match('[0-9]{4}-[0-9]{2}-[0-9]{2}\s[0-9]{2}:[0-9]{2}:[0-9]{2}', $value);
         }
         public function isEmpty($value){
             return empty($value);
         }
         public function HTMLInput($value,$id,$htmlAttributes){
             return '<input type="text" name="'.$id.'" value="'.$value.'" '.$htmlAttributes.'/>';   
         }
         public function javascriptValidator(){
             return '';
         }
         public function generateRandom(){
             return base_convert(rand(10e16, 10e20), 10, 36);
         }
    }
}
