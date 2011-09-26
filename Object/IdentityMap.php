<?php
namespace Art\Object {
    abstract class IdentityMap extends \Art\Singleton{
        protected static $_objects=array();

        public static function exists($class, $id) {
            return isset(self::$_objects[$class][$id]);
        }

        public static function get($class, $id) {
            return self::$_objects[$class][$id];
        }

        public static function add($object) {
            if (!$object->isPersisted())
                throw new IdentityMapException('Object cannot be added as it is not persisted');
            self::$_objects[$object->getClass()][$object->isPersisted()] = $object;
        }

        public static function remove($object) {
            unset(self::$_objects[$object->getClass()][$object->isPersisted()]);
        }
        
        public static function reset() {
            self::$_objects=array();
        }
    }
    class IdentityMapException extends \Art\Exception{}
}