<?php
namespace Home\Widget;
use Home\Controller\BaseController;
class NavWidget extends BaseController{
	public function index()
	{
		$this->display('Widget:Nav:index');
	}
}