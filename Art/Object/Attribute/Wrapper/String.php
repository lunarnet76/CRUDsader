<?php
namespace Art\Object\Attribute\Wrapper{
    class String extends \Art\Object\Attribute\Wrapper{
        
        
         public function formatForDatabase($value){
             return $value;
         }
         public function formatFromDatabase($value){
             return $value;
         }
         public function isValid($value,$options){
             return true;
         }
         public function isEmpty($value,$options){
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
