<?php
/**
 * @author      Jean-Baptiste Verrey<jeanbaptiste.verrey@gmail.com>
 * @copyright   2011 Jean-Baptiste Verrey
 * @license     see license.txt
 * @since       0.1
 */
namespace CRUDsader\Adapter\I18n\Translation {
    /**
     * do basically nothing
     * @package CRUDsader\Adapter\I18n\Translation
     */
    class None extends \CRUDsader\Adapter{
        public function translate($index,$glue=',') {
            return is_array($index)?implode($glue,$index):$index;
        }
    }
}