<?php
namespace CRUDsader\Adapter\I18n\Translation {
    class Ini extends \CRUDsader\Adapter{
        public function translate($index,$glue=',') {
            return is_array($index)?implode($glue,$index):'{'.$index.'}';
        }
    }
}