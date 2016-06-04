<?php
/**
 * 公共函数
 * @package    config
 * @copyright  Copyright (c) 2014-2030 muxiangdao-cn Inc.(http://www.muxiangdao.cn)
 * @license    http://www.muxiangdao.cn
 * @link       http://www.muxiangdao.cn
 * @author	   muxiangdao-cn Team
 */

/**
 * 打印输出
 * @param $arr
 * @param string $Charset
 */
function p($arr,$Charset = 'utf-8'){
	if (!empty($Charset)) {
		header("Content-Type: text/html; charset=$Charset");
	}
    dump($arr,1,'',0);
}
/**
 * 验证验证码是否正确
 * @param string $code 用户验证码
 * @param string $id 验证码标识     
 * @return bool 用户验证码是否正确
 */
function check_verify($code,$id)
{
    $verify = new \Think\Verify();    
	return $verify->check($code, $id);
}
/**
 * 对象转化为数组
 * @param object $obj 对象
 * @return array 数组
 */
function object_to_array($obj){
	$_arr = is_object($obj) ? get_object_vars($obj) :$obj;
	foreach ($_arr as $key=>$val){
		$val = (is_array($val) || is_object($val)) ? object_to_array($val):$val;
		$arr[$key] = $val;
	}
	return $arr;
}
/**
 * 系统非常规MD5加密方法
 * @param  string $str 要加密的字符串
 * @return string 
 */
function re_md5($str)
{
	$key = C('WKY_KEY');
	return '' === $str ? '' : md5(sha1($str).$key);
}
/**
 * 格式化价格
 * @param  string $price 
 * @return string 
 */
function price_format($price) 
{	
	if($price)
	{
		$price_format = number_format($price,2,'.','');
		return $price_format;
	}else{
		return '0.00';	
	}
}
//订单号生成
function order_sn() 
{
	$order_sn = 'MXD'.date('Ymd').substr( implode(NULL,array_map('ord',str_split(substr(uniqid(),7,13),1))) , -8 , 8);
	return $order_sn;
}
/**
 * 取上一步来源地址
 * @param
 * @return string 字符串类型的返回结果
 */
function getReferer()
{
	return empty($_SERVER['HTTP_REFERER'])?'':$_SERVER['HTTP_REFERER'];
}
/**
 * 获取用户登录标识
 * @param string
 * @return string
 */
function get_login_sign($str)
{
	switch($str)
	{
		case 'member_id':
		  return session('member_id') ? session('member_id') : '';
		  break;
		case 'nickname':
		  return session('nickname') ? session('nickname') : '';
		  break;
		default:
		  return '';
	}	
}
/**
 * 加密函数
 * @param string $txt 需要加密的字符串
 * @param string $key 密钥
 * @return string 返回加密结果
 */
function encrypt($txt)
{
	if(empty($txt))
	{
		return $txt;
		exit;
	}
    $key = C('WKY_KEY');
	
	$chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
	$ikey ="-x6g6ZWm2G9g_vr0Bo.pOq3kRIxsZ6rm";
	$nh1 = rand(0,61);
	$nh2 = rand(0,61);
	$nh3 = rand(0,61);
	$ch1 = $chars{$nh1};
	$ch2 = $chars{$nh2};
	$ch3 = $chars{$nh3};
	$nhnum = $nh1 + $nh2 + $nh3;
	$knum = 0;$i = 0;
	while(isset($key{$i})) $knum +=ord($key{$i++});
	$mdKey = substr(md5(md5(md5($key.$ch1).$ch2.$ikey).$ch3),$nhnum%8,$knum%8 + 16);
	$txt = base64_encode($txt);
	$txt = str_replace(array('+','/','='),array('-','_','.'),$txt);
	$tmp = '';
	$j=0;$k = 0;
	$tlen = strlen($txt);
	$klen = strlen($mdKey);
	for ($i=0; $i<$tlen; $i++) {
		$k = $k == $klen ? 0 : $k;
		$j = ($nhnum+strpos($chars,$txt{$i})+ord($mdKey{$k++}))%61;
		$tmp .= $chars{$j};
	}
	$tmplen = strlen($tmp);
	$tmp = substr_replace($tmp,$ch3,$nh2 % ++$tmplen,0);
	$tmp = substr_replace($tmp,$ch2,$nh1 % ++$tmplen,0);
	$tmp = substr_replace($tmp,$ch1,$knum % ++$tmplen,0);
	return $tmp;
}
/**
 * 解密函数
 * @param string $txt 需要解密的字符串
 * @param string $key 密匙
 * @return string 字符串类型的返回结果
 */
function decrypt($txt)
{
	if(empty($txt))
	{
		return $txt;
		exit;
	}
    $key = C('WKY_KEY');
	
	$chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
	$ikey ="-x6g6ZWm2G9g_vr0Bo.pOq3kRIxsZ6rm";
	$knum = 0;$i = 0;
	$tlen = strlen($txt);
	while(isset($key{$i})) $knum +=ord($key{$i++});
	$ch1 = $txt{$knum % $tlen};
	$nh1 = strpos($chars,$ch1);
	$txt = substr_replace($txt,'',$knum % $tlen--,1);
	$ch2 = $txt{$nh1 % $tlen};
	$nh2 = strpos($chars,$ch2);
	$txt = substr_replace($txt,'',$nh1 % $tlen--,1);
	$ch3 = $txt{$nh2 % $tlen};
	$nh3 = strpos($chars,$ch3);
	$txt = substr_replace($txt,'',$nh2 % $tlen--,1);
	$nhnum = $nh1 + $nh2 + $nh3;
	$mdKey = substr(md5(md5(md5($key.$ch1).$ch2.$ikey).$ch3),$nhnum % 8,$knum % 8 + 16);
	$tmp = '';
	$j=0; $k = 0;
	$tlen = strlen($txt);
	$klen = strlen($mdKey);
	for ($i=0; $i<$tlen; $i++) {
		$k = $k == $klen ? 0 : $k;
		$j = strpos($chars,$txt{$i})-$nhnum - ord($mdKey{$k++});
		while ($j<0) $j+=61;
		$tmp .= $chars{$j};
	}
	$tmp = str_replace(array('-','_','.'),array('+','/','='),$tmp);
	return trim(base64_decode($tmp));
}

/**
 * 过滤特殊字符
 * @param string $f_str 要过滤的信息
 * @return string 返回过滤后的结果
 */
function str_rp($f_str)
{
	$f_str = preg_replace("/and/i","&#97;nd",$f_str);
	$f_str = preg_replace("/execute/i","&#101;xecute",$f_str);
	$f_str = preg_replace("/update/i","&#117;pdate",$f_str);
	$f_str = preg_replace("/count/i","&#99;ount",$f_str);
	$f_str = preg_replace("/chr/i","&#99;hr",$f_str);
	$f_str = preg_replace("/mid/i","&#109;id",$f_str);
	$f_str = preg_replace("/master/i","&#109;aster",$f_str);
	$f_str = preg_replace("/truncate/i","&#116;runcate",$f_str);
	$f_str = preg_replace("/char/i","&#99;har",$f_str);
	$f_str = preg_replace("/declare/i","&#100;eclare",$f_str);
	$f_str = preg_replace("/select/i","&#115;elect",$f_str);
	$f_str = preg_replace("/create/i","&#99;reate",$f_str);
	$f_str = preg_replace("/delete/i","&#100;elete",$f_str);
	$f_str = preg_replace("/insert/i","&#105;nsert",$f_str);
	$f_str = stripcslashes($f_str);		//防止单引号双号引被转义
	$f_str = str_replace('<','&lt;',$f_str);
	$f_str = str_replace(">",'&gt;',$f_str);
	$f_str = str_replace('\'','&#39;',$f_str);
	$f_str = str_replace('"','&quot;',$f_str);
	$f_str = str_replace('　','　',$f_str);
   return $f_str;
}
/**
 * 通过URL获取页面信息
 * @param string $url 地址
 * @return string 返回页面信息
 */
function get_url($url) 
{
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL,$url);  //设置访问的url地址   
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);	
	curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);//不输出内容
	$result =  curl_exec($ch);
	curl_close ($ch);
	return $result; 
}
/**
 * 模拟POST提交
 * @param string $url 地址
 * @param string $data 提交的数据
 * @return string 返回结果
 */
function post_url($url, $data) 
{
    $curl = curl_init(); // 启动一个CURL会话
    curl_setopt($curl, CURLOPT_URL, $url); // 要访问的地址
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE); // 对认证证书来源的检查
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE); // 从证书中检查SSL加密算法是否存在
    curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)'); // 模拟用户使用的浏览器
    //curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1); // 使用自动跳转
    //curl_setopt($curl, CURLOPT_AUTOREFERER, 1);    // 自动设置Referer
    curl_setopt($curl, CURLOPT_POST, 1);             // 发送一个常规的Post请求
    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);   // Post提交的数据包x
    curl_setopt($curl, CURLOPT_TIMEOUT, 30);         // 设置超时限制 防止死循环
    curl_setopt($curl, CURLOPT_HEADER, 0);           // 显示返回的Header区域内容
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);   // 获取的信息以文件流的形式返回

    $tmpInfo = curl_exec($curl); // 执行操作
    if(curl_errno($curl)) 
	{
       echo 'Errno'.curl_error($curl);//捕抓异常
    }
    curl_close($curl); // 关闭CURL会话
    return $tmpInfo; // 返回数据
}
/**
 * 获取IP地址信息
 * @param string $ip IP
 * @return string 
 */
function get_ip_info($ip)
{
	if($ip)
	{
		$url = 'http://ip.taobao.com/service/getIpInfo.php?ip='.$ip; //使用淘宝IP库		
		$info_arr = get_url($url);
		$list=json_decode($info_arr,true);
		if($list['code'] == 0)
		{
			//$return = $list['data']['region'].' '.$list['data']['city'];
			$return = $list['data']['city'];
			return $return;
		}
	}
}
//获取系统设置
function MSC($name,$table='setting',$field='value'){
	$value = S($name);
	if (false == $value) {
		$where = array('name'=>$name);
		$value = M($table)->where($where)->getField($field);
	}
	return $value;
}
//获取微信设置
function Wx_C($name,$wx_id=1){
	$value = S($name);
	if (false == $value) {
		$where = array('wx_id'=>$wx_id);
		$value = M('WxSetting')->where($where)->getField($name);
	}
	return $value;
}
//微信获取基础支持的access_token
function get_wx_AccessToken($wx_id=1)
{	
	//S('wx_access_token',null);
	$wx_access_token = S('wx_access_token');
	if(false == $wx_access_token)
	{
		$wx_data = M('WxSetting')->where(array('wx_id'=>$wx_id))->find();
		$wx_appid = $wx_data['wx_appid'];
		$wx_secret = $wx_data['wx_secret'];
		$url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.$wx_appid.'&secret='.$wx_secret;
		$output = get_url($url);
		$jsoninfo = json_decode($output, true);
		$wx_access_token = $jsoninfo['access_token'];
		S('wx_access_token',$wx_access_token,7000); //官默认有效期为7200秒						
	}
	return $wx_access_token;
}
//防中文转义
function json_encode_ex( $value) 
{ 
	if(version_compare( PHP_VERSION,'5.4.0','<')) 
	{ 
		 $str = json_encode( $value); 
		 $str =  preg_replace_callback("#\\\u([0-9a-f]{4})#i", 
			function( $matchs) 
			{ 
				  return  iconv('UCS-2BE', 'UTF-8',  pack('H4',  $matchs[1])); 
			}, 
			  $str 
			); 
		 return  $str; 
	}else{ 
		 return json_encode( $value, JSON_UNESCAPED_UNICODE); 
	} 
}

//检查用户微信网页授权access_token是否有效 与基础支持的access_token不同
function check_web_token($openid,$web_token)
{
	$url  = 'https://api.weixin.qq.com/sns/auth?access_token='.$web_token.'&openid='.$openid;
	$output = get_url($url);
	$output = json_decode($output,true);	
	if($output['errmsg'] == 'ok')
	{
		return $web_token;	
	}else{
		return false;
	}
}
//刷新用户微信网页授权access_token 与基础支持的access_token不同
//@return array 返回结果
function refresh_web_token($mid,$refresh_token)
{
	$url = 'https://api.weixin.qq.com/sns/oauth2/refresh_token?appid='.Wx_C('wx_appid').'&grant_type=refresh_token&refresh_token='.$refresh_token;
	$output = get_url($url);
	$output = json_decode($output,true);	
	if($output['errcode'] > 0)
	{
		return false;	
	}else{
		$data = array();
		$data['web_token'] = $output['access_token'];
		$data['refresh_token'] = $output['refresh_token'];  
		M('Member')->where('member_id='.$mid)->save($data);
		unset($data);
		return $output;	
	}
}
//获取微信用户网页授权access_token 与基础支持的access_token不同
function get_web_token($mid)
{
	$ms = M('Member')->where('member_id='.$mid)->field('openid,web_token,refresh_token')->find();
	//判断令牌是否有效
	$zt = check_web_token($ms['openid'],$ms['web_token']);
	if($zt)
	{
		return $ms; 
	}else{
		//刷新令牌
		$output = refresh_web_token($mid,$ms['refresh_token']);
		if(is_array($output) && !empty($output))
		{
			return $output;	
		}
	}
}
/**
 * 编辑器内容
 * @param int $id 编辑器id名称，与name同名
 * @param string $value 编辑器内容
 * @param string $width 宽 带px
 * @param string $height 高 带px
 * @param string $style 样式内容
 * @param string $upload_state 上传状态，默认是开启
 */
function kindEditor($id, $value='', $width='650px', $height='300px', $style='visibility:hidden;',$upload_state="true", $media_open=false)
{
	//是否开启多媒体
	$media = '';
	if ($media_open){
		$media = ", 'flash', 'media'";
	}
	$items = "['source','justifyleft', 'justifycenter', 'justifyright',
			'justifyfull', 'insertorderedlist', 'insertunorderedlist', 'indent', 'outdent', '|','formatblock', 'fontsize','forecolor', 'hilitecolor', 'bold',
			'italic', '|', 'image'".$media.", 'table']";
	//图片、Flash、视频、文件的本地上传都可开启。默认只有图片，要启用其它的需要修改resource\kindeditor\php下的upload_json.php的相关参数
	echo '<textarea id="'. $id .'" name="'. $id .'" style="width:'. $width .';height:'. $height .';'. $style .'">'.$value.'</textarea>';
	echo '
<script src="'.C('SiteUrl').'/Public/static/kindeditor/kindeditor-min.js" charset="utf-8"></script>
<script src="'.C('SiteUrl').'/Public/static/kindeditor/lang/zh_CN.js" charset="utf-8"></script>
<script>
	var KE;
  KindEditor.ready(function(K) {
        KE = K.create("textarea[name=\''.$id.'\']", {
						items : '.$items.',
						cssPath : "' .C('SiteUrl'). '/Public/static/kindeditor/themes/default/default.css",
						uploadJson : "'.C('SiteUrl').'/'.MODULE_NAME.'/Editupload/upload",  
						allowImageUpload : '.$upload_state.',
						allowFlashUpload : false,
						allowMediaUpload : false,
						allowFileManager : false,
						syncType:"form",
						afterCreate : function() {
							var self = this;
							self.sync();
						},
						afterChange : function() {
							var self = this;
							self.sync();
						},
						afterBlur : function() {
							var self = this;
							self.sync();
						}
        });
			KE.appendHtml = function(id,val) {
				this.html(this.html() + val);
				if (this.isCreated) {
					var cmd = this.cmd;
					cmd.range.selectNodeContents(cmd.doc.body).collapse(false);
					cmd.select();
				}
				return this;
			}
	});
</script>';
	//return true;
}
/**
 * 百度编辑器ueditor
 * @param int $id 编辑器id名称，与name同名
 * @param string $value 编辑器内容
 * @param string $width 宽 带px
 * @param string $height 高 带px
 */
function ueditor($id, $value='', $width='1000px', $height='500px')
{
	echo '<script type="text/javascript">
	window.onload=function(){
		UE.getEditor('.$id.');
	}
	</script>';
	echo '<script type="text/javascript" charset="utf-8" src="'.C('SiteUrl').'/Public/static/ueditor/ueditor.config.js"></script>';
	echo '<script type="text/javascript" charset="utf-8" src="'.C('SiteUrl').'/Public/static/ueditor/ueditor.all.js"> </script>';
	echo '<script type="text/javascript" charset="utf-8" src="'.C('SiteUrl').'/Public/static/ueditor/lang/zh-cn/zh-cn.js"></script>';
	echo '<script id="'.$id.'" name="'.$id.'" type="text/plain" style="width:'.$width.';height:'.$height.';">'.$value.'</script>';
}

/**
 * 	作用：产生随机字符串，不长于32位
 */
function createNoncestr($length = 32) 
{
	$chars = "abcdefghijklmnopqrstuvwxyz0123456789";  
	$str ="";
	for ( $i = 0; $i < $length; $i++ )  {  
		$str.= substr($chars, mt_rand(0, strlen($chars)-1), 1);  
	}  
	return $str;
}

/**
 * 	作用：array转xml
 */
function arrayToXml($arr)
{
	$xml = "<xml>";
	foreach ($arr as $key=>$val)
	{
		 if (is_numeric($val))
		 {
			$xml.="<".$key.">".$val."</".$key.">"; 

		 }
		 else
			$xml.="<".$key."><![CDATA[".$val."]]></".$key.">";  
	}
	$xml.="</xml>";
	return $xml; 
}

/**
 * 	作用：将xml转为array
 */
function xmlToArray($xml)
{		
	//将XML转为array        
	$array_data = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);		
	return $array_data;
}
/**
 * 	作用：获取微信授权用户openid
 */
function get_openid($mid)
{
	$openid = M('Member')->where('member_id='.$mid)->getField('openid');
	return $openid;	
} 
/**
 * 	作用：微信JSAPI方式支付参数请求
 */
function jsapi_pay($param)
{
	Vendor('WxPayPubHelper.WxPayPubHelper');
	$jsApi = new \JsApi_pub();
	$unifiedOrder = new \UnifiedOrder_pub();		
	$unifiedOrder->setParameter("openid",$param['openid']);//用户openid
	$unifiedOrder->setParameter("body",$param['body']);//商品描述				
	$unifiedOrder->setParameter("out_trade_no",$param['out_trade_no']);//商户订单号 
	$unifiedOrder->setParameter("total_fee",$param['total_fee']);     //总金额
	$unifiedOrder->setParameter("notify_url",C('SiteUrl')."/Payment/paynotify");//通知地址 
	$unifiedOrder->setParameter("trade_type","JSAPI");//交易类型				

	$prepay_id = $unifiedOrder->getPrepayId();			
	//使用jsapi调起支付
	$jsApi->setPrepayId($prepay_id);
	$jsApiParameters = $jsApi->getParameters();	
	return $jsApiParameters;		
}
//返回订单状态名称
function get_order_state_name($order_state)
{
	switch ($order_state)
	{
		case 10:
		  $return = "未付款";
		  break;
		case 20:
		  $return = "已付款";
		  break;
		case 30:
		  $return = "已发货";
		  break;
		case 40:
		  $return = "已收货";
		  break;
		case 50:
		  $return = "已完成";
		  break;	 
		case 60:
		  $return = "已取消";
		  break;		   	  
		default:
		  $return = "未知状态";
	}	
	return $return;	
}

/**
 * 根据地区id获取地区名字
 */
function getDistrictName($disId)
{
	return M('District')->where(array('id'=>$disId))->getField('name');
}
/**
 * 单文件上传
 * @param array
 * @return string
 */
function upload_one_file($param)
{
	$upload = new \Think\Upload();
	$upload->maxSize   =  2097152;  //字节 1KB=1024字节 默认为2M
	$upload->exts      =  array('jpg', 'gif', 'png', 'jpeg');
	$upload->savePath  =  $param['savePath']; //保存路径 相对路径
	$upload->subName   =  $param['subName'];  //子目录
	$upload->saveName  =  $param['saveName']; //保存名称
	$upload->saveExt   =  $param['saveExt'];  //保存后缀
	$upload->replace   =  true; //存在同名的文件 覆盖
	$info   =   $upload->uploadOne($param['files']);
	if(!$info)
	{
		return 'error';
	}else{
		return $info['savepath'].$info['savename'];
	}
}
/**
 * 计算中文字符串长度
 * @param string $string 字符串
 * @return number 字符串长度
 */
function utf8_strlen($string = null) {
	// 将字符串分解为单元
	preg_match_all("/./us", $string, $match);
	// 返回单元个数
	return count($match[0]);
}
/**
 * 获取子级ID
 * @param array $array 集合数组
 * @param int $pid 父级ID
 * @param string $idKey 索引键名
 * @param string $pidKey 父级关联键名
 * @return Ambigous <multitype:, multitype:unknown >
 */
function getChildsId ($array, $pid, $idKey, $pidKey){
	$arr = array();
	foreach ($array as $v){
		if ($v[$pidKey] == $pid) {
			$arr[] = $v[$idKey];
			$arr = array_merge($arr, getChildsId($array, $v[$idKey], $idKey, $pidKey));
		}
	}
	return $arr;
}
function unlimitedForLayer($cate, $child_name = 'child' , $pid_name = 'pid' , $id_name = 'id',$pid = 0){
	$arr = array();
	foreach ($cate as $v){
		if ($v[$pid_name] == $pid){
			$v[$child_name] = unlimitedForLayer($cate,$child_name,$pid_name,$id_name,$v[$id_name]);
			$arr[] = $v;
		}
	}
	return $arr;
}
//传递一个子分类ID返回所有的父级分类
function getParents ($cate, $id, $pid_name = 'pid', $id_name = 'id') {
	$arr = array();
	foreach ($cate as $v){
		if ($v[$id_name]==$id) {
			$arr[]=$v;
			$arr = array_merge(getParents($cate, $v[$pid_name] , $pid_name, $id_name),$arr);
		}
	}
	return $arr;
}
/**
 * 获取汉字首字母
 * @param string $s0 汉字
 * @return unknown|string|NULL 返回首字母大写
 */
function getfirstchar($s0){
	$fchar = ord($s0{0});
	if($fchar >= ord("A") and $fchar <= ord("z") )return strtoupper($s0{0});
	$s1 = iconv("UTF-8","gb2312", $s0);
	$s2 = iconv("gb2312","UTF-8", $s1);
	if($s2 == $s0){$s = $s1;}else{$s = $s0;}
	$asc = ord($s{0}) * 256 + ord($s{1}) - 65536;
	if($asc >= -20319 and $asc <= -20284) return "A";
	if($asc >= -20283 and $asc <= -19776) return "B";
	if($asc >= -19775 and $asc <= -19219) return "C";
	if($asc >= -19218 and $asc <= -18711) return "D";
	if($asc >= -18710 and $asc <= -18527) return "E";
	if($asc >= -18526 and $asc <= -18240) return "F";
	if($asc >= -18239 and $asc <= -17923) return "G";
	if($asc >= -17922 and $asc <= -17418) return "H";
	if($asc >= -17417 and $asc <= -16475) return "J";
	if($asc >= -16474 and $asc <= -16213) return "K";
	if($asc >= -16212 and $asc <= -15641) return "L";
	if($asc >= -15640 and $asc <= -15166) return "M";
	if($asc >= -15165 and $asc <= -14923) return "N";
	if($asc >= -14922 and $asc <= -14915) return "O";
	if($asc >= -14914 and $asc <= -14631) return "P";
	if($asc >= -14630 and $asc <= -14150) return "Q";
	if($asc >= -14149 and $asc <= -14091) return "R";
	if($asc >= -14090 and $asc <= -13319) return "S";
	if($asc >= -13318 and $asc <= -12839) return "T";
	if($asc >= -12838 and $asc <= -12557) return "W";
	if($asc >= -12556 and $asc <= -11848) return "X";
	if($asc >= -11847 and $asc <= -11056) return "Y";
	if($asc >= -11055 and $asc <= -10247) return "Z";
	return null;
}
function pinyin($zh){
	$ret = "";
	$s1 = iconv("UTF-8","gb2312", $zh);
	$s2 = iconv("gb2312","UTF-8", $s1);
	if($s2 == $zh){$zh = $s1;}
	for($i = 0; $i < strlen($zh); $i++){
		$s1 = substr($zh,$i,1);
		$p = ord($s1);
		if($p > 160){
			$s2 = substr($zh,$i++,2);
			$ret .= getfirstchar($s2);
		}else{
			$ret .= $s1;
		}
	}
	return $ret;
}
/**
 *  @desc 根据两点间的经纬度计算距离
 *  @param float $lat 纬度值
 *  @param float $lng 经度值
 */
function getDistance($lat1, $lng1, $lat2, $lng2)
{
	$earthRadius = 6367000; //approximate radius of earth in meters


	$lat1 = ($lat1 * pi() ) / 180;
	$lng1 = ($lng1 * pi() ) / 180;

	$lat2 = ($lat2 * pi() ) / 180;
	$lng2 = ($lng2 * pi() ) / 180;


	$calcLongitude = $lng2 - $lng1;
	$calcLatitude = $lat2 - $lat1;
	$stepOne = pow(sin($calcLatitude / 2), 2) + cos($lat1) * cos($lat2) * pow(sin($calcLongitude / 2), 2);
	$stepTwo = 2 * asin(min(1, sqrt($stepOne)));
	$calculatedDistance = $earthRadius * $stepTwo;

	return round($calculatedDistance);
}
/**
 * 生成随机字符串
 * @param number $length 生成字符串长度
 * @param number $upper 是否开启大写字母 0否1是
 * @param number $lower 是否开启小写字母
 * @param number $num 是否开启数字
 * @return string $nonce_str 生成的随机字符串
 */
function nonce_str($length = 8,$upper = 1,$lower = 1,$num = 1){
	// 密码字符集，可任意添加你需要的字符
	$chars = '';
	if ($upper) {
		$chars .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
	}
	if ($lower) {
		$chars .= 'abcdefghijklmnopqrstuvwxyz';
	}
	if ($num) {
		$chars .= '0123456789';
	}
	$nonce_str = '';
	for ( $i = 0; $i < $length; $i++ )
	{
		// 这里提供两种字符获取方式
		// 第一种是使用 substr 截取$chars中的任意一位字符；
		// 第二种是取字符数组 $chars 的任意元素
		// $password .= substr($chars, mt_rand(0, strlen($chars) – 1), 1);
		$nonce_str .= $chars[ mt_rand(0, strlen($chars) - 1) ];
	}
	return $nonce_str;
}
/**
 * 截取中文字符串
 * @param string $string 中文字符串
 * @param int $sublen 截取长度
 * @param int $start 开始长度 默认0
 * @param string $code 编码方式 默认UTF-8
 * @param string $omitted 末尾省略符 默认...
 * @return string
 */
 function cut_str($string, $sublen, $start = 0, $code = 'UTF-8', $omitted = '...')
 {
     if($code == 'UTF-8')
     {
         $pa ="/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|\xe0[\xa0-\xbf][\x80-\xbf]|[\xe1-\xef][\x80-\xbf][\x80-\xbf]|\xf0[\x90-\xbf][\x80-\xbf][\x80-\xbf]|[\xf1-\xf7][\x80-\xbf][\x80-\xbf][\x80-\xbf]/";
         preg_match_all($pa, $string, $t_string);
         if(count($t_string[0]) - $start > $sublen) return join('', array_slice($t_string[0], $start, $sublen)).$omitted;
         return join('', array_slice($t_string[0], $start, $sublen));
     }
     else
     {
         $start = $start*2;
         $sublen = $sublen*2;
         $strlen = strlen($string);
         $tmpstr = ''; for($i=0; $i<$strlen; $i++)
         {
             if($i>=$start && $i<($start+$sublen))
             {
                 if(ord(substr($string, $i, 1))>129)
                 {
                     $tmpstr.= substr($string, $i, 2);
                 }
                 else
                 {
                     $tmpstr.= substr($string, $i, 1);
                 }
             }
             if(ord(substr($string, $i, 1))>129) $i++;
         }
         if(strlen($tmpstr)<$strlen ) $tmpstr.= $omitted;
         return $tmpstr;
     }
 }
/**
 * 调用api接口
 * @param url $apiurl api.muxiangdao.cn/Article/articleList
 * @param array $param ['status'=>'1','page'=>'2','pageshow'=>'10'];
 * @param string $format eg:array(arr),object(obj),json;defalut = array
 */
 function get_api($apiurl, $param, $format = 'array'){
 	if (is_array($param)) {
 		$string = '?';
 		foreach ($param as $key => $val){
 			$string .= $key.'='.$val.'&';
 		}
 		$param = substr($string, 0, -1);
 	}
 	$url = $apiurl.$param;
 	$json = get_url($url);
 	$start = strpos($json, '{');
 	$end = -1*(strlen(strrchr($json, '}'))-1);
 	if ($end) {
		$json = substr($json, $start, $end);
 	}else {
 		$json = substr($json, $start);
 	}
 	$obj = json_decode($json);
	$array = object_to_array($obj);
	$xml = arrayToXml($array);
	switch ($format){
		case 'array':$data = $array;break;
		case 'arr':$data = $array;break;
		case 'obj':$data = $obj;break;
		case 'object':$data = $obj;break;
		case 'json':$data = $json;break;
		default:$data = $array;
	}
	return $data;
 }
 function getCity($City, $langue = 'CH'){
 	$langue = strtolower($langue);
 	$City = M('District')->where(array('name_en'=>$City,'status'=>1))->getField('name_'.$langue);
 	return $City;
 }
 /**
  * SEO读取
  * @param array $param 首选值 $param = ['title'=>'seo标题','keywords'=>'seo关键字','description'=>'seo描述'];
  * @param string $controller 控制器
  * @param string $action 方法
  */
 function seo($param = array(), $controller = CONTROLLER_NAME,$action = ACTION_NAME){
 	$seo = M('Seo');
 	$controller = CONTROLLER_NAME;
 	$action = ACTION_NAME;
 	$cavalue = $controller.'/'.$action;
 	$seo_info = $seo->where(array('cavalue'=>$cavalue))->find();
 	if (empty($seo_info)) {
 		$cavalue = $controller.'/*';
 		$seo_info = $seo->where(array('cavalue'=>$cavalue))->find();
 		if (empty($seo_info)) {
 			$seo_info = $seo->where(array('default'=>1))->find();
 		}
 	}
 	if (!empty($seo_info)) {
 		!empty($param['title']) ? $seo_info['title'] = $param['title'] : '';
 		$seo_html = '<title>'.$seo_info['title'].'</title>';
 		!empty($param['keywords']) ? $seo_info['keywords'] = $param['keywords'] : '';
 		$seo_html .= '<meta content="'.$seo_info['keywords'].'" name="keywords">';
 		!empty($param['description']) ? $seo_info['description'] = $param['description'] : '';
 		$seo_html .= '<meta content="'.$seo_info['description'].'" name="description">';
 		
 		$param = $_GET;
 		if (!empty($param['city'])) {
 			$replace = M('District')->where(array('name_en'=>trim($param['city'])))->getField('name_ch');
 		}else {
 			$replace = '';
 		}
 		$search = '{{$city}}';
 		$seo_html = str_replace($search, $replace, $seo_html);
 		
 		if (!empty($param['area'])) {
 			$replace = M('District')->where(array('id'=>intval($param['area'])))->getField('name_ch');
 		}else {
 			$replace = '';
 		}
 		$search = '{{$area}}';
 		$seo_html = str_replace($search, $replace, $seo_html);
 		
 		$search = '{{$type}}';
 		$seo_html = str_replace($search, $replace, $seo_html);
 		
 		return $seo_html;
 	}else {
 		return false;
 	}
 }
 /**
  * 单文件上传
  * @param array
  * @return string
  */
 function upload_one($param)
 {
 	$upload = new \Think\Upload();
 	$upload->maxSize   =  2097152;  //字节 1KB=1024字节 默认为2M
 	$upload->exts      =  array('jpg', 'gif', 'png', 'jpeg');
 	$upload->savePath  =  $param['savePath']; //保存路径 相对路径
 	$upload->subName   =  $param['subName'];  //子目录
 	$upload->saveName  =  $param['saveName']; //保存名称
 	$upload->saveExt   =  $param['saveExt'];  //保存后缀
 	$upload->replace   =  true; //存在同名的文件 覆盖
 	$info   =   $upload->uploadOne($param['files']);
 	if(!$info)
 	{
 		return 'error';
 	}else{
 		return $info['savepath'].$info['savename'];
 	}
 }
 function is_mobile_request()
 {
 	$_SERVER['ALL_HTTP'] = isset($_SERVER['ALL_HTTP']) ? $_SERVER['ALL_HTTP'] : '';
 	$mobile_browser = '0';
 	if(preg_match('/(up.browser|up.link|mmp|symbian|smartphone|midp|wap|phone|iphone|ipad|ipod|android|xoom)/i', strtolower($_SERVER['HTTP_USER_AGENT'])))
 		$mobile_browser++;
 	if((isset($_SERVER['HTTP_ACCEPT'])) and (strpos(strtolower($_SERVER['HTTP_ACCEPT']),'application/vnd.wap.xhtml+xml') !== false))
 		$mobile_browser++;
 	if(isset($_SERVER['HTTP_X_WAP_PROFILE']))
 		$mobile_browser++;
 	if(isset($_SERVER['HTTP_PROFILE']))
 		$mobile_browser++;
 	$mobile_ua = strtolower(substr($_SERVER['HTTP_USER_AGENT'],0,4));
 	$mobile_agents = array(
 			'w3c ','acs-','alav','alca','amoi','audi','avan','benq','bird','blac',
 			'blaz','brew','cell','cldc','cmd-','dang','doco','eric','hipt','inno',
 			'ipaq','java','jigs','kddi','keji','leno','lg-c','lg-d','lg-g','lge-',
 			'maui','maxo','midp','mits','mmef','mobi','mot-','moto','mwbp','nec-',
 			'newt','noki','oper','palm','pana','pant','phil','play','port','prox',
 			'qwap','sage','sams','sany','sch-','sec-','send','seri','sgh-','shar',
 			'sie-','siem','smal','smar','sony','sph-','symb','t-mo','teli','tim-',
 			'tosh','tsm-','upg1','upsi','vk-v','voda','wap-','wapa','wapi','wapp',
 			'wapr','webc','winw','winw','xda','xda-'
 	);
 	if(in_array($mobile_ua, $mobile_agents))
 		$mobile_browser++;
 	if(strpos(strtolower($_SERVER['ALL_HTTP']), 'operamini') !== false)
 		$mobile_browser++;
 	// Pre-final check to reset everything if the user is on Windows
 	if(strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'windows') !== false)
 		$mobile_browser=0;
 	// But WP7 is also Windows, with a slightly different characteristic
 	if(strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'windows phone') !== false)
 		$mobile_browser++;
 	if($mobile_browser>0)
 		return true;
 	else
 		return false;
 }