<?php
namespace perudesarrollo\AeMotor;

class Core
{
    public $cache;

    public $cache_key;

    public $debug;

    public $twig;

    public $limpiar_cache;

    protected $config;

    public function __construct($cnf = [])
    {
        if (!is_array($cnf)) {
            throw new Exception('Core Motor Library: empty config to array');
        }
        $twig_views   = isset($cnf['twig']['views']) ? $cnf['twig']['views'] : null;
        $this->config = $cnf;
        $this->cache  = new Memcache($this->config['cache']);

        $loader     = new \Twig_Loader_Filesystem($cnf['twig']['views']);
        $this->twig = new \Twig_Environment($loader, $cnf['twig']['env']);
        $this->twig->addExtension(new \Twig_Extension_Optimizer(\Twig_NodeVisitor_Optimizer::OPTIMIZE_FOR));
        $this->twig->addExtension(new \Twig_Extension_Debug());
        $this->twig->addExtension(new \Twig_Extensions_Extension_Date());
        $this->twig->addExtension(new Ayuda());
        $this->twig->addExtension(new AyudaAmp());
    }

    public function ads($key = 'home', $seccion = 'home', $no_mostrar_googleIma = false, $no_mostrar_publicidad = false)
    {
        if ($no_mostrar_publicidad) {return false;}
        $seccion = (array) $this->eplanning($key . '.' . $seccion);
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

    public function dataLayer($seo = '', $data = [])
    {
        $seo = (array) $seo;
        if (!isset($seo['titulo'])) {return false;}
        $out = [
            "window"    => [
                "title"       => $seo['titulo'],
                "url"         => @$data['uri_string'],
                "description" => isset($seo['meta_description']) ? $seo['meta_description'] : null,
                "keywords"    => isset($seo['meta_keywords']) ? $seo['meta_keywords'] : null,
                "pub_time"    => date("Y-m-d H:i:s"),
            ],
            "referrers" => [
                "referrer_url"    => isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : null,
                "primaryReferrer" => null,
                "subReferrer"     => null,
                "browser"         => @$data['browser'],
                "device"          => @$data['device'],
            ],
            "actions"   => [],
            "user"      => [],
            "category"  => [
                "primaryCategory" => 'Deportes',
                "subCategory1"    => isset($seo['nombre_categoria']) ? $seo['nombre_categoria'] : 'Home',
                "subCategory1_id" => isset($seo['id_categoria']) ? (int) $seo['id_categoria'] : '0',
            ],
        ];
        $json = @json_encode($out);
        return $json;
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

    public function getCompacto($w = null, $limit = 5, $offset = 0, $o = null, $coleccion = MDB_COMPACTO)
    {
        if ($this->debug) {
            echo "\nGet Compacto:: \n";
            print_r($w);
            echo "\nOrder:: \n";
            print_r($o);
        }

        if (!is_array($w)) {return false;}

        $mongodb = $this->mongodb()->{$coleccion};
        $option  = [
            'skip'  => $offset,
            'limit' => (!empty($limit)) ? (int) $limit : null,
            'sort'  => is_array($o) ? (array) $o : null,
        ];

        $cursor = $mongodb->find($w, $option)->toArray();
        return $cursor;
    }

    public function getCompactoNid($nid = 0, $tiempo = 3600, $coleccion = MDB_COMPACTO)
    {
        $cache_key = $nid;
        $cache     = $this->cache->get($cache_key);
        if (empty($cache)) {
            $cache = $this->mongodb()->{$coleccion}->findOne(["_id" => $nid]);
            if (!empty($cache)) {
                $this->cache->set($cache_key, $cache, $tiempo);
            }
        }
        $this->_debug($cache_key, $cache, __FUNCTION__);
        return (array) $cache;
    }

    public function getMasleidos($key = 'general')
    {
        return $this->getCacheApi($key, CACHE_MASLEIDAS);
    }

    public function getTagRow($w = [], $tiempo = 3600, $coleccion = MDB_TAGS)
    {
        if (!is_array($w)) {return false;}
        $cache_key = serialize($w);
        $cache     = $this->cache->get($cache_key);
        if (empty($cache)) {
            $cache = $this->mongodb()->{$coleccion}->findOne($w);
            if (!empty($cache)) {
                $this->cache->set($cache_key, $cache, $tiempo);
            }
        }
        $this->_debug($cache_key, $cache, __FUNCTION__);
        return (array) $cache;
    }

    public function getTags($w = null, $limit = 1, $offset = 0, $o = null, $coleccion = MDB_TAGS)
    {
        if (!is_array($w)) {return false;}
        $mongodb = $this->mongodb()->{$coleccion};
        $option  = [
            'skip'  => $offset,
            'limit' => (!empty($limit)) ? (int) $limit : null,
            'sort'  => is_array($o) ? (array) $o : null,
        ];

        $cursor = $mongodb->find($w, $option)->toArray();

        $cache = $this->mongodb()->{MDB_TAGS}->find($w);

        return $cursor;
    }

    public function ldjson($nota = [], $relacionadas = [])
    {
        if (!is_array($nota)) {return false;}
        $image = [];
        $list  = [];

        if (!empty($relacionadas)) {
            foreach ($relacionadas as $k => $v) {
                $v = (array) $v;
                $v = [
                    "@type"                => "ImageObject",
                    "description"          => trim($v['titulo_alt']),
                    "height"               => 418,
                    "representativeOfPage" => true,
                    "url"                  => CDN_ELEMENTOS . sprintf($v['img']['path'], '696x418'),
                    "width"                => 696,
                ];

                $v2 = [
                    "@type"    => "ListItem",
                    "position" => $k + 1,
                    "url"      => base_url($v['url']),
                ];
                $list[]  = $v2;
                $image[] = $v;

                unset($v);
            }
        }

        $keywords = [];
        foreach ($nota['tags'] as $k => $v) {
            $v          = (array) $v;
            $keywords[] = $v['name'];
            unset($v);
        }

        $out = [
            "@context"         => "http://schema.org",
            "@type"            => "NewsArticle",
            "articleBody"      => trim(strip_tags($nota['contenido'])),
            "author"           => [
                "@type" => "Person",
                "name"  => "América Noticias",
            ],
            "dateModified"     => date('Y-m-d\TH:i:sP', $nota['pubtime']),
            "datePublished"    => date('Y-m-d\TH:i:sP', $nota['pubtime']),
            "description"      => trim($nota['bajada_alt']),
            "headline"         => trim($nota['titulo_alt']),
            "image"            => $image,
            "keywords"         => $keywords,
            "mainEntityOfPage" => [
                "@id"   => base_url($nota['url']),
                "@type" => "WebPage",
            ],
            "publisher"        => [
                "@type" => "Organization",
                "logo"  => [
                    "@type"  => "ImageObject",
                    "height" => 60,
                    "url"    => URL_ESTATICOS . 'f/assets/deportes/img/default.jpg?v2',
                    "width"  => 316,
                ],
                "name"  => "América Noticias",
            ],
            "video"            => [],
        ];

        $out2 = [
            "@context"        => "https://schema.org",
            "@type"           => "ItemList",
            "itemListElement" => $list,
        ];

        return ['nota' => json_encode($out), 'list' => json_encode($out2)];
    }

    public function limpiarCache($key = '')
    {
        $key = empty($this->cache_key) ? $key : $this->cache_key;
        if ($this->limpiar_cache) {
            $this->cache->delete($key);
        }
    }

    public function menuNav($key)
    {
        return $this->getCacheApi($key, CACHE_MENU);
    }

    public function minisitio($key = '')
    {
        return $this->getCacheApi($key, CACHE_MINISITIO);
    }

    public function mongodb()
    {
        try {
            $string = "mongodb://" . $this->config['mongodb']['host'] . ":" . $this->config['mongodb']['port'];
            $mongo  = new \MongoDB\Client($string, [], [
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

    public function redis()
    {
        try {
            \Predis\Autoloader::register();
            $redis = new \Predis\Client([
                'scheme' => 'tcp',
                'host'   => $this->config['redis']['host'],
                'port'   => $this->config['redis']['port'],
            ], [
                'prefix' => $this->config['redis']['prefix'],
            ]);

            return $redis;
        } catch (\Predis\Network\ConnectionException $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function seo($key = 'home')
    {
        return $this->getCacheApi($key, CACHE_SEO);
    }

    public function struct($key = '')
    {
        return $this->getCacheApi($key, CACHE_STRUCT);
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
