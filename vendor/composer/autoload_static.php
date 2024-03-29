<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit5cada7178d0c7ac619c88f6efb75b485
{
    public static $prefixLengthsPsr4 = array (
        'P' => 
        array (
            'Psr\\Http\\Server\\' => 16,
            'Psr\\Http\\Message\\' => 17,
        ),
        'I' => 
        array (
            'ILIAS\\Plugin\\Announcements\\' => 27,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Psr\\Http\\Server\\' => 
        array (
            0 => __DIR__ . '/..' . '/psr/http-server-handler/src',
        ),
        'Psr\\Http\\Message\\' => 
        array (
            0 => __DIR__ . '/..' . '/psr/http-message/src',
        ),
        'ILIAS\\Plugin\\Announcements\\' => 
        array (
            0 => __DIR__ . '/../..' . '/classes',
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit5cada7178d0c7ac619c88f6efb75b485::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit5cada7178d0c7ac619c88f6efb75b485::$prefixDirsPsr4;

        }, null, ClassLoader::class);
    }
}
