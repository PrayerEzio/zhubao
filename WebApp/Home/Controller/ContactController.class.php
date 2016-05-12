<?php
namespace Home\Controller;
class ContactController extends BaseController {
	public function __construct()
	{
		parent::__construct();
		$this->seo_set();
	}  
    public function index()
	{
        $this->display();
    }
}