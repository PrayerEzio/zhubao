<?php
/**
 * 商铺
 * @copyright  Copyright (c) 2014-2030 muxiangdao-cn Inc.(http://www.muxiangdao.cn)
 * @license    http://www.muxiangdao.cn
 * @link       http://www.muxiangdao.cn
 * @author	   muxiangdao-cn Team
 */
namespace Toadmin\Controller;
use Think\Page;
class StoreController extends GlobalController {
	public function _initialize() 
	{
        parent::_initialize();
        $this->model = D('Store');
	}	
	public function index(){
		if (IS_POST) {
			//删除处理
			if (is_array($_POST['del_id']) && !empty($_POST['del_id']))
			{
				foreach ($_POST['del_id'] as $article_id)
				{
					//删除图片
					$article_pic = $this->art->where('article_id='.$article_id)->getField('article_pic');
					if($article_pic)
					{
						@unlink(BasePath.'/Uploads/'.$article_pic);						
					}
					$this->art->where('article_id='.$article_id)->delete(); 
				}
				$this->success("操作成功",U('article'));
				exit;
			}else {
				$this->error("请选择要操作的对象"); 	
			}
		}elseif (IS_GET){
			$where = array();
			$count = $this->model->where($where)->count();
			$page = new Page($count,10);
			$list = $this->model->where($where)->limit($page->firstRow.','.$page->listRows)->order('store_sort desc')->select();
			$this->assign('list',$list);
			$this->assign('show_page',$page->show());
			$this->display();
		}
	}
	public function curdStore(){
		if (IS_POST) {
			$id = intval($_POST['id']);
			$data['store_name'] = str_rp(trim($_POST['store_name']));
			$data['store_keyword'] = str_rp(trim($_POST['store_keyword']));
			$data['province_id'] = intval($_POST['province_id']);
			$data['city_id'] = intval($_POST['city_id']);
			$data['area_id'] = intval($_POST['area_id']);
			$data['store_content'] = str_replace('\'','&#39;',$_POST['store_content']);;
			$data['store_sort'] = intval($_POST['store_sort']);
			$data['store_status'] = intval($_POST['store_status']);
			if ($id) {
				$res = $this->model->where(array('store_id'=>$id))->save($data);
			}else {
				$res = $this->model->add($data);
			}
			if ($res) {
				$this->success('操作成功',U('index'));
			}else {
				$this->error('操作失败');
			}
		}elseif (IS_GET){
			$where['store_id'] = intval($_GET['id']);
			$info = $this->model->where($where)->find();
			$this->assign('info',$info);
			$this->display();
		}
	}
}