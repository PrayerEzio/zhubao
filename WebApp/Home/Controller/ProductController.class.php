<?php
namespace Home\Controller;
use Think\Page;

class ProductController extends BaseController {
	public function __construct()
	{
		parent::__construct();
		$this->seo_set();
        $this->model = D('Goods');
        $this->classModel = D('GoodsClass');
        $this->tagModel = M('GoodsTag');
	}  
    public function index()
	{
        $where['goods_status'] = 1;
        if (intval($_GET['gc_id']))
        {
            $where['gc_id'] = intval($_GET['gc_id']);
        }
        if (intval($_GET['tag']))
        {
            $where['tag'] = array('like','%|'.intval($_GET['tag']).'|%');
        }
        if (trim($_GET['price']))
        {
            $price = explode('-',trim($_GET['price']));
            foreach ($price as $key => $item)
            {
                switch ($item)
                {
                    case 'min':
                        $price[$key] = 0;
                        break;
                    case 'max':
                        $price[$key] = 99999999;
                        break;
                    default:
                        $price[$key] = floatval($item);
                }
            }
            if ($price[0] < $price[1])
            {
                $min_price = floatval($price[0]);
                $max_price = floatval($price[1]);
            }
            else
            {
                $min_price = floatval($price[1]);
                $max_price = floatval($price[0]);
            }
            $where['goods_price'] = array('between',array($min_price,$max_price));
        }
        $order = 'goods_sort desc,add_time desc';
        $count = $this->model->where($where)->count();
        $page = new Page($count,9);
        $limit = $page->firstRow.','.$page->listRows;
        $list = $this->model->where($where)->order($order)->limit($limit)->select();
        $goods_class_list = $this->classModel->where(array('gc_parent_id'=>0))->order('gc_sort desc')->select();
        $goods_tag_list = $this->tagModel->where(array('tag_status'=>1))->order('tag_sort desc')->select();
        $this->assign('list',$list);
        $this->assign('goods_class_list',$goods_class_list);
        $this->assign('goods_tag_list',$goods_tag_list);
        $this->assign('page',$page->show());
        $this->assign('search',$_GET);
        $this->display();
    }
	public function detail()
	{
        $id = intval($_GET['id']);
        $where['goods_status'] = 1;
        $where['goods_id'] = $id;
        $info = $this->model->where($where)->find();
        $info['goods_pic_more'] = unserialize($info['goods_pic_more']);
        $this->assign('info',$info);
        $param['title'] = $info['goods_name'];
        $param['keywords'] = $info['goods_key'];
        $param['description'] = $info['goods_desc'];
        $this->assign('seo',seo($param));
		$this->display();
	}
}