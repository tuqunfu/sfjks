<?php
namespace app\api\controller;

use think\Controller;
use think\Session;
/*api基类*/
class Base extends Controller
{
    protected function _initialize()
    {
        parent::_initialize();
		$isopen_sign = config('isopen_sign');
		/*
		if($isopen_sign)
		{
			$auth_token = config('auth_token');
			$header = getHeaderInfo();
			$header['Sign'] = $header['Sign'] ? $header['Sign'] : $header['sign'];
			$header['Time'] = $header['Time'] ? $header['Time'] : $header['time'];
			$realimei = input('get.realimei');
			$signstr = $realimei.$auth_token.$header['Time'];
			if(!empty($header['Sign']) && !empty($header['Time']) && !empty($realimei) )
			{
				// 
				if(abs($header['Time'] - time()*1000) < 60000)
				{
					if($header['Sign']!= md5($signstr))
					{
						echo json_encode(array('status'=>3,'msg'=>'接口验证错误！'));
						exit;
					}
				}
				else
				{
					echo json_encode(array('status'=>3,'msg'=>'接口请求超时'));
					exit;
				}
			}
			else
			{
				echo json_encode(array('status'=>3,'msg'=>'接口参数错误'));
				exit;
			}
		}*/
		
    }
}