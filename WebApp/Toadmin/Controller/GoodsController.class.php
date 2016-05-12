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
class GoodsController extends GlobalController {
	public function _initialize() 
	{
        parent::_initialize();
		$this->model = D('Goods');
		$this->tagMod = D('GoodsTag');
		$this->tagClassMod = D('TagClass');
	}	
	//分类
	public function goods_class()
	{
	    $GoodsClass = M("GoodsClass");	
		$parent_id = $_GET['gc_parent_id']?intval($_GET['gc_parent_id']):0;
        //$gc_list =  $GoodsClass->where('gc_parent_id=0')->order('gc_sort asc')->select();		
		$tmp_list = getTreeClassList(3);
		if (is_array($tmp_list)){
			foreach ($tmp_list as $k => $v){
				if ($v['gc_parent_id'] == $parent_id){
					/**
					 * 判断是否有子类
					 */
					if ($tmp_list[$k+1]['deep'] > $v['deep']){
						$v['have_child'] = 1;
					}
					$class_list[] = $v;
				}
			}
		}	
		$this->assign('class_list', $class_list);			
		$this->display();		
	}
    //产品类别添加
    public function goods_class_add()
	{
		$GoodsClass = M("GoodsClass");
		if(IS_POST && $_POST['form_submit'] == 'ok')
		{
			$map=array();
			$map['gc_name']      = str_rp(trim($_POST['gc_name']));
			$map['gc_parent_id'] = intval($_POST['gc_parent_id']);
			$map['gc_title']      = str_rp(trim($_POST['gc_title']));
			$map['gc_key']      = str_rp(trim($_POST['gc_key']));
			$map['gc_desc']      = str_rp(trim($_POST['gc_desc']));
            $map['gc_sort']      = intval($_POST['gc_sort']);
            $map['show_index']	 = intval($_POST['show_index']);
            if (is_array($_POST['tc_id'])) {
            	$tc_ids = '';
            	foreach ($_POST['tc_id'] as $key => $val){
            		if ($key != 0) {
            			$tc_ids .= ','.$val;
            		}else {
            			$tc_ids = $val;
            		}
            	}
            	$map['tc_id'] = $tc_ids;
            }
            $return = $GoodsClass->add($map);
			if($return)
			{
				if(!empty($_FILES['gc_img']['name']))
	            {
	            	$gc_img = 'gc_'.time();
	            	$param = array('savePath'=>'goodsclass/','subName'=>'','files'=>$_FILES['gc_img'],'saveName'=>$gc_img,'saveExt'=>'');
	            	$up_return = upload_one($param);
	            	if($up_return == 'error')
	            	{
	            		$this->error('图片上传失败');
	            		exit;
	            	}else{
	            		$data['gc_img'] = $up_return;
	            		$GoodsClass->where('gc_id='.$return)->save($data);
	            	}
	            }
				$this->success('添加成功！', U('goods_class'));
			}else{
				$this->error('添加失败！');		
			}
		}else{
			/**
			 * 父类列表，只取到第二级
			 */
			$class_list = getTreeClassList(1);
			if (is_array($class_list)){
				foreach ($class_list as $k => $v){
					$class_list[$k]['gc_name'] = str_repeat("&nbsp;",$v['deep']*2).'├ '.$v['gc_name'];
				}
			}	
			$this->gc_parent_id = intval($_GET['gc_parent_id']);
			//所需筛选
			$tag_class= M('TagClass')->order('tc_sort desc')->select();
			$this->assign('tag_class',$tag_class);
			$this->assign('class_list', $class_list);		
			$this->display();	
		}
		
    }	

    //产品选择一级类别异步加载二级分类 goods_class_add.html
	public function goods_class_ajax()
	{
		$gc_parent_id = intval($_GET['gc_parent_id']); 
		if($gc_parent_id)
		{
			$GoodsClass = M("GoodsClass");
		    $gc_list =$GoodsClass->where('gc_parent_id='.$gc_parent_id)->select(); 
            if(is_array($gc_list) && !empty($gc_list))
			{
			   foreach($gc_list as $rs)
			   {	
				   $gc_id = $rs['gc_id'];
				   $gc_name = $rs['gc_name'];
				   $gc_v.="<option  value='$gc_id'>&nbsp;&nbsp;$gc_name</option>";	
			   }
			}			
		}
		echo "<select name='gc_parent_id_2' id='gc_parent_id_2'><option value='0'>请选择...</option>".$gc_v."</select>";
	}	

	//异步获取产品下级分类
	public function goods_nc_ajax()
	{
	 	$gc_parent_id = $_GET['gc_parent_id']?intval($_GET['gc_parent_id']):0;
		$tmp_list = getTreeClassList(3); //取分类列表
		if (is_array($tmp_list))
		{
			foreach ($tmp_list as $k => $v)
			{
				if ($v['gc_parent_id'] == $gc_parent_id)
				{
					/**
					 * 判断是否有子类
					 */
					if ($tmp_list[$k+1]['deep'] > $v['deep'])
					{
						$v['have_child'] = 1;
					}
					$class_list[] = $v;
				}
			}
		}	
		$output = json_encode($class_list);
		print_r($output);
		exit;			
	}
	
	//编辑分类
	public function goods_class_edit()
	{
		$GoodsClass = M("GoodsClass");
		if(IS_POST && $_POST['gc_id'])
		{
			$data = array();
			$data['gc_id'] = intval($_POST['gc_id']);
			$data['gc_name'] = str_rp(trim($_POST['gc_name']));
			$data['gc_parent_id'] = intval($_POST['gc_parent_id']);
			$data['gc_title']      = str_rp(trim($_POST['gc_title']));
			$data['gc_key']      = str_rp(trim($_POST['gc_key']));
			$data['gc_desc']      = str_rp(trim($_POST['gc_desc']));			
            $data['gc_sort']      = intval($_POST['gc_sort']);
            $data['show_index']	 = intval($_POST['show_index']);
            if (is_array($_POST['tc_id'])) {
            	$tc_ids = '';
            	foreach ($_POST['tc_id'] as $key => $val){
            		if ($key != 0) {
            			$tc_ids .= ','.$val;
            		}else {
            			$tc_ids = $val;
            		}
            	}
            	$data['tc_id'] = $tc_ids;
            }
            //图片上传
            if(!empty($_FILES['gc_img']['name']))
            {
            	$gc_img = 'gc_'.time();
            	$param = array('savePath'=>'goodsclass/','subName'=>'','files'=>$_FILES['gc_img'],'saveName'=>$gc_img,'saveExt'=>'');
            	$up_return = upload_one($param);
            	if($up_return == 'error')
            	{
            		$this->error('图片上传失败');
            		exit;
            	}else{
            		$data['gc_img'] = $up_return;
            	}
            }
            if ($GoodsClass->where('gc_id='.$data['gc_id'])->save($data)) {
            	$this->success('操作成功！', U('goods_class'));
            }else {
            	$this->error('操作失败！');
            }
		}else{
			$gc_id = intval($_GET['gc_id']);
			$rs = $GoodsClass->where('gc_id='.$gc_id)->find();
				/**
				 * 父类列表，只取到第二级
				 */
				$class_list = getTreeClassList(1);
				if (is_array($class_list)){
					foreach ($class_list as $k => $v){
						$class_list[$k]['gc_name'] = str_repeat("&nbsp;",$v['deep']*2).'├ '.$v['gc_name'];
					}
				}		
			$this->assign('class_list', $class_list);	
			//所需筛选
			$tag_class= M('TagClass')->order('tc_sort desc')->select();
			$this->assign('tag_class',$tag_class);
			$this->assign('rs',$rs);	
			$this->display();	
		}
	}	
	//删除分类信息
	public function goods_class_del()
	{
		$GoodsClass = M("GoodsClass");
		$gc_id = intval($_GET['gc_id']);	
		if($gc_id)
		{
			$gc = $GoodsClass->where('gc_id='.$gc_id)->find();
			if($gc['gc_img'])
			{
				//删除图片
				@unlink(BasePath.'/Uploads/'.$gc['gc_img']);		
			}
			$delnum = $GoodsClass->where('gc_id='.$gc_id)->delete(); 
			if($delnum)
			{
				$this->success('操作成功！', U('goods_class'));
			}else{
				$this->error('操作失败！');
			}
		}
	}	
			
	public function goods_tag()
	{
		$list = $this->tagMod->order('tag_sort desc')->select();
		$this->assign('list',$list);
		$this->display();
	}
	public function delTag()
	{
		$id = intval($_GET['del']);
		$tag_pic = $this->tagMod->where(array('tag_id'=>$id))->getField('tag_pic');
		if(!empty($tag_pic))
		{
			//删除图片
			@unlink(BasePath.'/Uploads/'.$tag_pic);
		}
		$this->tagMod->where(array('tag_id'=>$id))->delete();
		$this->success('删除成功');
	}
	public function curdTag()
	{
		if (IS_POST){
			$id = intval($_POST['id']);
			$data['tag_name'] = str_rp(trim($_POST['tag_name']));
			$data['tag_sort'] = intval($_POST['tag_sort']);
			$data['tag_status'] = intval($_POST['tag_status']);
			$data['tc_id'] = intval($_POST['tc_id']);
			if ($_FILES['tag_pic']['size'] > 0) 
			{
				if ($id) {
					$tag_pic = $this->tagMod->where(array('tag_id'=>$id))->getField('tag_pic');
					if(!empty($tag_pic))
					{
						//删除图片
						@unlink(BasePath.'/Uploads/'.$tag_pic);
					};
				}
				$tag_img = 'tag_'.NOW_TIME;
				$param = array('savePath'=>'goods/','subName'=>'','files'=>$_FILES['tag_pic'],'saveName'=>$tag_img,'saveExt'=>'');				
				$up_return = upload_one($param);
				if($up_return == 'error')
				{
					$this->error('图片上传失败');
					exit;	
				}else{
					$data['tag_pic'] = $up_return;	
				}					
			}
			if ($id) {
				$res = $this->tagMod->where(array('tag_id'=>$id))->save($data);
			}else {
				$res = $this->tagMod->add($data);
			}
			if ($res) {
				$this->success('操作成功',U('goods_tag'));
			}else {
				$this->error('操作失败');
			}
		}elseif (IS_GET) {
			$id = intval($_GET['id']);
			$info = $this->tagMod->where(array('tag_id'=>$id))->find();
			$tag_class = $this->tagClassMod->order('tc_sort desc')->select();
			$this->assign('tag_class',$tag_class);
			$this->assign('info',$info);
			$this->display();
		}
	}
	//管理
	public function goods()
	{
		$map = array();
		$goods_name = trim($_GET['goods_name']);
		$gc_id = intval($_GET['gc_id']);
		if($goods_name)$map['goods_name'] = array('like','%'.$goods_name.'%');
		if($gc_id)
		{
			$all_next_gc_id='';
			$in_arr = get_all_gc_id($gc_id); //该分类下的所有分类
			$all_next_gc_id='';
			$map['gc_id'] = array('in',$in_arr);	
		}
		$totalRows = $this->model->where($map)->count();
		$page = new Page($totalRows,10);	
		$list = $this->model->where($map)->relation('GoodsClass')->limit($page->firstRow.','.$page->listRows)->order('add_time desc')->select();				
		$this->assign('list',$list);
		$this->assign('search',$_GET);	
		$this->assign('page_show',$page->show());
		/**
		 * 父类列表，只取到第二级
		 */
		$class_list = getTreeClassList(3);
		if (is_array($class_list)){
			foreach ($class_list as $k => $v){
				$class_list[$k]['gc_name'] = str_repeat("&nbsp;",$v['deep']*2).'├ '.$v['gc_name'];
			}
		}			
		$this->assign('class_list', $class_list);	
					
		$this->display();
	}
	public function goods_add()
	{
		if(IS_POST){
			$data = array();
			$data['gc_id'] = intval($_POST['gc_id']);
			$data['goods_name'] = str_rp(trim($_POST['goods_name']));
			$data['goods_key'] = str_rp(trim($_POST['goods_key']));
			$data['goods_desc'] = str_rp(trim($_POST['goods_desc']));
			$data['goods_url'] = str_rp(trim($_POST['goods_url']));
			$data['goods_storage'] = intval($_POST['goods_storage']);
			$data['goods_serial'] = str_rp(trim($_POST['goods_serial']));
			$data['goods_price'] = price_format(trim($_POST['goods_price']));
			$data['goods_sort'] = intval($_POST['goods_sort']);
			$data['goods_body'] = str_replace('\'','&#39;',$_POST['goods_body']);
			$data['add_time'] = NOW_TIME;
			if (!empty($_POST['tag']) && is_array($_POST['tag'])) {
				$tag = '|';
				foreach ($_POST['tag'] as $key => $val){
					$tag .= $val.'|';
				}
				$data['tag'] = $tag;
			}else{
				$data['tag'] = '';
			}
			$data['goods_status'] = intval($_POST['goods_status']);
			$data['show_index'] = intval($_POST['show_index']);
			$data['goods_profile'] = serialize(array_filter($_POST['more_address']));
				
			//图片上传
			if(!empty($_FILES['goods_pic']['name']))
			{
				$goods_img = 'g_'.$data['add_time'];
				$param = array('savePath'=>'goods/','subName'=>'','files'=>$_FILES['goods_pic'],'saveName'=>$goods_img,'saveExt'=>'');
				$up_return = upload_one($param);
				if($up_return == 'error')
				{
					$this->error('图片上传失败');
					exit;
				}else{
					$data['goods_pic'] = $up_return;
				}
			}
			$goods_id = $this->model->add($data);
			if($goods_id)
			{
				//规格处理
				$spec_val = $_POST['s_value'];
				if(is_array($spec_val) && !empty($spec_val))
				{
					foreach ($spec_val as $k=>$val)
					{
						$val['sort']	= intval($val['sort']);
						$val['name']	= trim($val['name']);
						$val['price']	= trim($val['price']);
						if($val['name'] && $val['price'])
						{
							/**
							 * 新增规格值
							 */
							$val_add	= array();
							$val_add['goods_id'] = $goods_id;
							$val_add['spec_name'] = trim($val['name']);
							$val_add['spec_goods_price'] = price_format(trim($val['price']));
							$val_add['spec_goods_sort']	= intval($val['sort']);
							$return = M('GoodsSpec')->add($val_add);
							unset($val_add);
						}
					}
					//更新商品列表默认规格信息
					$df_spec = M('GoodsSpec')->where('goods_id='.$goods_id)->order('spec_goods_price asc')->find();
					if(is_array($df_spec) && !empty($df_spec))
					{
						$spec_data = array();
						$spec_data['spec_id'] = $df_spec['spec_id'];
						$spec_data['spec_name'] = $df_spec['spec_name'];
						$spec_data['goods_price'] = $df_spec['spec_goods_price'];
						$this->model->where('goods_id='.$goods_id)->save($spec_data);
					}
				}
	
				//商品多图片处理
				$GoodsPic = M('GoodsPic');
				$pic_val = $_POST['s_pic'];
				if(is_array($pic_val) && !empty($pic_val))
				{
					$pic_data = array();
					$n=1;
					foreach ($pic_val as $p=>$pv)
					{
						$pic_data['p_sort']	= intval($pv['sort']);
						$pic_data['goods_id'] = $goods_id;
						$pic_name = 's_pic_'.$p;
						if($_FILES[$pic_name]['size'] > 0)
						{
							$goods_img = 'g_'.$goods_id.'_'.$n.'_'.NOW_TIME;
							$param = array('savePath'=>'goods/','subName'=>'','files'=>$_FILES[$pic_name],'saveName'=>$goods_img,'saveExt'=>'');
							$up_return = upload_one($param);
							if($up_return == 'error')
							{
								$this->error('图片上传失败');
								exit;
							}else{
								$pic_data['pic'] = $up_return;
							}
							$GoodsPic->add($pic_data);
						}
						$n++;
					}
				}
					
				$this->success('操作成功', U('goods'));
				exit;
			}else{
				$this->error('操作失败');
			}
		}else{
			/**
			 * 父类列表
			 */
			$class_list = getTreeClassList(3);
			if (is_array($class_list)){
				foreach ($class_list as $k => $v){
					$class_list[$k]['gc_name'] = str_repeat("&nbsp;",$v['deep']*2).'├ '.$v['gc_name'];
				}
			}
			//规格
			$spec_list = D('Spec')->relation('SpecValue')->where('sp_show=1')->order('sp_sort asc')->select();
				
			//相册
			$ac_list = M('AlbumClass')->order('aclass_sort asc')->select();
			$pc_list = M('AlbumPic')->where('aclass_id=1')->order('upload_time asc')->select();
				
			//常用城市
			$this->city_list = D('District')->where('usetype=1')->order('d_sort desc')->select();
				
			$this->assign('ac_list', $ac_list);
			$this->assign('pc_list', $pc_list);
			$tag = $this->tagClassMod->relation(true)->order('tc_sort desc')->select();
			$this->assign('tag_list',$tag);
			$this->assign('class_list', $class_list);
			$this->assign('spec_list', $spec_list);
			$this->assign('sign_i', count($spec_list));
			$this->assign('pic_list_i', 0);
			$this->display('goods_edit');
		}
	}
	public function goods_edit()
	{
		$goods_id = intval($_REQUEST['goods_id']);
		if(IS_POST){
			$data = array();
			$data['gc_id'] = intval($_POST['gc_id']);
			$data['goods_name'] = str_rp(trim($_POST['goods_name']));
			$data['goods_key'] = str_rp(trim($_POST['goods_key']));
			$data['goods_desc'] = str_rp(trim($_POST['goods_desc']));
			$data['goods_url'] = str_rp(trim($_POST['goods_url']));
			$data['goods_storage'] = intval($_POST['goods_storage']);
			$data['goods_serial'] = str_rp(trim($_POST['goods_serial']));
			$data['goods_price'] = price_format(trim($_POST['goods_price']));
			$data['goods_sort'] = intval($_POST['goods_sort']);
			$data['goods_body'] = str_replace('\'','&#39;',$_POST['goods_body']);
			$data['add_time'] = NOW_TIME;
			if (!empty($_POST['tag']) && is_array($_POST['tag'])) {
				$tag = '|';
				foreach ($_POST['tag'] as $key => $val){
					$tag .= $val.'|';
				}
				$data['tag'] = $tag;
			}else{
				$data['tag'] = '';
			}
			$data['goods_status'] = intval($_POST['goods_status']);
			$data['show_index'] = intval($_POST['show_index']);
			$data['goods_profile'] = serialize(array_filter($_POST['more_address']));
				
			//图片上传
			if(!empty($_FILES['goods_pic']['name']))
			{
				$goods_img = 'g_'.$data['add_time'];
				$gd = $this->model->where('goods_id='.$goods_id)->find();
				if($gd['goods_pic'])
				{
					$old_pic = BasePath.'/Uploads/'.$gd['goods_pic'];
					unlink($old_pic);
				}
				$param = array('savePath'=>'goods/','subName'=>'','files'=>$_FILES['goods_pic'],'saveName'=>$goods_img,'saveExt'=>'');
				$up_return = upload_one($param);
				if($up_return == 'error')
				{
					$this->error('图片上传失败');
					exit;
				}else{
					$data['goods_pic'] = $up_return;
				}
			}
	
			$return = $this->model->where('goods_id='.$goods_id)->save($data);
			if($return)
			{
				//规格处理
				$GoodsSpec = M('GoodsSpec');
				$spec_val = $_POST['s_value'];
				if(is_array($spec_val) && !empty($spec_val))
				{
					$GoodsSpec->where('goods_id='.$goods_id)->delete($data); // 删除原来的规格
					foreach ($spec_val as $k=>$val)
					{
						$val['sort']	= intval($val['sort']);
						$val['name']	= trim($val['name']);
						$val['price']	= trim($val['price']);
						if($val['name'] && $val['price'])
						{
							/**
							 * 新增规格值
							 */
							$val_add	= array();
							$val_add['goods_id'] = $goods_id;
							$val_add['spec_name'] = trim($val['name']);
							$val_add['spec_goods_price'] = price_format(trim($val['price']));
							$val_add['spec_goods_sort']	= intval($val['sort']);
							$return = $GoodsSpec->add($val_add);
							unset($val_add);
						}
					}
					//更新商品列表默认规格信息
					$df_spec = M('GoodsSpec')->where('goods_id='.$goods_id)->order('spec_goods_price asc')->find();
					if(is_array($df_spec) && !empty($df_spec))
					{
						$spec_data = array();
						$spec_data['spec_id'] = $df_spec['spec_id'];
						$spec_data['spec_name'] = $df_spec['spec_name'];
						$spec_data['goods_price'] = $df_spec['spec_goods_price'];
						$this->model->where('goods_id='.$goods_id)->save($spec_data);
					}
				}else{
					$spec_data = array();
					$spec_data['spec_id'] = 0;
					$spec_data['spec_name'] = '';
					$this->model->where('goods_id='.$goods_id)->save($spec_data);
					$GoodsSpec->where('goods_id='.$goods_id)->delete($data); // 删除原来的规格
				}
	
				//商品多图片处理
				$GoodsPic = M('GoodsPic');
				$pic_val = $_POST['s_pic'];
				if(is_array($pic_val) && !empty($pic_val))
				{
					$pic_data = array();
					$n=1;
					foreach ($pic_val as $p=>$pv)
					{
						$pic_data['p_sort']	= intval($pv['sort']);
						$pic_data['goods_id'] = $goods_id;
						$pic_name = 's_pic_'.$p;
						if($_FILES[$pic_name]['size'] > 0)
						{
							$goods_img = 'g_'.$goods_id.'_'.$n.'_'.NOW_TIME;
							$param = array('savePath'=>'goods/','subName'=>'','files'=>$_FILES[$pic_name],'saveName'=>$goods_img,'saveExt'=>'');
							$up_return = upload_one($param);
							if($up_return == 'error')
							{
								$this->error('图片上传失败');
								exit;
							}else{
								$pic_data['pic'] = $up_return;
							}
							$GoodsPic->add($pic_data);
						}
						$n++;
					}
				}
				//图片处理END
					
				$this->success('操作成功', U('goods'));
				exit;
			}else{
				$this->error('操作失败');
			}
		}else{
			/**
			 * 父类列表
			 */
			$class_list = getTreeClassList(3);
			if (is_array($class_list)){
				foreach ($class_list as $k => $v){
					$class_list[$k]['gc_name'] = str_repeat("&nbsp;",$v['deep']*2).'├ '.$v['gc_name'];
				}
			}
				
			$rs = $this->model->where('goods_id='.$goods_id)->find();
			if (!empty($rs['tag'])) {
				$rs['tag'] = substr($rs['tag'], 1,-1);
				$rs['tag'] = explode('|', $rs['tag']);
			}
			$rs['goods_profile'] = unserialize($rs['goods_profile']);
			$rs['numAdd'] = count($rs['goods_profile']);
			$rs['goods_pic_more'] = unserialize($rs['goods_pic_more']);
			$rs['numMorepic'] = count($rs['goods_pic_more']);
			$this->assign('rs', $rs);
			$tag = $this->tagClassMod->relation(true)->order('tc_sort desc')->select();
			$this->assign('tag_list',$tag);
			$this->assign('rs', $rs);
				
			//规格
			$spec_list = M('GoodsSpec')->where('goods_id='.$goods_id)->order('spec_goods_sort asc')->select();
			//多图片
			$pic_list = M('GoodsPic')->where('goods_id='.$goods_id)->order('p_sort asc')->select();
	
			//常用城市
			$this->city_list = D('District')->where('usetype=1')->order('d_sort desc')->select();
	
			//相册
			$ac_list = M('AlbumClass')->order('aclass_sort asc')->select();
			$pc_list = M('AlbumPic')->where('aclass_id=1')->order('upload_time asc')->select();
			$this->assign('ac_list', $ac_list);
			$this->assign('pc_list', $pc_list);
			$this->assign('spec_list', $spec_list);
			$this->assign('spec_list_i', count($spec_list)+1);
	
			$this->assign('pic_list', $pic_list);
			$this->assign('pic_list_i', count($pic_list)+1);
				
			$this->assign('class_list', $class_list);
			$this->display();
		}
	}
	//删除
	public function goods_del()
	{
		if(IS_GET)
		{
			$gd = $this->model->where('goods_id='.$_GET['goods_id'])->find();
			if($gd['goods_pic'])
			{
				$old_pic = BasePath.'/Uploads/'.$gd['goods_pic'];
				@unlink($old_pic);
			}
			$list = M('GoodsPic')->where(array('goods_id'=>$_GET['goods_id']))->select();
			foreach ($list as $key => $val){
				$old_pic = BasePath.'/Uploads/'.$val['pic'];
				@unlink($old_pic);
			}
			$this->model->where('goods_id='.$_GET['goods_id'])->delete();
			M('GoodsPic')->where(array('goods_id'=>$_GET['goods_id']))->delete();
		}
		if(IS_POST)
		{
			$map = array();
			$map['goods_id'] = array('in',$_POST['goods_id']);
			$this->model->where($map)->delete(); 
		}
		$this->success("操作成功",U('goods'));  	
		exit;			
	}	
	//异步获取图片
	public function get_album_list()
	{
		$sign = intval($_GET['sign']);
		$aclass_id = intval($_GET['aclass_id']);
		if($aclass_id)
		{
			//$AlbumClass = M('AlbumClass');
			$AlbumPic = M('AlbumPic');
			$pic_list=$AlbumPic->where('aclass_id='.$aclass_id)->order('upload_time asc')->select();
			if(is_array($pic_list) && !empty($pic_list))
			{
				foreach($pic_list as $rs)
				{
					$apic_cover = C('SiteUrl').'/Uploads/'.$rs['apic_cover'];
					if($sign==1)
					{
						$pic_list_str.='<li onclick="insert_img_editor(\''.$apic_cover.'\');"><a href="JavaScript:void(0);"><span class="thumb size90"><img src="'.$apic_cover.'" title="点击插入"></span></a></li>';
					}else{
						$pic_list_str.='<li onclick="insert_img(\''.$apic_cover.'\');"><a href="JavaScript:void(0);"><span class="thumb size90"><img src="'.$apic_cover.'" title="点击插入"></span></a></li>';
					}
				}	
				 header("Cache-Control: no-cache");
				 echo $pic_list_str;
			}							
		}else{
			echo'';	
		}				
	}	
	//在线编辑	
	public function ajax()
	{
		$id = intval($_GET['id']);
		switch(trim($_GET['branch']))
		{
			case 'gc_sort':
			M('GoodsClass')->where('gc_id='.$id)->setField($_GET['column'],intval($_GET['value']));
			break;
			case 'gc_name':
			M('GoodsClass')->where('gc_id='.$id)->setField($_GET['column'],trim($_GET['value']));
			break;	
			case 'goods_sort':
			M('Goods')->where('goods_id='.$id)->setField($_GET['column'],intval($_GET['value']));
			break;
			case 'tag_sort':
			M('GoodsTag')->where('tag_id='.$id)->setField($_GET['column'],intval($_GET['value']));
			break;
			case 'tag_name':
			M('GoodsTag')->where('tag_id='.$id)->setField($_GET['column'],trim($_GET['value']));
			break;
		}
	}
}