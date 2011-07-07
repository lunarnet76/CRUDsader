<?php
namespace Art\Object\Attribute{
    abstract class Wrapper{
        abstract public static function formatForDatabase($value,$options);
        abstract public static function formatFromDatabase($value,$options);
        abstract public static function isValid($value,$options);
        abstract public static function isEmpty($value,$options);
        abstract public static function HTMLInput($value,$id,$options,$htmlAttributes);
        abstract public static function javascriptValidator($options);
        abstract public static function generateRandom($options);
    }
}