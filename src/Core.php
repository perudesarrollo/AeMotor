<?php
namespace perudesarrollo\AeMotor;

use Mobile_Detect;
use MongoDB\Client;

class Core
{
    protected $cache_segundos = 3600;
    protected $config;

    public $cache;
    public $cache_key;
    public $debug;
    public $mobileDetect;

    public function __construct($cnf = [])
    {
        if (!is_array($cnf)) {
            throw new Exception('Core Motor Library: empty config to array');
        }
        $this->config       = $cnf;
        $this->mobileDetect = new Mobile_Detect();
        $this->cache        = new Memcache($this->config['cache']);
    }

    public function ads($key = 'home.home', $no_mostrar_googleIma = false)
    {
        $seccion = (array) $this->eplanning($key);
        $size    = (array) $this->eplanning('sizes');

        $size['movil_middle_banner_2'] = isset($size['movil_middle_banner_1']) ? $size['movil_middle_banner_1'] : null;

        $pub        = [];
        $div_nombre = !empty($seccion['nombre']) ? rtrim($seccion['nombre']) : null;

        $pub = [
            'skin'                  => [
                'div'    => !empty($seccion['div_skin']) ? $seccion['div_skin'] : null,
                'codigo' => sprintf(ADS_SLOT, $div_nombre, 'Skin', @$size['skin'], @$seccion['div_skin']),
                'estado' => !empty($seccion['div_skin']) ? true : false,
            ],
            'top'                   => [
                'div'    => !empty($seccion['div_top_banner']) ? $seccion['div_top_banner'] : null,
                'codigo' => sprintf(ADS_SLOT, $div_nombre, 'TopBanner', @$size['top'], @$seccion['div_top_banner']),
                'estado' => !empty($seccion['div_top_banner']) ? true : false,
            ],
            'footer'                => [
                'div'    => !empty($seccion['div_footer']) ? $seccion['div_footer'] : null,
                'codigo' => sprintf(ADS_SLOT, $div_nombre, 'Footer', @$size['footer'], @$seccion['div_footer']),
                'estado' => !empty($seccion['div_footer']) ? true : false,
            ],
            'right0'                => [
                'div'    => (!empty($seccion['div_right_0']) ? $seccion['div_right_0'] : null),
                'codigo' => sprintf(ADS_SLOT, $div_nombre, 'Right0', @$size['right0'], @$seccion['div_right_0']),
                'estado' => !empty($seccion['div_right_0']) ? true : false,
            ],
            'right1'                => [
                'div'    => (!empty($seccion['div_right_1']) ? $seccion['div_right_1'] : null),
                'codigo' => sprintf(ADS_SLOT, $div_nombre, 'Right1', @$size['right1'], @$seccion['div_right_1']),
                'estado' => !empty($seccion['div_right_1']) ? true : false,
            ],
            'googleIma'             => [
                'div'    => (!empty($seccion['pre_roll']) ? $seccion['pre_roll'] : null),
                'codigo' => null,
                'estado' => ($no_mostrar_googleIma) ? false : true,
            ],
            'movil_middle_banner_1' => [
                'div'    => !empty($seccion['div_middle_banner']) ? $seccion['div_middle_banner'] : null,
                'codigo' => sprintf(ADS_SLOT, $div_nombre, 'MiddleBanner', @$size['movil_middle_banner_1'], @$seccion['div_middle_banner']),
                'estado' => !empty($seccion['div_middle_banner']) ? true : false,
            ],
            'movil_middle_banner_2' => [
                'div'    => !empty($seccion['div_middle_banner1']) ? $seccion['div_middle_banner1'] : null,
                'codigo' => sprintf(ADS_SLOT, $div_nombre, 'MiddleBanner1', @$size['movil_middle_banner_1'], @$seccion['div_middle_banner1']),
                'estado' => !empty($seccion['div_middle_banner1']) ? true : false,
            ],
        ];

        $pub['bidder'] = [
            'div'    => (!empty($seccion['bidder']) ? $this->prebid($pub, $seccion['bidder'], $size) : null),
            'codigo' => null,
            'estado' => true,
        ];

        $pub['bidder_mobil'] = [
            'div'    => (!empty($seccion['bidder_movil']) ? $this->prebid($pub, $seccion['bidder_movil'], $size, true) : null),
            'codigo' => null,
            'estado' => true,
        ];

        $tmp = false;
        if (!empty($pub)) {
            foreach ($pub as $k => $v) {
                if ($v['estado']) {
                    $tmp[$k] = $v;
                }
                unset($v);
            }
        }

        $mostrar = [
            'desktop'    => [
                'skin'      => [],
                'top'       => [],
                'footer'    => [],
                'right0'    => [],
                'right1'    => [],
                'bidder'    => [],
                'googleIma' => [],
            ],
            'movil'      => [
                'top'                   => [],
                'footer'                => [],
                'movil_middle_banner_1' => [],
                'movil_middle_banner_2' => [],
                'bidder_mobil'          => [],
                'googleIma'             => [],
            ],
            'responsive' => [
                'skin'                  => [],
                'top'                   => [],
                'footer'                => [],
                'right0'                => [],
                'right1'                => [],
                'bidder'                => [],
                'bidder_mobil'          => [],
                'googleIma'             => [],
                'movil_middle_banner_1' => [],
                'movil_middle_banner_2' => [],
            ],
        ];

        $out = [];
        foreach ($mostrar as $k => $v) {
            $out[$k] = array_intersect_key($tmp, $mostrar[$k]);
            unset($v);
        }

        return $out;
    }

    public function eplanning($key = '')
    {
        return $this->getCacheApi($key, CACHE_EPLANNING);
    }

    public function getCacheApi($key = '', $prefix = '')
    {
        $key   = sprintf($prefix, $key);
        $cache = $this->cache->get($key);
        $this->_debug($key, $cache, __FUNCTION__);
        return $cache;
    }

    public function getCompacto($w = null, $limit = 5, $offset = 0, $o = null)
    {
        if ($this->debug) {
            echo "\nGet Compacto:: \n";
            print_r($w);
            echo "\nOrder:: \n";
            print_r($o);
        }

        if (!is_array($w)) {return false;}

        $mongodb = $this->mongodb()->{MDB_COMPACTO};
        $option  = [
            'skip'  => $offset,
            'limit' => (!empty($limit)) ? (int) $limit : null,
            'sort'  => is_array($o) ? (array) $o : null,
        ];

        $cursor = $mongodb->find($w, $option)->toArray();
        return $cursor;
    }

    public function getMasleidos($key = 'general')
    {
        return $this->getCacheApi($key, CACHE_MASLEIDAS);
    }

    public function getTagRow($w = [])
    {
        if (!is_array($w)) {return false;}
        $cache_key = serialize($w);
        $cache     = $this->cache->get($cache_key);
        if (empty($cache)) {
            $cache = $this->mongodb()->{MDB_TAGS}->findOne($w);
            if (!empty($cache)) {
                $this->cache->set($cache_key, $cache, $this->cache_segundos);
            }
        }
        $this->_debug($cache_key, $cache, __FUNCTION__);
        return (array) $cache;
    }

    public function getTags($w = null, $limit = 1, $offset = 0)
    {
        if (!is_array($w)) {return false;}
        $mongodb = $this->mongodb()->{MDB_TAGS};
        $option  = [
            'skip'  => $offset,
            'limit' => (!empty($limit)) ? (int) $limit : null,
            'sort'  => is_array($o) ? (array) $o : null,
        ];

        $cursor = $mongodb->find($w, $option)->toArray();

        $cache = $this->mongodb()->{MDB_TAGS}->find($w);

        return $cursor;
    }

    public function limpiarCache($key = '')
    {
        $key = empty($this->cache_key) ? $key : $this->cache_key;
        if (!empty($key)) {
            $this->cache->delete($key);
        }
    }

    public function menuNav($key)
    {
        return $this->getCacheApi($key, CACHE_MENU);
    }

    public function mongodb()
    {
        try {
            $string = "mongodb://" . $this->config['mongodb']['host'] . ":" . $this->config['mongodb']['port'];
            $mongo  = new Client($string, [], [
                'typeMap' => [
                    'array'    => 'array',
                    'document' => 'array',
                    'root'     => 'array',
                ],
            ]);
            $mongo = $mongo->{$this->config['mongodb']['db']};
            return $mongo;
        } catch (MongoDB\Driver\Exception\ConnectionTimeoutException $e) {
            throw new Exception('MongoDB: ' . $e->getMessage());
        }
    }

    public function portada($key = 'home')
    {
        return $this->getCacheApi($key, CACHE_PORTADA);
    }

    public function portadaTags($key = '')
    {
        return $this->getCacheApi($key, CACHE_TAG);
    }

    public function prebid($pub = '', $data = '', $size = '', $formato_mobil = false)
    {
        if (!is_array($pub) && empty($data) && !isset($size['top'])) {return false;}

        $sizeMapping = false;

        if (!empty($size['responsive'])) {
            foreach ($size['responsive'] as $k => $v) {
                $v = array_change_key_case($v, CASE_LOWER);
                if (!empty($v['espacio'])) {
                    $sizeMapping[rtrim($v['espacio'])] = $v;
                }
            }
            $size['size_mapping'] = $sizeMapping;
        }

        $tmp = false;

        $bidder = unserialize(preg_replace('/[\n\r]/', '#', $data));

        $tamanos = [
            0 => "top",
            1 => "right0",
            2 => "right1",
            3 => "footer",
        ];

        $tamanos_mobil = [
            0 => "top",
            1 => "movil_middle_banner_1",
            2 => "movil_middle_banner_2",
            3 => "footer",
        ];

        if ($formato_mobil) {
            $tamanos = $tamanos_mobil;
        }

        foreach ($tamanos as $tk => $tv) {
            $bidder_tamanano = false;
            foreach ($bidder as $k => $v) {
                $params[$k] = false;
                if (!empty($v)) {
                    foreach ($v as $k2 => $v2) {
                        if (!empty($v2)) {
                            $ads             = explode("#", $v2);
                            $ads             = array_values(array_filter($ads));
                            $params[$k][$k2] = !empty($ads[$tk]) ? $ads[$tk] : null;
                        }
                        unset($v2);
                    }
                }

                $bidder_tamanano[] = [
                    'bidder' => $k,
                    'params' => $params[$k],
                ];
                unset($v);
            }

            if (!empty($pub[$tv]['div'])) {
                $tmp[] = [
                    "posicion"    => $tv,
                    "code"        => $pub[$tv]['div'],
                    "sizes"       => (!empty($size[$tv]) ? $size[$tv] : null),
                    "bids"        => $bidder_tamanano,
                    "sizeMapping" => !empty($size['size_mapping'][$tv]) ? $size['size_mapping'][$tv] : [],
                ];
            }
        }

        return $tmp;
    }

    public function seo($key = 'home')
    {
        return $this->getCacheApi($key, CACHE_SEO);
    }

    public function vinculos($key = '')
    {
        return $this->getCacheApi($key, CACHE_VICULOS);
    }

    private function _debug($key = '', $data = false, $function_name = '')
    {
        if ($this->debug) {
            echo "\n{$function_name}:: " . $key . "\n";
            print_r($data);
        }
    }
}