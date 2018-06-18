<?php
namespace perudesarrollo\AeMotor;

class Ayuda extends \Twig_Extension
{
    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter('raw_urldecode', [$this, 'raw_urldecode']),
            new \Twig_SimpleFilter('iniciales_titulares', [$this, 'iniciales_titulares']),
            new \Twig_SimpleFilter('time_ago', [$this, 'time_ago']),
        ];
    }

    public function raw_urldecode($string)
    {
        return html_entity_decode(rawurldecode($string));
    }

    public function iniciales_titulares($string)
    {
        if (empty($string)) {return false;}
        list($a, $b) = explode(' ', $string);
        return strtoupper(substr($a, 0, 1) . substr($b, 0, 1));
    }

    public function time_ago($datefrom, $dateto = -1, $movil = false)
    {
        if (0 == $datefrom) {$datefrom = time() - 20;}
        if (-1 == $dateto) {$dateto = time();}
        // Calcular la diferencia en segundos de dos timestamps
        $difference = $dateto - $datefrom;
        // Si la diferencia es menor que 60 segundos
        if ($difference < 60) {$interval = "s";}
        // Si la diferencia esta entre 60 segundos y 60 minutos
        elseif ($difference >= 60 && $difference < 60 * 60) {$interval = "n";}
        // Si la diferencia está entre 1 hora y 24 horas
        elseif ($difference >= 60 * 60 && $difference < 60 * 60 * 24) {$interval = "h";}
        // Si la diferencia está entre 1 dias
        elseif ($difference >= 60 * 60 * 24) {$interval = "d";}
        // Basado en el intervalo mostrar el mensaje
        switch ($interval) {
            case "d":
                $res = date('d.m.Y', $datefrom);
                break;
            case "h":
                $datediff = floor($difference / 60 / 60);
                $res      = (1 == $datediff) ? "Hace $datediff hora" : "Hace $datediff horas";
                if ($movil) {
                    $res = (1 == $datediff) ? "Hace $datediff" . "h" : "Hace $datediff" . "h";
                }
                break;
            case "n":
                $datediff = floor($difference / 60);
                $res      = (1 == $datediff) ? "Hace $datediff minuto" : "Hace $datediff minutos";
                if ($movil) {
                    $res = (1 == $datediff) ? "Hace $datediff" . "m" : "Hace $datediff" . "m";
                }
                break;
            case "s":
                $datediff = $difference;
                if ($datediff < 0) {
                    $datediff = 1;
                }

                $res = (1 == $datediff) ? "Hace $datediff segundo" : "Hace $datediff segundos";
                if ($movil) {
                    $res = (1 == $datediff) ? "Hace $datediff" . "s" : "Hace $datediff" . "s";
                }
                break;
        }
        return $res;
    }
}
