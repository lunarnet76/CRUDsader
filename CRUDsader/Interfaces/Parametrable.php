<?php
namespace CRUDsader\Interfaces {
    interface Parametrable {
        /**
         * @param string $name
         */
        public function setParameter($name=false,$value=null);
        
        /**
         * @param string $name
         */
        public function unsetParameter($name=false);
        
        /**
         * @param string $name
         * @return bool
         */
        public function hasParameter($name=false);

        /**
         * @param string $name
         * @return \CRUDsader\Adapter
         */
        public function getParameter($name=false);
        
        /**
         * @return array
         */
        public function getParameters();
    }
}