<?php

// autoload_real.php @generated by Composer

class ComposerAutoloaderInit0c5a47672e50e01dd10b8d426b9b2b4e
{
    private static $loader;

    public static function loadClassLoader($class)
    {
        if ('Composer\Autoload\ClassLoader' === $class) {
            require __DIR__ . '/ClassLoader.php';
        }
    }

    /**
     * @return \Composer\Autoload\ClassLoader
     */
    public static function getLoader()
    {
        if (null !== self::$loader) {
            return self::$loader;
        }

        spl_autoload_register(array('ComposerAutoloaderInit0c5a47672e50e01dd10b8d426b9b2b4e', 'loadClassLoader'), true, true);
        self::$loader = $loader = new \Composer\Autoload\ClassLoader(\dirname(__DIR__));
        spl_autoload_unregister(array('ComposerAutoloaderInit0c5a47672e50e01dd10b8d426b9b2b4e', 'loadClassLoader'));

        require __DIR__ . '/autoload_static.php';
        call_user_func(\Composer\Autoload\ComposerStaticInit0c5a47672e50e01dd10b8d426b9b2b4e::getInitializer($loader));

        $loader->register(true);

        return $loader;
    }
}
