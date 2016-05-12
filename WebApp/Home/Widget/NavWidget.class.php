<?php
namespace Home\Widget;
use Home\Controller\BaseController;
class NavWidget extends BaseController{
	public function index()
	{
		$nav['knowledge'] = M('Document')->where(array('doc_code'=>array('LIKE','Knowledge%')))->order('doc_sort desc')->select();
		$nav['article'] = M('Document')->where(array('doc_code'=>array('LIKE','Article%')))->order('doc_sort desc')->select();
		$nav['news'] = D('ArticleClass')->order('ac_sort desc')->select();
		$nav['product'] = M('GoodsClass')->order('gc_sort desc')->select();
		$nav['product'] = unlimitedForLayer($nav['product'],'child','gc_parent_id','gc_id');
		$nav['tag'] = M('GoodsTag')->where(array('tag_status'=>1))->order('tag_sort desc')->select();
		$this->assign('nav',$nav);
		$this->display('Widget:Nav:index');
	}
}