<?php
namespace CRUDsader\Adapter\I18n\Translation {
    class None extends \CRUDsader\Adapter{
        public function translate($index) {
            return $index;
        }
    }
}