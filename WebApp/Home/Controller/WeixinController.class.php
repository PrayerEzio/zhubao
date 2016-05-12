<?php
namespace Home\Controller;
use Think\Controller;

class WeixinController extends Controller {
	//const TOKEN = 'mxdmxd';
	public function valid()
    {
		if($_GET["echostr"])
		{
			$echoStr = $_GET["echostr"];
			if($this->checkSignature())
			{
				echo $echoStr;
				exit;
			}
		}else{
			$this->responseMsg();	
		}
    }
	//消息处理
    public function responseMsg()
    {
		//get post data, May be due to the different environments
		//接收数据 
		$postStr = $GLOBALS["HTTP_RAW_POST_DATA"]; //获取POST数据  
		
      	//extract post data
		if(!empty($postStr))
		{
              	$postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);  //用SimpleXML解析POST过来的XML数据  
                $fromUsername = $postObj->FromUserName;    //获取发送方帐号(OpenID)  
                $toUsername = $postObj->ToUserName;        //获取接收方账号  
                $keyword = trim($postObj->Content);        //获取消息内容  
				$msgType = $postObj->MsgType;              //消息类型
				
				//返回数据处理			
												
                //消息处理类型
				if($msgType == 'text') 
				{	   
					if(!empty($keyword))
					{						
						switch ($keyword)
						{																																																																								
							default:
								$contentStr = $this->get_click_msg('return_msg'); //自动回复
								$resultStr = $this->seedTextMessage($fromUsername,$toUsername,$contentStr);	
								break;														
						} //switch end																				
					}else{
						echo '你想说点什么？';
					}
					
				}
				
				//事件处理类型
				if($msgType == 'event')
				{  
					$event = $postObj->Event; //事件类型
					switch($event)
					{
						//关注事件
						case 'subscribe':  
							$contentStr  = $this->get_click_msg('subscribe');
							$resultStr = $this->seedTextMessage($fromUsername,$toUsername,$contentStr);	
							break;
						//已关注的用户 扫描二维码时	
						case 'SCAN': 
							$contentStr  = $this->get_click_msg('scan');
							$resultStr = $this->seedTextMessage($fromUsername,$toUsername,$contentStr);	
							break;
						//自定义菜单点击事件	
						case 'CLICK': 
							$EventKey = $postObj->EventKey; //事件KEY值
							$contentStr = $this->get_click_msg($EventKey);
							$resultStr = $this->seedTextMessage($fromUsername,$toUsername,$contentStr);	
							break;	

						//自定义菜单跳转事件	
/*						 case 'VIEW': 
							$EventKey = $postObj->EventKey; //事件URL值						
					    	break;	*/																			
					}  //switch end	
					
				}
				echo $resultStr;  //输出结果		

        }else{
        	echo "";
        	exit;
        }
    }	
	
	//发送普通消息
	public function seedTextMessage($fromUsername,$toUsername,$contentStr)
	{
		$msg_type = 'text';
		$textTpl = "<xml>
					<ToUserName><![CDATA[%s]]></ToUserName>
					<FromUserName><![CDATA[%s]]></FromUserName>
					<CreateTime>%s</CreateTime>
					<MsgType><![CDATA[%s]]></MsgType>
					<Content><![CDATA[%s]]></Content>
					<FuncFlag>0</FuncFlag>
					</xml>";  	
					
			$resultStr = sprintf($textTpl, $fromUsername, $toUsername, NOW_TIME, $msg_type, $contentStr);  	
			return $resultStr;			
	}
	
	//发送图文消息
	public function sendPicTextMessage($fromUsername,$toUsername,$item_str,$item_count)
	{
		$msg_type ='news';
		$textTpl="<xml>  
				  <ToUserName><![CDATA[%s]]></ToUserName>  
			      <FromUserName><![CDATA[%s]]></FromUserName>  
				  <CreateTime>%s</CreateTime>  
				  <MsgType><![CDATA[%s]]></MsgType>  
				  <ArticleCount>%s</ArticleCount>  
				  <Articles>$item_str</Articles>  
				  </xml>";	
		$resultStr = sprintf($textTpl, $fromUsername, $toUsername, NOW_TIME, $msg_type, $item_count);  			  
		return $resultStr;				  		
	}	
	
	//获取推送消息
	public function get_click_msg($key)
	{
		if($key)
		{
			$msg_key = 'WxMsg_'.$key;
			$contentStr = F($msg_key);
			if($contentStr == false)
			{
				$contentStr = M('WxMsg')->where('msg_key=\''.$key.'\'')->getField('body');	
				F($msg_key,$contentStr);
			}
			return $contentStr;	
		}	
	}
	
	//服务器配置验证	
	private function checkSignature()
	{
        // you must define TOKEN by yourself
		//$token = self::TOKEN;
		$token = M('WxSetting')->where(array('wx_id'=>1))->getField('wx_token');
        if (!$token) {
            throw new Exception('TOKEN is not defined!');
        }
        
        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];
        		
		$tmpArr = array($token, $timestamp, $nonce);
        // use SORT_STRING rule
		sort($tmpArr, SORT_STRING);
		$tmpStr = implode( $tmpArr );
		$tmpStr = sha1( $tmpStr );
		
		if( $tmpStr == $signature ){
			return true;
		}else{
			return false;
		}
	}
	//逻辑处理
/*	public function to_set_data($openid)
	{
		$openid = (string)$openid;
		$Member = M('Member');
		$m_info = $Member->where('openid=\''.$openid.'\'')->find();
		if(is_array($m_info) && !empty($m_info))
		{
			//已关注的用户 更新第几次关注
			$Member->where('member_id='.$m_info['member_id'])->setInc('guanzhu_num',1); 
			return $this->get_click_msg('subscribe_2');	
		}else{
			//第一次关注
			$data = array();
			$data['openid'] = $openid;
			$data['register_time'] = NOW_TIME;
			$data['guanzhu_num'] = 1;
			$Member->add($data);
			unset($data);
			return $this->get_click_msg('subscribe');	
		}
	}*/
			
}