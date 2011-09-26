<?php
namespace Art\Interfaces {
    interface Helpable {

        public static function hasHelper($name);

        public static function getHelper($name);
    }
}