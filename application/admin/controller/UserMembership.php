<?php
namespace app\admin\controller;
use app\common\controller\AdminBase;
use think\facade\Config;
use think\facade\Cache;
use think\facade\Session;
use think\Db;

class UserMembership extends AdminBase
{
    protected function initialize()
    {
        parent::initialize();
		$this->tabs = array(
			'index'=>array(
				'name'=>'用户会员',
				'url'=>url('admin/'.$this->controller.'/index'),
				'url_str' =>'admin/'.$this->controller.'/index',
				'status'=>false
			)
		);
    }

    public function index()
    {
        $user_membership_list = $this->user_membership_model->alias('a')
		->join('os_user t','t.id=a.user_id')
		->join('os_membership_plan p','a.plan_id=p.id')
		->field('a.id,a.start_time,a.end_time,a.status,a.created_at,t.user_name,p.name as plan_name')
		->select();
		$this->tabs['index']['status'] = true;
		$this->assign('tabs',$this->tabs);
        return $this->fetch('index', ['user_membership_list' => $user_membership_list]);
    }
}