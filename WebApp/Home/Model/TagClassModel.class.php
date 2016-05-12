<?php
/**
 * 标签分类模型
 * @copyright  Copyright (c) 2014-2015 muxiangdao-com Inc.(http://www.muxiangdao.com)
 * @license    http://www.muxiangdao.com
 * @link       http://www.muxiangdao.com
 * @author     muxiangdao-com Team Prayer (283386295@qq.com)
 */
namespace Home\Model;
use Think\Model\RelationModel;

class TagClassModel extends RelationModel{
	protected $_link = array(
		'GoodsTag' => array(             
			'mapping_type' => self::HAS_MANY,         
			'class_name' => 'GoodsTag', 
			'foreign_key' => 'tc_id',			 
			'mapping_order' => 'tag_sort desc,tag_id',
		),
	);
}
