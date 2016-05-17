<?php
namespace Home\Controller;
class AboutController extends BaseController {
	public function __construct()
	{
		parent::__construct();
		$this->seo_set();
        $this->model = M('Document');
	}  
    public function index()
	{
        $where['doc_code'] = CONTROLLER_NAME.'/'.ACTION_NAME;
        $info = $this->model->where($where)->find();
        $this->assign('info',$info);
        $param['title'] = $info['doc_title'];
        $param['keywords'] = $info['doc_key'];
        $param['description'] = $info['doc_desc'];
        $this->assign('seo',seo($param));
        $this->display();
    }
}