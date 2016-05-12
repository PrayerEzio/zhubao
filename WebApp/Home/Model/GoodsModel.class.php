<?php
/**
 * 商品模型
 * @copyright  Copyright (c) 2014-2015 muxiangdao-com Inc.(http://www.muxiangdao.com)
 * @license    http://www.muxiangdao.com
 * @link       http://www.muxiangdao.com
 * @author     muxiangdao-com Team Prayer (283386295@qq.com)
 */
namespace Home\Model;
use Think\Model\RelationModel;

class GoodsModel extends RelationModel{
	protected $_link = array(
		'GoodsClass' => array(             
			'mapping_type' => self::BELONGS_TO,         
			'class_name' => 'GoodsClass', 
			'foreign_key' => 'gc_id',			 
		),
	);
}
