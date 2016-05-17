<?php
namespace Home\Controller;
use Think\Page;

class NewsController extends BaseController {
	public function __construct()
	{
		parent::__construct();
		$this->seo_set();
        $this->model = D('Article');
	}  
    public function index()
	{
        $where['article_show'] = 1;
        $order = 'article_sort desc,article_time desc';
        $count = $this->model->where($where)->count();
        $page = new Page($count,3);
        $limit = $page->firstRow.','.$page->listRows;
        $list = $this->model->where($where)->order($order)->limit($limit)->select();
        $this->assign('list',$list);
        $this->assign('page',$page->show());
        $this->display();
    }
	public function detail()
	{
        $id = intval($_GET['id']);
        $where['article_show'] = 1;
        $where['article_id'] = $id;
        $info = $this->model->where($where)->find();
        $this->assign('info',$info);
        $param['title'] = $info['article_title'];
        $param['keywords'] = $info['article_key'];
        $param['description'] = $info['article_desc'];
        $this->assign('seo',seo($param));
        $this->display();
	}
}