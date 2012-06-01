<?php
/**
 * @author      Jean-Baptiste Verrey<jeanbaptiste.verrey@gmail.com>
 * @copyright   2011 Jean-Baptiste Verrey
 * @license     see license.txt
 * @since       0.1
 */
namespace CRUDsader\Object {
	abstract class IdentityMap {
		protected static $_objects = array();

		public static function exists($class, $id)
		{
			if(isset(self::$_objects[$class][$id]) && $class  == 'swarm'){
				pre($id,'IM'.$class);
				var_dump(self::$_objects[$class][$id]);
			}
			if (!\CRUDsader\Instancer::getInstance()->configuration->identityMap->sync)
				return false;
			return isset(self::$_objects[$class][$id]);
		}

		public static function get($class, $id)
		{
			return self::$_objects[$class][$id];
		}

		public static function add($object)
		{
			if (!$object->isPersisted())
				throw new IdentityMapException('Object cannot be added as it is not persisted');
			self::$_objects[$object->getClass()][$object->isPersisted()] = $object;
		}

		public static function remove($object)
		{
			unset(self::$_objects[$object->getClass()][$object->isPersisted()]);
		}

		public static function reset()
		{
			self::$_objects = array();
		}
	}
	class IdentityMapException extends \CRUDsader\Exception {
		
	}
}