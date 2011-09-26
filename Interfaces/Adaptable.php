<?php
namespace CRUDsader\Interfaces {
    interface Adaptable {
        /**
         * @param string $name
         * @return bool
         */
        public function hasAdapter($name=false);

        /**
         * @param string $name
         * @return \CRUDsader\Adapter
         */
        public function getAdapter($name=false);
        
        /**
         * @return array
         */
        public function getAdapters();
    }
}