<?php
namespace Art\Interfaces {
    interface Adaptable {
        /**
         * @param string $name
         * @return bool
         */
        public function hasAdapter($name=false);

        /**
         * @param string $name
         * @return \Art\Adapter
         */
        public function getAdapter($name=false);
        
        /**
         * @return array
         */
        public function getAdapters();
    }
}