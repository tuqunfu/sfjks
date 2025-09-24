<?php
namespace app\index\controller;
use app\common\controller\HomeBase;
use think\facade\Config;
use think\facade\Cache;
use think\facade\Session;
use think\Db;

class User extends HomeBase
{
    protected function initialize()
    {
        parent::initialize();
    }
    
	// 注册
    public function register()
    {
        if ($this->request->isPost()) {
            $data            = $this->request->param();
            $validate_result = $this->validate($data, 'UserRegister');
            if ($validate_result !== true) {
                $this->error($validate_result);
            } else {
				$data['nick_name'] = $data['user_name'];
                $data['password'] = md5($data['password'].config('application.salt'));
                $data['create_time'] = strtotime('now');
                $data['create_by'] = 'admin';
				$data['status'] = 1;
                if ($this->user_model->allowField(true)->save($data)) {
                    $this->success('保存成功');
                } else {
                    $this->error('保存失败');
                }
            }
        }
    }

    // 登录系统
	public function login()
    {
        if ($this->request->isPost()) {
            $data            = $this->request->only(['username', 'password', 'verify']);
            $validate_result = $this->validate($data, 'UserLogin');
            if ($validate_result !== true) {
                $this->error($validate_result);
            } else {
                $username = $data['username'];
                $password = $data['password'];
                // 构建密码加密值
                $hashedPassword = md5($password . config('application.salt'));
                $where = [
                    // 密码必须匹配
                    ['a.password', '=', $hashedPassword],
                ];
                // 判断 $username 是手机号还是用户名
                if (preg_match('/^1[3456789]\d{9}$/', $username)) {
                    // 是手机号
                    $where[] = ['a.mobile', '=', $username];
                } else {
                    // 当作用户名处理
                    $where[] = ['a.user_name', '=', $username];
                }
                $user = Db::name('user')
                        ->alias('a')
                        ->field('a.id, a.user_name, a.status, a.mobile')
                        ->where($where)
                        ->find();
                if (!empty($user)) {
                    if ($user['status'] != 1) {
                        $this->error('当前用户已禁用');
                    } else {
                        Session::set('user_id', $user['id']);
                        Session::set('user_name', $user['user_name']);
						Session::set('user', $user);
                        Db::name('user')->update(
                            [
                                'last_login_time' =>  time(),
                                'last_login_ip'   => $this->request->ip(),
                                'id'              => $user['id']
                            ]
                        );
                        $this->success('登录成功', 'Index/font/index',$user);
                    }
                } else {
                    $this->error('用户名或密码错误');
                }
            }
        }
    }

    // 退出登录
    public function logout(){
        Session::delete('user_id');
        Session::delete('user_name');
        $this->success('退出成功', 'index/font/index');
    }
    
    // 获取用户信息
    public function get_user_info(){
        $user_id    = Session::get('user_id');
        $where['a.id'] = $user_id;
        $user = Db::name('user')->alias('a')
									->field('a.id,a.user_name,a.status')
									->where($where)
									->find();
        if (!empty($user)) {
            $user_membership = $this->user_membership_model->alias('a')
                                    ->join('os_user t','t.id=a.user_id')
                                    ->join('os_membership_plan p','a.plan_id=p.id')
                                    ->field('a.id,a.start_time,a.end_time,a.status,a.created_at,t.user_name,p.name as plan_name')
                                    ->where(array('a.user_id'=>$user_id,'a.status'=>1))
                                    ->find();
            if($user_membership!=null){
                $user['membership'] = $user_membership['plan_name'];
            }else{
                $user['membership'] = null;
            }
            return json($user);
        }else{
            return json(null);
        }
    }

    // 验证
    public function getstatus(){
        $user_id    = Session::get('user_id');
        if(empty($user_id)){
            $font_log_list = $this->font_log_model->where(array('user_ip'=>$this->request->ip()))->select();
            if(!empty($font_log_list) && count($font_log_list)>3){
                return json(false);
            } else {
                $data['user_ip'] = $this->request->ip();
                $data['session_id'] = session_id();
                $data['create_time'] = strtotime('now');
                if ($this->font_log_model->allowField(true)->save($data)) {
                     return json(true);
                } else {
                    return json(false);
                }
            }
        }else{
            return json(true);
        }
    }

    // 获取会员列表
    public function get_membership_plan_list(){
        $list = $this->membership_plan_model->where(array('is_active'=>1))->select();
        return json($list);
    }

    // 创建支付宝支付页面
    public function user_membership(){
        $user_id    = Session::get('user_id');
        if($user_id == null){
            $this->error("请用户登录！");
        }
        if ($this->request->isPost()) {
            $data = $this->request->only(['id']);
            $membership_plan = $this->membership_plan_model->where(array('id'=>$data['id']))->find();
            if($membership_plan ==  null){
                $this->error("会员已失效！");
            }
             //引入支付宝支付
            require_once VENDOR_PATH.'/alipay/config.php';
            require_once VENDOR_PATH.'/alipay/pagepay/service/AlipayTradeService.php';
            require_once VENDOR_PATH.'/alipay/pagepay/buildermodel/AlipayTradePagePayContentBuilder.php';
            // 创建支付宝支付链接
            header("Content-type:text/html;charset=utf-8");
            $total_amount = $membership_plan['price'];
            $out_trade_no = $this->generateOrderNo();
            $payRequestBuilder = new \AlipayTradePagePayContentBuilder();
            $payRequestBuilder->setBody('书法教科书会员');
            $payRequestBuilder->setSubject('书法教科书会员');
            $payRequestBuilder->setTotalAmount($total_amount);
            $payRequestBuilder->setOutTradeNo($out_trade_no);
            // 创建订单数据
            $order = [];
            $order['create_time'] = strtotime('now');
            $order['create_by'] = 'admin';
            $order['order_no'] = $out_trade_no;
            $order['user_id'] = $user_id;
            $order['amount'] = $total_amount;
            $order['pay_status'] = 0;
            $order['status'] = 0;
            $order['plan_id'] = $membership_plan['id'];
            $order['order_desc'] = '书法教科书会员';
            $status = $this->order_model->allowField(true)->save($order);
            if($status){
                //电脑网站支付请求
                $aop = new \AlipayTradeService($config);
                $response = $aop->pagePay($payRequestBuilder,$config['return_url'],$config['notify_url']);
                //输出表单
                var_dump($response);
            }else{
                $this->error("订单创建失败");
            }
        }
    }

    function generateOrderNo() {
        $micro = substr(microtime(), 2, 4); // 取微秒部分作为随机因子
        $rand = str_pad(rand(0, 99), 2, '0', STR_PAD_LEFT);
        return date('YmdHis') . $micro . $rand;
    }
}