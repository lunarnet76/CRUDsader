<?php
namespace CRUDsader\Object\Attribute\Wrapper{
    class Date extends \CRUDsader\Object\Attribute\Wrapper{
        
        
         public function formatForDatabase($value){
             $x=explode('/',$value);
             return $x[2].'-'.$x[1].'-'.$x[0];
         }
         
         public function formatFromDatabase($value){
             $x=explode('-',$value);
             return $x[2].'/'.$x[1].'/'.$x[0];
         }
         public function isValid($value){
             return true;
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
