<?php
	
	// 获取http请求头部信息
	function getHeaderInfo($header='',$echo=false) {
		ob_start();
		$headers   	= getallheaders();
		if(!empty($header)) {
			$info 	= $headers[$header];
			echo($header.':'.$info."\n"); ;
		}else {
			foreach($headers as $key=>$val) {
				echo("$key:$val\n");
			}
		}
		$output 	= ob_get_clean();
		if ($echo) {
			echo (nl2br($output));
		}else {
			// 返回请求头数组
			return $headers;
		}

	}
	// Api接口返回err类型
	function Error_Message($data){
		$error = array(
			1001   => '权限验证错误,无法访问接口',
			1002   => '权限验证参数错误，无法访问接口',
			1003   => '参数错误',
			1004   => '渠道不存在',
			1005   => '渠道不可用',
			1006   => '必要的参数不能为空',
			1007   => '不存在该用户',
			1008   => '账户原密码不正确',
			1009   => '账户更新密码失败',
			1011   => '新增地址失败',
			1012   => '不存在改地址',
			1013   => '地址更新失败',
			1014   => '地址删除失败',
			1015   => '设置默认地址失败',
			1016   => '没有找到该商品',
			1017   => '创建订单缺少必要信息',
			1018   => '创建订单失败',
			1019   => '该订单不存在',
			1020   => '该订单已支付',
			1021   => '订单支付失败',
			1022   => '积分余额不足',
			1023   => '确认收货失败',
			1024   => '删除订单失败',
			1025   => '消息更新失败',
			1026   => '步数上传失败',
			1027   => '该地址不存在',
			1028   => '意见反馈失败'
   		);
		return $error[$data];
	}
	
	// 返回数据方法
	function outPut($data,$page = NULL){
		if (!is_array($data)) {
            $status = array(
                'status' => array(
                    'succeed' => 0,
                    'error_code' => $data,
                    'error_desc' => Error_Message($data)
                )
            );
            die(json_encode($status));
        }
		if (isset($data['data'])) {
		    $data = $data['data'];
		}
        $data = array_merge(array('data'=>$data), array('status' => array('succeed' => 1)));
		if (!empty($pager)) {
			$data = array_merge($data, array('page'=>$pager));
		}
        die(json_encode($data));
	}
	
	// 所有资源返回地址
	function getUrl($url)
	{
		if(strtolower(substr($url, 0, 4))== 'http')
		{
			return $url;
		}
		else
		{
			// 网站更目录
			$pathurl = Config('base_path').'/'.ltrim($url, '/');
			
			if(strpos($url,'public') !== false)
			{
				return 'http://oy98pgcb1.bkt.clouddn.com/'.ltrim($url, '/');
			}
			else
			{
				return 'http://oy98pgcb1.bkt.clouddn.com/public/'.ltrim($url, '/');
			}
			
		}
	}
	
	/**
    * 获取客户端IP地址
    * @param integer $type
    * @return mixed
    */
	function getclientip() {
          static $realip = NULL;
            
          if($realip !== NULL){
              return $realip;
          }
          if(isset($_SERVER)){
              if(isset($_SERVER['HTTP_X_FORWARDED_FOR'])){ //但如果客户端是使用代理服务器来访问，那取到的就是代理服务器的 IP 地址，而不是真正的客户端 IP 地址。要想透过代理服务器取得客户端的真实 IP 地址，就要使用 $_SERVER["HTTP_X_FORWARDED_FOR"] 来读取。
                  $arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
                  /* 取X-Forwarded-For中第一个非unknown的有效IP字符串 */
                  foreach ($arr AS $ip){
                      $ip = trim($ip);
                      if ($ip != 'unknown'){
                          $realip = $ip;
                          break;
                      }
                  }
              }elseif(isset($_SERVER['HTTP_CLIENT_IP'])){//HTTP_CLIENT_IP 是代理服务器发送的HTTP头。如果是"超级匿名代理"，则返回none值。同样，REMOTE_ADDR也会被替换为这个代理服务器的IP。
                  $realip = $_SERVER['HTTP_CLIENT_IP'];
              }else{
                  if (isset($_SERVER['REMOTE_ADDR'])){ //正在浏览当前页面用户的 IP 地址
                      $realip = $_SERVER['REMOTE_ADDR'];
                  }else{
                      $realip = '0.0.0.0';
                  }
              }
          }else{
              //getenv环境变量的值
              if (getenv('HTTP_X_FORWARDED_FOR')){//但如果客户端是使用代理服务器来访问，那取到的就是代理服务器的 IP 地址，而不是真正的客户端 IP 地址。要想透过代理服务器取得客户端的真实 IP 地址
                  $realip = getenv('HTTP_X_FORWARDED_FOR');
              }elseif (getenv('HTTP_CLIENT_IP')){ //获取客户端IP
                  $realip = getenv('HTTP_CLIENT_IP');
              }else{
                  $realip = getenv('REMOTE_ADDR');  //正在浏览当前页面用户的 IP 地址
              }
          }
          preg_match("/[\d\.]{7,15}/", $realip, $onlineip);
          $realip = !empty($onlineip[0]) ? $onlineip[0] : '0.0.0.0';
          return $realip;
   }
   
	function sortSteps_Num($a,$b)
	{
		if(intval($a['steps_number_total'])<=intval($b['steps_number_total']))
		{
			return 1;
		}
		else
		{
			return -1;
		}
	}
   
   /*
   用户积分兑换规则
   */
   
   function exchange($steps)
   {
	   return 100;
   }