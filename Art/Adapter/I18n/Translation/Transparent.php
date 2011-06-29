<?php
namespace Art\Adapter\I18n\Translation {
    class Transparent extends \Art\Adapter{
        public function translate($index) {
            return $index;
        }
    }
}