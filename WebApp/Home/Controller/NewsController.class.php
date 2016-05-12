<?php
namespace Home\Controller;
class NewsController extends BaseController {
	public function __construct()
	{
		parent::__construct();
		$this->seo_set();
	}  
    public function index()
	{
        $this->display();
    }
	public function detail()
	{
		$this->display();
	}
}