<?php
namespace perudesarrollo\AeMotor;

class AyudaAmp extends \Twig_Extension
{
    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter('amp_clear_contenido', [$this, 'amp_clear_body']),
        ];
    }

    /**
     * Function amp_clear
     * @param string
     * @return string
     */
    public function amp_clear($p)
    {
        do {
            $p = preg_replace('/(<[^>]+) style=".*?"/i', '$1', $p);
            $p = str_replace('<p>', '', $p);
            $p = str_replace('</p>', '', $p);
            $p = str_replace('<span>', '', $p);
            $p = str_replace('</span>', '', $p);
        } while (stristr($p, 'style='));

        return $p;
    }

    /**
     * Function clear_body_amp
     * @param string
     * @return string
     */
    public function amp_clear_body($body)
    {
        //$body = preg_replace('/(<[^>]+) style=".*?"/i', '$1', $body);
        do {
            $body = preg_replace('/(<[^>]+) style=".*?"/i', '$1', $body);
            //var_dump($body);
        } while (stristr($body, 'style='));

        return $this->amp_format($body);
    }

    public function amp_format($body)
    {
        //$body = str_replace('</p>', '', $body);
        //$body = str_replace('</div>', '', $body);
        //$split = preg_split("/<div>|<p>/", $body);
        $split = preg_split("/<div>/", $body);

        $html = "";

        foreach ($split as $key => $p) {
            $twitter   = $this->amp_format_twitter($p);
            $facebook  = $this->amp_format_facebook($p);
            $youtube   = $this->amp_format_youtube($p);
            $instagram = $this->amp_format_instagram($p);
            $storify   = $this->amp_format_storify($p);

            if ($twitter) {
                $html .= '<p>' . $twitter . '</p>';
            } elseif ($facebook) {
                $html .= '<p>' . $facebook . '</p>';
            } elseif ($youtube) {
                $html .= '<p>' . $youtube . '</p>';
            } elseif ($instagram) {
                $html .= '<p>' . $instagram . '</p>';
            } elseif ($storify) {
                $html .= '<p>' . $storify . '</p>';
            } else {
                $html .= '<p>' . $p . '</p>';
            }
        }

        $split = preg_split("/<p>/", $html);

        $html = "";
        foreach ($split as $key => $p) {
            //print_r($p);

            $image  = $this->amp_format_image($p);
            $iframe = $this->amp_format_iframe($p);

            if ($image) {
                $html .= '<p>' . $image . '</p>';
            } elseif ($iframe) {
                $html .= '<p>' . $iframe . '</p>';
            } else {
                $html .= '<p>' . $p . '</p>';
            }
        }

        return $html;
    }

    public function amp_format_facebook($p)
    {
        preg_match('/(facebook.com\/plugins\/(video|post).php\?href\=http)([^&]+)/', $p, $matches);

        if (count($matches) > 0) {
            $video = "";
            if ('video' == $matches[2]) {
                $video = 'data-embed-as="video"';
            }

            $facebook = urldecode(end($matches));
            return <<<EOF
			<amp-facebook width=480 height=320
				$video
			    data-href="http$facebook"
			    layout="responsive"
			    >
			</amp-facebook>
EOF;
        } else {
            return false;
        }
    }

    public function amp_format_iframe($p)
    {

        preg_match('/<iframe.*src=\"(.*)\".*><\/iframe>/isU', $p, $matches);

        if (end($matches)) {
            $url = end($matches);

            $temp = strpos($p, "http");
            if ($temp) {
                $url = str_replace("http://", "https://", $url);
            } else {
                $temp = strpos($p, "//");
                if ($temp) {
                    $url = "https:" . $url;
                } else {
                    $url = "https://" . $url;
                }
            }

            return <<<EOF
				<amp-iframe width="580" height="348"
				    sandbox="allow-scripts allow-same-origin"
				    layout="responsive"
				    frameborder="0"
				    src="$url">
				</amp-iframe>
EOF;
        } else {
            return false;
        }
    }

    public function amp_format_image($p)
    {

        preg_match('/<\s*img[^\>]*src\s*=\s*[\""\']?([^\""\'\s>]*)/', $p, $matches);

        if (end($matches)) {
            $image = end($matches);
            return <<<EOF
				<amp-img
						width="580"
						height="348"
						layout="responsive"
						src="$image"
						>
				</amp-img>
EOF;
        } else {
            return false;
        }
    }

    public function amp_format_instagram($p)
    {
        preg_match("/(https?:\/\/www\.)?instagram\.com\/p\/([a-zA-Z0-9_-]+)/", $p, $matches);

        if (count($matches) > 0) {
            $instagram = end($matches);

            return <<<EOF
			<amp-instagram
			    data-shortcode="$instagram"
			    width="400"
			    height="400"
			    layout="responsive"
			    >
			</amp-instagram>
EOF;
        } else {
            return false;
        }
    }

    public function amp_format_storify($p)
    {
        preg_match("#://storify\.com/(\d+|[A-Za-z0-9\.]+)/([A-Za-z0-9\.]+)\-([a-z0-9]+)#", $p, $matches);

        if (count($matches) > 0) {
            //$iframe = $matches[0];
            return "<br>";
            /*
        return <<<EOF
        <amp-iframe width=300 height=300
        sandbox="allow-scripts allow-same-origin"
        layout="responsive"
        frameborder="0"
        src="https$iframe">
        </amp-iframe>
        EOF;
         */
        } else {
            return false;
        }
    }

    public function amp_format_twitter($p)
    {
        preg_match("#https?://twitter\.com/(?:\#!/)?(\w+)/status(es)?/(\d+)#is", $p, $matches);

        if (end($matches)) {
            $twitter = end($matches);
            return <<<EOF
			<amp-twitter width=486 height=657
			    data-tweetid="$twitter"
			    layout="responsive"
			    >
			</amp-twitter>
EOF;
        } else {
            return false;
        }
    }

    public function amp_format_youtube($p)
    {
        preg_match_all('#((?:www\.)?(?:youtube\.com\/(?:watch\?v=|embed\/|v\/)|youtu\.be\/|youtube\-nocookie\.com\/embed\/)([a-zA-Z0-9-]*))#i', $p, $matches);
        if (count(end($matches)) > 0) {
            $youtube = end($matches);
            $youtube = $youtube[0];
            return <<<EOF
			<amp-youtube
			    data-videoid="$youtube"
			    width="480" height="320"
			    layout="responsive"
			     >
    		</amp-youtube>
EOF;
        } else {
            return false;
        }
    }
}
