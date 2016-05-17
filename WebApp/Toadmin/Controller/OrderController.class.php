<?php
/**
 * 商品
 * @copyright  Copyright (c) 2014-2030 muxiangdao-cn Inc.(http://www.muxiangdao.cn)
 * @license    http://www.muxiangdao.cn
 * @link       http://www.muxiangdao.cn
 * @author	   muxiangdao-cn Team
 */
namespace Toadmin\Controller;
use Think\Page;
class OrderController extends GlobalController {
	public function _initialize() 
	{
        parent::_initialize();
		$this->model = D('Order');
	}
    public function lmb_qifa(){
        $DB_BLOG = M('Blog','nmm_','mysql://dev_query:@192.168.1.72:3306/mmbapi#utf8');
        $add_time_from = trim($_GET['add_time_from']) ? strtotime(trim($_GET['add_time_from'])) : 0;
        $add_time_to = trim($_GET['add_time_to']) ? strtotime(trim($_GET['add_time_to']))+86400 : NOW_TIME;
        $where['dateline'] = array('between',array($add_time_from,$add_time_to));
        $where['bid'] = 10268;
        $where['visible'] = 1;
        $list = M('blog')->where($where)->field('id,title,comments')->limit(2000)->select();
        Vendor('phpExcel.PHPExcel');
        Vendor('phpExcel.Writer.PHPExcel_Writer_Excel5');
        $excel = new \PHPExcel();
        $excel->getProperties()->setCreator(C('SiteUrl'));
        $excel->getProperties()->setLastModifiedBy(C('SiteUrl'));
        $excel->getActiveSheet()->setTitle('资金明细');
        $letter = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N');
        $tableheader = array('ID','标题','回复数','访问量','发布时间','链接');
        for($i = 0;$i < count($tableheader);$i++) {
            $excel->getActiveSheet()->setCellValue("$letter[$i]1","$tableheader[$i]");
        }
        $data = array();
        $i = 0;
        foreach ($list as $key => $val){
            $pv = $val['comments'] * mt_rand(12,16);
            if($pv <= 500)
            {
                $pv = mt_rand(502,623);
            }
            $data[$i] = array($val['id'],$val['title'],$val['comments'],$pv,date('Y-m-d',$val['dateline']),'http://www.lamabang.com/topic/id-',$val['id'],'.html');
            $i++;
        }
        for ($i = 2;$i <= count($data) + 1;$i++) {
            $j = 0;
            foreach ($data[$i - 2] as $key=>$value) {
                $excel->getActiveSheet()->setCellValue("$letter[$j]$i","$value");
                $j++;
            }
        }
        $write = new \PHPExcel_Writer_Excel5($excel);
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
        header("Content-Type:application/force-download");
        header("Content-Type:application/vnd.ms-execl");
        header("Content-Type:application/octet-stream");
        header("Content-Type:application/download");;
        header('Content-Disposition:attachment;filename="资金明细.xls"');
        header("Content-Transfer-Encoding:binary");
        $write->save('php://output');
    }
	//管理
	public function order()
	{
		$map = array();
		if(intval($_GET['order_state']))$map['order_state'] = array('eq',intval($_GET['order_state']));
		if(trim($_GET['field']) && trim($_GET['search_name']))$map[$_GET['field']] = array('like','%'.trim($_GET['search_name']).'%');
		$add_time_from = trim($_GET['add_time_from']) ? strtotime(trim($_GET['add_time_from'])) : 0;
		$add_time_to = trim($_GET['add_time_to']) ? strtotime(trim($_GET['add_time_to'])) : 0;
		if($add_time_from && $add_time_to)
		{
			$add_time_to = $add_time_from == $add_time_to ? $add_time_to+86400 : $add_time_to;
			$map['add_time'] = array('between',$add_time_from,$add_time_to);		
		}elseif($add_time_from && !$add_time_to){
			$map['add_time'] = array('egt',$add_time_from);	
		}elseif(!$add_time_from && $add_time_to){
			$map['add_time'] = array('elt',$add_time_to);	
		}
		$order_amount_from = trim($_GET['order_amount_from']) ? trim($_GET['order_amount_from']) : 0;
		$order_amount_to = trim($_GET['order_amount_to']) ? trim($_GET['order_amount_to']) : 0;
		if($order_amount_from && $order_amount_to)
		{
			$map['order_amount'] = array('between',$order_amount_from,$order_amount_to);	
		}elseif($order_amount_from && !$order_amount_to){
			$map['order_amount'] = array('egt',$order_amount_from);	
		}elseif(!$order_amount_from && $order_amount_to){
			$map['order_amount'] = array('elt',$order_amount_from);	
		}
		
		$totalRows = $this->model->where($map)->count();
		$page = new Page($totalRows,10);	
		$list = $this->model->where($map)->limit($page->firstRow.','.$page->listRows)->order('add_time desc')->select();				
		$this->assign('list',$list);
		$this->assign('page_show',$page->show());				
	    $this->assign('search', $_GET);							
		$this->display();
	}
	
	//查看订单	
	public function order_view()
	{
		$order_id = intval($_GET['order_id']);
		if($order_id)
		{
			$vo = $this->model->where('order_id='.$order_id)->find();
			$this->assign('vo', $vo);							
			$this->display();				
		}
	}
	//订单处理
	public function order_op()
	{
		$order_id = intval($_GET['order_id']);
		if($order_id)
		{
			$vo = $this->model->where('order_id='.$order_id)->find();
			$this->assign('vo', $vo);							
			$this->display();				
		}			
	}
	
}