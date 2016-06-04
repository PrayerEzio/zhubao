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
		$this->assign('title','臻爱珠宝');
		$this->display();
    }
}