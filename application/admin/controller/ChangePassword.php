<?php
namespace app\admin\controller;

use app\common\controller\AdminBase;
use think\facade\Config;
use think\facade\Cache;
use think\facade\Session;
use think\Db;

class ChangePassword extends AdminBase
{
	public function initialize()
    {
        parent::initialize();
		// 顶部导航栏信息
		$this->tabs = array(
			'index'=>array(
				'name'=>'更改密码',
				'url'=>url('admin/'.$this->controller.'/index'),
				'url_str' =>'admin/'.$this->controller.'/index',
				'status'=>false
			)
		);
    }
	
    public function index()
    {
		$this->tabs['index']['status'] = true;
		$this->assign('tabs',$this->tabs);
		$this->assign('btns',$this->btns);
        return $this->fetch('system/change_password');
    }

    public function updatePassword()
    {
        if ($this->request->isPost()) {
            $admin_id    = Session::get('admin_id');
            $data   = $this->request->param();
            $result = Db::name('admin_user')->find($admin_id);
            if ($result['password'] == md5($data['old_password'] . Config::get('application.salt'))) {
                if ($data['password'] == $data['confirm_password']) {
                    $new_password = md5($data['password'] . Config::get('application.salt'));
                    $res          = Db::name('admin_user')->where(['id' => $admin_id])->setField('password', $new_password);
                    if ($res !== false) {
                        $this->success('修改成功');
                    } else {
                        $this->error('修改失败');
                    }
                } else {
                    $this->error('两次密码输入不一致');
                }
            } else {
                $this->error('原密码不正确');
            }
        }
    }
}