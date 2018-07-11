<?php
namespace perudesarrollo\AeMotor;

class Masleido
{
    protected $redis;

    private $umbral = 20;

    public function __construct($redis = [])
    {
        $this->redis = $redis;
    }

    public function get() {}

    public function set($nid = 0, $seccion = '', $pubtime = '', $style_e = '')
    {
        $tipo = false;
        if (GALERIA == $style_e) {
            $tipo = 'fotos';
        } else if (VIDEO == $style_e) {
            $tipo = 'videos';
        }

        $score = $this->redis->zscore('general', $nid);
        if (null != $score) {
            //si el nid ya esta guardado en la lista general, solo incremento el valor en 1
            $this->redis->zincrby('general', 1, $nid);
        } else {
            //si el nid nunca ha sido guardado en la lista general, tengo que verificar si llegue al umbral
            $cantidad = $this->redis->zcard('general'); //cantidad en la lista
            if ($cantidad < $this->umbral) {
                //si la cantidad aun no alcanza el umbral solo agrego el nid a la lista general
                $this->redis->zadd('general', 1, $nid);
            } else if ($cantidad >= $this->umbral) {
                //si ya alcance el umbral de la tabla, debo hacer lo siguiente
                //elimino el elemento con menor record de la tabla
                //luego ingreso mi nuevo elemento con score 1
                $this->redis->zremrangebyrank('general', 0, 0);
                $this->redis->zadd('general', 1, $nid);
            }
        }
        //luego debo guardarlo en el general de su minisite
        $score = $this->redis->zscore($seccion . ':general ', $nid);
        if (null != $score) {
            //si el nid ya esta guardado en la lista general del minisite, solo incremento el valor en 1
            $this->redis->zincrby($seccion . ':general', 1, $nid);
        } else {
            //si el nid nunca ha sido guardado en la lista general del ministe, tengo que verificar si llegue al umbral
            $cantidad = $this->redis->zcard($seccion . ':general'); //cantidad en la lista
            if ($cantidad < $this->umbral) {
                //si la cantidad aun no alcanza el umbral solo agrego el nid a la lista general del minisite
                $this->redis->zadd($seccion . ':general', 1, $nid);
            } else if ($cantidad >= $this->umbral) {
                //si ya alcance el umbral de la tabla, debo hacer lo siguiente
                //elimino el elemento con menor record de la lista general del ministe
                //luego ingreso mi nuevo elemento con score 1 en la lista general del ministe
                $this->redis->zremrangebyrank($seccion . ':general', 0, 0);
                $this->redis->zadd($seccion . ':general', 1, $nid);
            }
        }
        if (null != $tipo) {
            //si es fotos o video
            //en fotos o videos del general
            $score = $this->redis->zscore($tipo, $nid);
            if (null != $score) {
                //si el nid ya esta guardado en la lista general, solo incremento el valor en 1
                $this->redis->zincrby($tipo, 1, $nid);
            } else {
                //si el nid nunca ha sido guardado en la lista general, tengo que verificar si llegue al umbral
                $cantidad = $this->redis->zcard($tipo); //cantidad en la lista
                if ($cantidad < $this->umbral) {
                    //si la cantidad aun no alcanza el umbral solo agrego el nid a la lista general
                    $this->redis->zadd($tipo, 1, $nid);
                } else if ($cantidad >= $this->umbral) {
                    //si ya alcance el umbral de la tabla, debo hacer lo siguiente
                    //elimino el elemento con menor record de la tabla
                    //luego ingreso mi nuevo elemento con score 1
                    $this->redis->zremrangebyrank($tipo, 0, 0);
                    $this->redis->zadd($tipo, 1, $nid);
                }
            }
            //en fotos o videos del minisite
            $score = $this->redis->zscore($seccion . ':' . $tipo, $nid);
            if (null != $score) {
                //si el nid ya esta guardado en la lista general del minisite, solo incremento el valor en 1
                $this->redis->zincrby($seccion . ':' . $tipo, 1, $nid);
            } else {
                //si el nid nunca ha sido guardado en la lista general del ministe, tengo que verificar si llegue al umbral
                $cantidad = $this->redis->zcard($seccion . ':' . $tipo); //cantidad en la lista
                if ($cantidad < $this->umbral) {
                    //si la cantidad aun no alcanza el umbral solo agrego el nid a la lista general del minisite
                    $this->redis->zadd($seccion . ':' . $tipo, 1, $nid);
                } else if ($cantidad >= $this->umbral) {
                    //si ya alcance el umbral de la tabla, debo hacer lo siguiente
                    //elimino el elemento con menor record de la lista general del ministe
                    //luego ingreso mi nuevo elemento con score 1 en la lista general del ministe
                    $this->redis->zremrangebyrank($seccion . ':' . $tipo, 0, 0);
                    $this->redis->zadd($seccion . ':' . $tipo, 1, $nid);
                }
            }
        }
    }
};
