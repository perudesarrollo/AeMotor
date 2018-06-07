<?php
defined('BASEPATH') or exit('No direct script access allowed');
use perudesarrollo\AeMotor\Core;

class MY_Controller extends CI_Controller
{
    protected $motor;
    public function __construct()
    {
        parent::__construct();
        $this->load->config('motor');
        $config      = $this->config->item('motor');
        $this->motor = new Core($config);
    }

    public function index() {}

}

/* End of file MY_Controller.php */
/* Location: ./application/core/MY_Controller.php */
