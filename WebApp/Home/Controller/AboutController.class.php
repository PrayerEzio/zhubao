<?php
namespace Home\Controller;
class AboutController extends BaseController {
	public function __construct()
	{
		parent::__construct();
		$this->seo_set();
	}  
    public function index()
	{
		$this->display();
    }
    
    public function map()
    {
    	$this->display();
    }

}