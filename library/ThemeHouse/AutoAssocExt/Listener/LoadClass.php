<?php

class ThemeHouse_AutoAssocExt_Listener_LoadClass extends ThemeHouse_Listener_LoadClass
{

    protected function _getExtendedClasses()
    {
        return array(
            'ThemeHouse_AutoAssocExt' => array(
                'controller' => array(
                    'XenForo_ControllerPublic_Register'
                ), 
            ), 
        );
    }

    public static function loadClassController($class, array &$extend)
    {
        $extend = self::createAndRun('ThemeHouse_AutoAssocExt_Listener_LoadClass', $class, $extend, 'controller');
    }
}