<?php if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Welcome extends MY_Controller
{

    /**
     * Index Page for this controller.
     *
     * Maps to the following URL
     *         http://example.com/index.php/welcome
     *    - or -
     *         http://example.com/index.php/welcome/index
     *    - or -
     * Since this controller is set as the default controller in
     * config/routes.php, it's displayed at http://example.com/
     *
     * So any other public methods not prefixed with an underscore will
     * map to /index.php/welcome/<method_name>
     * @see http://codeigniter.com/user_guide/general/urls.html
     */
    public function index()
    {
        $this->masleido->set(46162, 'actualidad', 123456, 3);
        $redis = $this->motor->redis();
        $redis->set('foo', 'bar');
        $value = $redis->keys('*');
        // $this->motor->debug = true;
        // print_r($value);
        // die();
        $cursor = $this->motor->getCompacto(["_id" => 314281]);
        // print_r($cursor);
        $data['demo'] = [
            1, 2, 3, 4, 5, 6, 7, 8,
        ];
        echo $this->motor->twig->render('welcome_message.html', $data);
        // $this->load->view('welcome_message', $data);
    }
}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */
