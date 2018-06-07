<?php
defined('BASEPATH') or exit('No direct script access allowed');
define('DB_MOTOR', 'devAN');
/*
|--------------------------------------------------------------------------
| Memcache Bloques
|--------------------------------------------------------------------------
|
 */
define('CACHE_PORTADA', 'portada:%s');
define('CACHE_TAG', "tag:%s");
define('CACHE_SEO', 'seo:%s');
define('CACHE_VICULOS', 'vinculos:%s');
define('CACHE_EXCLUSIVOS', 'exclusivos:%s');
define('CACHE_EPLANNING', 'eplanning:%s');
define('CACHE_MASLEIDAS', "masleidas:%s");
define('CACHE_MENU', 'menu');
define('CACHE_STRUCT', 'struct');
define('CACHE_TITULARES', 'titulares-');
define('CACHE_BANNER', "banner:%s");
define('CACHE_OPTA', "opta:%s:%s"); #fixture, ranking, goles
define('CACHE_AMP', "amp:%s");

/*
|--------------------------------------------------------------------------
| Publicidad
|--------------------------------------------------------------------------
|
 */
define('ADS_SLOT', "'/84748259/AN_%s_%s', %s, '%s'");

/*
|--------------------------------------------------------------------------
| MongoDB Colecciones
|--------------------------------------------------------------------------
|
 */
define('MDB_COMPACTO', 'compacto');
define('MDB_SEO', 'an_seo');
define('MDB_VINCULOS', 'an_vinculos');
define('MDB_MENU', 'an_menu');
define('MDB_EPLANNING', 'an_eplanning');
define('MDB_HORARIOS', 'an_horarios');
define('MDB_PROGRAMAS', 'an_programas');
define('MDB_ENCUESTAS', 'an_encuestas');
define('MDB_STRUCT', 'an_struct');
define('MDB_MINISITIO', 'an_minisitio');
define('MDB_TITULARES', 'an_titulares');
define('MDB_TAGS', 'an_tags');
define('MDB_OPTA', 'an_sportdata');

$motor = [
    'cache'   => [
        'servers' => [
            'default' => [
                'host'       => 'localhost',
                'port'       => '11211',
                'weight'     => '1',
                'persistent' => false,
            ],
        ],
        'config'  => [
            'engine'                => 'Memcached',
            'prefix'                => DB_MOTOR,
            'compression'           => MEMCACHE_COMPRESSED,
            'auto_compress_tresh'   => false,
            'auto_compress_savings' => 0.2,
            'expiration'            => 3600,
            'delete_expiration'     => 0,
        ],
    ],
    'mongodb' => [
        'host' => 'localhost',
        'port' => '27017',
        'db'   => 'test',
    ],
];

$config['motor'] = $motor;

/* End of file motor.php */
/* Location: ./application/config/motor.php */
