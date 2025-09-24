<?php
namespace app\admin\controller;
use app\common\controller\AdminBase;
use think\facade\Config;
use think\facade\Cache;
use think\facade\Session;
use think\Db;
class AdminUser extends AdminBase
{
    protected function initialize()
    {
        parent::initialize();
		$this->tabs = array(
			'index'=>array(
				'name'=>'管理员',
				'url'=>url('admin/'.$this->controller.'/index'),
				'url_str' =>'admin/'.$this->controller.'/index',
				'status'=>false
			),
			'add'=>array(
				'name'=>'添加管理员',
				'url'=>url('admin/'.$this->controller.'/add'),
				'url_str' =>'admin/'.$this->controller.'/add',
				'status'=>false
			)
		);
    }

    public function index()
    {
        $admin_user_list = $this->admin_user_model->select();
		$this->tabs['index']['status'] = true;
		$this->assign('tabs',$this->tabs);
        return $this->fetch('index', ['admin_user_list' => $admin_user_list]);
    }

    public function add()
    {
        $auth_group_list = $this->auth_group_model->select();
		$this->tabs['add']['status'] = true;	
		$this->assign('tabs',$this->tabs);
		$this->assign('btns',$this->btns);
        return $this->fetch('add', ['auth_group_list' => $auth_group_list]);
    }

    public function save()
    {
        if ($this->request->isPost()) {
            $data            = $this->request->param();
            $validate_result = $this->validate($data, 'AdminUser');
            if ($validate_result !== true) {
                $this->error($validate_result);
            } else {
                $data['password'] = md5($data['password'] . Config::get('salt'));
                if ($this->admin_user_model->allowField(true)->save($data)) {
                    $auth_group_access['uid']      = $this->admin_user_model->id;
                    $auth_group_access['group_id'] = $data['group_id'];
                    $this->auth_group_access_model->save($auth_group_access);
                    $this->success('保存成功');
                } else {
                    $this->error('保存失败');
                }
            }
        }
    }

    public function edit($id)
    {
        $admin_user             = $this->admin_user_model->find($id);
        $auth_group_list        = $this->auth_group_model->select();
        $auth_group_access      = $this->auth_group_access_model->where('uid', $id)->find();
        $admin_user['group_id'] = $auth_group_access['group_id'];
		$this->tabs['edit'] = array(
				'name'=>'编辑管理员',
				'url' => url('admin/'.$this->controller.'/edit'),
				'url_str' =>'admin/'.$this->controller.'/edit',
				'status'=>true
		);
		$this->assign('tabs',$this->tabs);
		$this->assign('btns',$this->btns);
        return $this->fetch('edit', ['admin_user' => $admin_user, 'auth_group_list' => $auth_group_list]);
    }

    public function update()
    {
        if ($this->request->isPost()) {
            $data            = $this->request->param();
            $validate_result = $this->validate($data, 'AdminUser');

            if ($validate_result !== true) {
                $this->error($validate_result);
            } else {
                $admin_user = $this->admin_user_model->find($data['id']);
                $admin_user->id       = $data['id'];
                $admin_user->username = $data['username'];
                $admin_user->status   = $data['status'];

                if (!empty($data['password']) && !empty($data['confirm_password'])) {
                    $admin_user->password = md5($data['password'] . Config::get('salt'));
                }
                if ($admin_user->save() !== false) {
                    $auth_group_access['uid']      = $data['id'];
                    $auth_group_access['group_id'] = $data['group_id'];
                    $this->auth_group_access_model->where('uid', $data['id'])->update($auth_group_access);
                    $this->success('更新成功');
                } else {
                    $this->error('更新失败');
                }
            }
        }
    }

    public function delete($id)
    {
        if ($id == 1) {
            $this->error('默认管理员不可删除');
        }
        if ($this->admin_user_model->destroy($id)) {
            $this->auth_group_access_model->where('uid', $id)->delete();
            $this->success('删除成功');
        } else {
            $this->error('删除失败');
        }
    }
}