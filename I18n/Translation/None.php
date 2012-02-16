<?php
/**
 * @author      Jean-Baptiste Verrey<jeanbaptiste.verrey@gmail.com>
 * @copyright   2011 Jean-Baptiste Verrey
 * @license     see license.txt
 * @since       0.1
 */
namespace CRUDsader\I18n\Translation {
    /**
     * do basically nothing
     * @package CRUDsader\I18n\Translation
     */
    class None extends \CRUDsader\I18n\Translation{
         /**
         * @param string $index
         * @return string;
         */
        public function translate($index){return $index;}
    }
}