<?php
/**
 * 案例
 * @copyright  Copyright (c) 2014-2015 muxiangdao-com Inc.(http://www.muxiangdao.com)
 * @license    http://www.muxiangdao.com
 * @link       http://www.muxiangdao.com
 * @author     muxiangdao-com Team Prayer (283386295@qq.com)
 */
namespace Toadmin\Controller;
use Think\Page;
class CasesController extends GlobalController {
	public function _initialize() 
	{
        parent::_initialize();
		$this->model = D('Cases');
		$this->classMod = D('CasesClass');
	}	
	//分类
	public function cases_class()
	{
	    $CasesClass = M("CasesClass");	
	    $class_list = $CasesClass->order('cc_sort desc')->select();
		$this->assign('class_list', $class_list);			
		$this->display();		
	}
    //产品类别添加
    public function cases_class_add()
	{
		$CasesClass = M("CasesClass");
		if(IS_POST && $_POST['form_submit'] == 'ok')
		{
			$map=array();
			$map['cc_name']      = str_rp(trim($_POST['cc_name']));
            $map['cc_sort']      = intval($_POST['cc_sort']);
            $return = $CasesClass->add($map);
			if($return)
			{
				$this->success('添加成功！', U('cases_class'));
			}else{
				$this->error('添加失败！');		
			}
		}else{
			$this->display();	
		}
		
    }	

    //产品选择一级类别异步加载二级分类 cases_class_add.html
	public function cases_class_ajax()
	{
		$cc_parent_id = intval($_GET['cc_parent_id']); 
		if($cc_parent_id)
		{
			$CasesClass = M("CasesClass");
		    $cc_list =$CasesClass->where('cc_parent_id='.$cc_parent_id)->select(); 
            if(is_array($cc_list) && !empty($cc_list))
			{
			   foreach($cc_list as $rs)
			   {	
				   $cc_id = $rs['cc_id'];
				   $cc_name = $rs['cc_name'];
				   $cc_v.="<option  value='$cc_id'>&nbsp;&nbsp;$cc_name</option>";	
			   }
			}			
		}
		echo "<select name='cc_parent_id_2' id='cc_parent_id_2'><option value='0'>请选择...</option>".$cc_v."</select>";
	}	

	//异步获取产品下级分类
	public function cases_nc_ajax()
	{
	 	$cc_parent_id = $_GET['cc_parent_id']?intval($_GET['cc_parent_id']):0;
		$tmp_list = getTreeClassList(3); //取分类列表
		if (is_array($tmp_list))
		{
			foreach ($tmp_list as $k => $v)
			{
				if ($v['cc_parent_id'] == $cc_parent_id)
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
	public function cases_class_edit()
	{
		$CasesClass = M("CasesClass");
		if(IS_POST && $_POST['cc_id'])
		{
			$data = array();
			$data['cc_id'] = intval($_POST['cc_id']);
			$data['cc_name'] = str_rp(trim($_POST['cc_name']));
			$data['cc_parent_id'] = intval($_POST['cc_parent_id']);
			$data['cc_title']      = str_rp(trim($_POST['cc_title']));
			$data['cc_key']      = str_rp(trim($_POST['cc_key']));
			$data['cc_desc']      = str_rp(trim($_POST['cc_desc']));			
            $data['cc_sort']      = intval($_POST['cc_sort']);
            //图片上传
            if(!empty($_FILES['cc_img']['name']))
            {
            	$cc_img = 'cc_'.time();
            	$param = array('savePath'=>'casesclass/','subName'=>'','files'=>$_FILES['cc_img'],'saveName'=>$cc_img,'saveExt'=>'');
            	$up_return = upload_one($param);
            	if($up_return == 'error')
            	{
            		$this->error('图片上传失败');
            		exit;
            	}else{
            		$data['cc_img'] = $up_return;
            	}
            }
            if ($CasesClass->where('cc_id='.$data['cc_id'])->save($data)) {
            	$this->success('操作成功！', U('cases_class'));
            }else {
            	$this->error('操作失败！');
            }
		}else{
			$cc_id = intval($_GET['cc_id']);
			$rs = $CasesClass->where('cc_id='.$cc_id)->find();
			$this->assign('rs',$rs);	
			$this->display();	
		}
	}	
	//删除分类信息
	public function cases_class_del()
	{
		$CasesClass = M("CasesClass");
		$cc_id = intval($_GET['cc_id']);	
		if($cc_id)
		{
			$gc = $CasesClass->where('cc_id='.$cc_id)->find();
			if($gc['cc_img'])
			{
				//删除图片
				@unlink(BasePath.'/Uploads/'.$gc['cc_img']);		
			}
			$delnum = $CasesClass->where('cc_id='.$cc_id)->delete(); 
			if($delnum)
			{
				$this->success('操作成功！', U('cases_class'));
			}else{
				$this->error('操作失败！');
			}
		}
	}	
			
	//管理
	public function cases()
	{
		$map = array();
		$case_name = trim($_GET['case_name']);
		$cc_id = intval($_GET['cc_id']);
		if($case_name)$map['case_name'] = array('like','%'.$case_name.'%');
		if($cc_id)
		{
			$map['cc_id'] = array('eq',$cc_id);	
		}
		$totalRows = $this->model->where($map)->count();
		$page = new Page($totalRows,10);	
		$list = $this->model->where($map)->relation('CasesClass')->limit($page->firstRow.','.$page->listRows)->order('case_sort desc,case_time desc')->select();				
		$this->assign('list',$list);
		$this->assign('search',$_GET);	
		$this->assign('page_show',$page->show());
		$class_list = $this->classMod->order('cc_sort desc')->select();		
		$this->assign('class_list', $class_list);	
					
		$this->display();
	}
	//添加
	public function cases_add()
	{
		if(IS_POST){
			$data = array();
			$data['cc_id'] = intval($_POST['cc_id']);
			$data['case_name'] = str_rp(trim($_POST['case_name']));
			$data['case_author'] = str_rp(trim($_POST['case_author']));
			$data['case_sort'] = intval($_POST['case_sort']);
			$data['case_idea'] = str_rp(trim($_POST['case_idea']));
			$data['case_content'] = str_replace('\'','&#39;',$_POST['case_content']);
			$data['case_status'] = intval($_POST['case_status']);
			$data['case_time'] = NOW_TIME;
			
			if($_FILES['case_pic']['size'] > 0)
			{
				$goods_img = 'c_'.$data['case_time'];
				$param = array('savePath'=>'cases/','subName'=>'','files'=>$_FILES['case_pic'],'saveName'=>$goods_img,'saveExt'=>'');
				$up_return = upload_one($param);
				if($up_return == 'error')
				{
					$this->error('图片上传失败');
					exit;
				}else{
					$data['case_pic'] = $up_return;
				}
			}

			$cases_id = $this->model->add($data);
			if($cases_id)
			{										 
			 	$this->success('操作成功', U('cases'));
				exit;		
			}else{
				 $this->error('操作失败');
			}				
		}else{
			/**
			 * 父类列表
			 */
			$class_list = $this->classMod->order('cc_sort desc')->select();
			//规格
			$spec_list = D('Spec')->relation('SpecValue')->where('sp_show=1')->order('sp_sort asc')->select();
			
			//相册
			$ac_list = M('AlbumClass')->order('aclass_sort asc')->select();
			$pc_list = M('AlbumPic')->where('aclass_id=1')->order('upload_time asc')->select();
			
			$this->assign('ac_list', $ac_list);
			$this->assign('pc_list', $pc_list);
			
			$this->assign('class_list', $class_list);
			$this->assign('spec_list', $spec_list);		
			$this->assign('sign_i', count($spec_list));				
			$this->display();	
		}
	}
	//添加
	public function cases_edit()
	{
		$cases_id = intval($_REQUEST['case_id']);
		if(IS_POST){
			$data = array();
			$data['cc_id'] = intval($_POST['cc_id']);
			$data['case_name'] = str_rp(trim($_POST['case_name']));
			$data['case_author'] = str_rp(trim($_POST['case_author']));
			$data['case_sort'] = intval($_POST['case_sort']);
			$data['case_idea'] = str_rp(trim($_POST['case_idea']));
			$data['case_content'] = str_replace('\'','&#39;',$_POST['case_content']);
			$data['case_status'] = intval($_POST['case_status']);
			$data['case_time'] = NOW_TIME;
			
			if(!empty($_FILES['case_pic']['name']))
			{
				$goods_img = 'c_'.$data['case_time'];
				$gd = $this->model->where('case_id='.$cases_id)->find();
				if($gd['case_pic'])
				{
					$old_pic = BasePath.'/Uploads/'.$gd['case_pic'];
					unlink($old_pic);
				}
				$param = array('savePath'=>'cases/','subName'=>'','files'=>$_FILES['case_pic'],'saveName'=>$goods_img,'saveExt'=>'');
				$up_return = upload_one($param);
				if($up_return == 'error')
				{
					$this->error('图片上传失败');
					exit;
				}else{
					$data['case_pic'] = $up_return;
				}
			}
						
			$return = $this->model->where('case_id='.$cases_id)->save($data);
			if($return)
			{										 
			 	$this->success('操作成功', U('cases'));
				exit;		
			}else{
				 $this->error('操作失败');
			}				
		}else{
			$rs = $this->model->where('case_id='.$cases_id)->find();
			$this->assign('rs', $rs);		
			$class_list = $this->classMod->order('cc_sort desc')->select();
			$this->assign('class_list', $class_list);			
			$this->display();	
		}
	}	
	//删除
	public function cases_del()
	{
		if(IS_GET)
		{
			$this->model->where('case_id='.$_GET['case_id'])->delete(); 		
		}
		if(IS_POST)
		{
			$map = array();
			$map['case_id'] = array('in',$_POST['case_id']);
			$this->model->where($map)->delete(); 
		}
		$this->success("操作成功",U('cases'));  	
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
			case 'cc_sort':
			M('CasesClass')->where('cc_id='.$id)->setField($_GET['column'],intval($_GET['value']));
			break;
			case 'cc_name':
			M('CasesClass')->where('cc_id='.$id)->setField($_GET['column'],trim($_GET['value']));
			break;	
			case 'case_sort':
			M('Cases')->where('case_id='.$id)->setField($_GET['column'],intval($_GET['value']));
			break;					
		}
	}
}