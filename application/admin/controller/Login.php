<?php
namespace app\admin\controller;

use think\facade\Config;
use think\facade\Cache;
use think\facade\Session;
use think\Controller;
use think\Db;

class Login extends Controller
{
    public function index()
    {
        return $this->fetch();
    }

    public function login()
    {
        if ($this->request->isPost()) {
            $data            = $this->request->only(['username', 'password', 'verify']);
            $validate_result = $this->validate($data, 'Login');

            if ($validate_result !== true) {
                $this->error($validate_result);
            } else {
                $where['a.username'] = $data['username'];
                $where['a.password'] = md5($data['password'].config('application.salt'));
                $admin_user = Db::name('admin_user')
									->alias('a')->join('auth_group_access t','a.id=t.uid')
									->field('a.id,a.username,a.status,t.group_id')
									->where($where)
									->find();
				
                if (!empty($admin_user)) {
                    if ($admin_user['status'] != 1) {
                        $this->error('当前用户已禁用');
                    } else {
                        Session::set('admin_id', $admin_user['id']);
                        Session::set('admin_name', $admin_user['username']);
						Session::set('admin_user', $admin_user);
                        Db::name('admin_user')->update(
                            [
                                'l_time' =>  time(),
                                'last_login_ip'   => $this->request->ip(),
                                'id'              => $admin_user['id']
                            ]
                        );
						$slog = array();
						$slog['type_id'] = 2;
						$slog['admin_id'] = $admin_user['id'];
						$slog['module'] = 'Login';
						$slog['method'] = 'Login/login';
						$slog['c_time'] = time();
						$slog['ip'] = $this->request->ip();
						$slog['record'] = '登录系统';
						Db::name('system_log')->insert($slog);
                        $this->success('登录成功', 'admin/index/index');
                    }
                } else {
                    $this->error('用户名或密码错误');
                }
            }
        }
    }
	
    public function logout()
    {
        Session::delete('admin_id');
        Session::delete('admin_name');
        $this->success('退出成功', 'admin/login/index');
    }
}
