<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit597e71ee5fe57761af214b983911f6cf
{
    public static $files = array (
        '0e6d7bf4a5811bfa5cf40c5ccd6fae6a' => __DIR__ . '/..' . '/symfony/polyfill-mbstring/bootstrap.php',
        '25072dd6e2470089de65ae7bf11d3109' => __DIR__ . '/..' . '/symfony/polyfill-php72/bootstrap.php',
        'e69f7f6ee287b969198c3c9d6777bd38' => __DIR__ . '/..' . '/symfony/polyfill-intl-normalizer/bootstrap.php',
        'f598d06aa772fa33d905e87be6398fb1' => __DIR__ . '/..' . '/symfony/polyfill-intl-idn/bootstrap.php',
        '98ac7ebbcd4b271b4f101d3af2543920' => __DIR__ . '/..' . '/layershifter/tld-extract/src/static.php',
        '49a1299791c25c6fd83542c6fedacddd' => __DIR__ . '/..' . '/yahnis-elsts/plugin-update-checker/load-v4p11.php',
    );

    public static $prefixLengthsPsr4 = array (
        'S' => 
        array (
            'Symfony\\Polyfill\\Php72\\' => 23,
            'Symfony\\Polyfill\\Mbstring\\' => 26,
            'Symfony\\Polyfill\\Intl\\Normalizer\\' => 33,
            'Symfony\\Polyfill\\Intl\\Idn\\' => 26,
        ),
        'L' => 
        array (
            'LayerShifter\\TLDSupport\\' => 24,
            'LayerShifter\\TLDExtract\\' => 24,
            'LayerShifter\\TLDDatabase\\' => 25,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Symfony\\Polyfill\\Php72\\' => 
        array (
            0 => __DIR__ . '/..' . '/symfony/polyfill-php72',
        ),
        'Symfony\\Polyfill\\Mbstring\\' => 
        array (
            0 => __DIR__ . '/..' . '/symfony/polyfill-mbstring',
        ),
        'Symfony\\Polyfill\\Intl\\Normalizer\\' => 
        array (
            0 => __DIR__ . '/..' . '/symfony/polyfill-intl-normalizer',
        ),
        'Symfony\\Polyfill\\Intl\\Idn\\' => 
        array (
            0 => __DIR__ . '/..' . '/symfony/polyfill-intl-idn',
        ),
        'LayerShifter\\TLDSupport\\' => 
        array (
            0 => __DIR__ . '/..' . '/layershifter/tld-support/src',
        ),
        'LayerShifter\\TLDExtract\\' => 
        array (
            0 => __DIR__ . '/..' . '/layershifter/tld-extract/src',
        ),
        'LayerShifter\\TLDDatabase\\' => 
        array (
            0 => __DIR__ . '/..' . '/layershifter/tld-database/src',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
        'Normalizer' => __DIR__ . '/..' . '/symfony/polyfill-intl-normalizer/Resources/stubs/Normalizer.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit597e71ee5fe57761af214b983911f6cf::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit597e71ee5fe57761af214b983911f6cf::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit597e71ee5fe57761af214b983911f6cf::$classMap;

        }, null, ClassLoader::class);
    }
}
