# ae-motor

### Configuración DEV 
/application/config/motor.php
```php
# Motor
define('DB_MOTOR', 'devAN');
define('IMAGEN', 0);
define('GALERIA', 2);
define('VIDEO', 3);
define('AUDIO', 4);
define('GALERIA+VIDEO', 5);
define('GALERIA+AUDIO', 6);
define('VIDEO+AUDIO', 7);
# Key Memcache
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
define('CACHE_OPTA', "opta:%s:%s");
define('CACHE_AMP', "amp:%s");
# Ads
define('ADS_SLOT', "'/84748259/AN_%s_%s', %s, '%s'");
# MongoDB
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
# Config
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
        'db'   => DB_MOTOR,
    ],
    'redis'   => [
        'host'   => 'localhost',
        'port'   => '6379',
        'prefix' => DB_MOTOR . ':',
    ],
];

$config['motor'] = $motor;
```
/application/core/MY_Controller.php
```php
use perudesarrollo\AeMotor\Core;
use perudesarrollo\AeMotor\Masleido;

class MY_Controller extends CI_Controller
{
    protected $motor;
    protected $masleido;
    public function __construct()
    {
        parent::__construct();
        $this->load->config('motor');
        $config = $this->config->item('motor');

        $config['twig']['views'] = APPPATH . 'views/';

        $this->motor    = new Core($config);
        $this->masleido = new Masleido($this->motor->redis());
    }

    public function index() {}

}
```
