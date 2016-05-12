<?php
/**
 * 文章分类模型
 * @copyright  Copyright (c) 2014-2015 muxiangdao-com Inc.(http://www.muxiangdao.com)
 * @license    http://www.muxiangdao.com
 * @link       http://www.muxiangdao.com
 * @author     muxiangdao-com Team Prayer (283386295@qq.com)
 */
namespace Home\Model;
use Think\Model\RelationModel;

class ArticleClassModel extends RelationModel{
	protected $_link = array(
		'Article' => array(    
				'mapping_type'  => self::HAS_MANY,    
				'class_name'    => 'Article',    
				'foreign_key'   => 'ac_id',
				'mapping_name'  => 'articles',    
				'mapping_order' => 'article_sort desc,article_time desc',
				'mapping_limit' => '8',
		),
	);
}
