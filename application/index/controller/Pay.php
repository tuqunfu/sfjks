<?php
namespace app\index\controller;

use app\common\controller\HomeBase;
use think\facade\Config;
use think\facade\Cache;
use think\facade\Session;
use think\Db;

// 支付
class Pay extends HomeBase
{
    protected function initialize()
    {
        parent::initialize();
    }
	
    // 支付回调
    public function notify_url(){
        require_once VENDOR_PATH.'/alipay/config.php';
        require_once VENDOR_PATH.'/alipay/pagepay/service/AlipayTradeService.php';
        $arr=$_POST;
        $alipaySevice = new \AlipayTradeService($config); 
        $alipaySevice->writeLog(var_export($_POST,true));
        $result = $alipaySevice->check($arr);
        if($result) {//验证成功
            $out_trade_no = $_POST['out_trade_no'];
            //支付宝交易号
            $trade_no = $_POST['trade_no'];
            //交易状态
            $trade_status = $_POST['trade_status'];
            $order = $this->order_model->where(array('order_no'=>$trade_no))->find();
        if($_POST['trade_status'] == 'TRADE_FINISHED') {
            
        }
        else if ($_POST['trade_status'] == 'TRADE_SUCCESS') {
            if($order!=null && $order['pay_status']==0){
                $order->id       = $data['id'];
                $order->pay_status = 1;
                $order->status = 2;
                $order->pay_time = strtotime('now');
                $order->save();
                // 新增用户
                $user_membership = [];
                $user_membership['user_id'] = $order['user_id'];
                $user_membership['plan_id'] = $order['plan_id'];
                $user_membership['start_time'] = strtotime('now');
                $user_membership['end_time'] = strtotime('now');
                $user_membership['status'] = 1;
                $user_membership['source'] = 'paid';
                $user_membership['created_at'] = strtotime('now');
                $user_membership_model->allowField(true)->save($user_membership);
            }
        }
        //——请根据您的业务逻辑来编写程序（以上代码仅作参考）——
        echo "success";	//请不要修改或删除
        }else {
            //验证失败
            echo "fail";
        }
    }

    // 支付通知
    public function return_url(){
        require_once VENDOR_PATH.'/alipay/config.php';
        require_once VENDOR_PATH.'/alipay/pagepay/service/AlipayTradeService.php';
        $arr=$_GET;
        $alipaySevice = new \AlipayTradeService($config); 
        $result = $alipaySevice->check($arr);
        if($result) {//验证成功
            //商户订单号
            $out_trade_no = htmlspecialchars($_GET['out_trade_no']);
            //支付宝交易号
            $trade_no = htmlspecialchars($_GET['trade_no']);
            return redirect(url('/index/font/index'));
        }
        else {
            //验证失败
           return redirect(url('/index/font/index'));
        }
    }

    public function index()
    {
		header("Content-type:text/html;charset=utf-8");
        $total_amount = input('post.total_amount');
        if($total_amount){
            //引入支付宝支付
            require_once VENDOR_PATH.'/alipay/config.php';
            require_once VENDOR_PATH.'/alipay/pagepay/service/AlipayTradeService.php';
            require_once VENDOR_PATH.'/alipay/pagepay/buildermodel/AlipayTradePagePayContentBuilder.php';
 
            //商户订单号，商户网站订单系统中唯一订单号，必填
            $out_trade_no = input('post.out_trade_no');
            //订单名称，必填
            $subject = input('post.goods_name');
            //付款金额，必填
            $total_amount = $total_amount;
            //商品描述，可空
            $body = input('post.goods_body');
            //构造参数
            $payRequestBuilder = new \AlipayTradePagePayContentBuilder();
            $payRequestBuilder->setBody($body);
            $payRequestBuilder->setSubject($subject);
            $payRequestBuilder->setTotalAmount($total_amount);
            $payRequestBuilder->setOutTradeNo($out_trade_no);
 
            //电脑网站支付请求
            $aop = new \AlipayTradeService($config);
            $response = $aop->pagePay($payRequestBuilder,$config['return_url'],$config['notify_url']);
 
            //输出表单
            var_dump($response);
        }else{
            $out_trade_no = 'ALPAY'.date('YmdHis'); //订单号
            $goods_name = '在线支付'; //商品名称
            $goods_body = 'test'; //商品描述
 
            $this->assign('out_trade_no',$out_trade_no);
            $this->assign('goods_name',$goods_name);
            $this->assign('goods_body',$goods_body);
			
			$template = array(
				"title" => "支付",
				"show_header" => true,
				"show_footer" => true,
				"footer_active" => 1
			);
			$this->assign('template', $template);
			return $this->fetch();
        }
    }
}
