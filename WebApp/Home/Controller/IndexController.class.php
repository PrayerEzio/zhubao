<?php
namespace Home\Controller;
class IndexController extends BaseController {
	public function __construct()
	{
		parent::__construct();
		$this->seo_set();
	}  
    public function index()
	{
        $order = 'ap_sort desc';
        $this->banner = M('AdvPosition')->where(array('ap_code'=>'banner'))->order($order)->select();
        $this->display();
    }
}