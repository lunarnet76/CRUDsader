<?php
namespace Art\Adapter\I18n\Translation {
    class None extends \Art\Adapter{
        public function translate($index) {
            return $index;
        }
    }
}