<?php
namespace app\admin\controller;

use app\common\controller\AdminBase;
use think\facade\Config;
use think\facade\Cache;
use think\facade\Session;

class AuthGroup extends AdminBase
{
    protected function initialize()
    {
        parent::initialize();
		// 顶部导航栏信息
		$this->tabs = array(
			'index'=>array(
				'name'=>'权限组',
				'url'=>url('admin/'.$this->controller.'/index'),
				'url_str' =>'admin/'.$this->controller.'/index',
				'status'=>false
			),
			'add'=>array(
				'name'=>'添加权限组',
				'url'=>url('admin/'.$this->controller.'/add'),
				'url_str' =>'admin/'.$this->controller.'/add',
				'status'=>false
			)
		);
    }

    public function index()
    {
        $auth_group_list = $this->auth_group_model->select();
		$this->tabs['index']['status'] = true;
		$this->assign('tabs',$this->tabs);
        return $this->fetch('index', ['auth_group_list' => $auth_group_list]);
    }

 
    public function add()
    {
		$this->tabs['add']['status'] = true;	
		$this->assign('tabs',$this->tabs);
		$this->assign('btns',$this->btns);
        return $this->fetch();
    }

 
    public function save()
    {
        if ($this->request->isPost()) {
            $data = $this->request->post();

            if ($this->auth_group_model->save($data) !== false) {
                $this->success('保存成功');
            } else {
                $this->error('保存失败');
            }
        }
    }


    public function edit($id)
    {
        $auth_group = $this->auth_group_model->find($id);
		$this->tabs['edit'] = array(
				'name'=>'编辑权限组',
				'url' => url('admin/'.$this->controller.'/edit'),
				'url_str' =>'admin/'.$this->controller.'/edit',
				'status'=>true
		);
		$this->assign('tabs',$this->tabs);
		$this->assign('btns',$this->btns);
        return $this->fetch('edit', ['auth_group' => $auth_group]);
    }

   
    public function update()
    {
        if ($this->request->isPost()) {
            $data = $this->request->post();

            if ($id == 1 && $data['status'] != 1) {
                $this->error('超级管理组不可禁用');
            }
            if ($this->auth_group_model->save($data, $data['id']) !== false) {
                $this->success('更新成功');
            } else {
                $this->error('更新失败');
            }
        }
    }

    public function delete($id)
    {
        if ($id == 1) {
            $this->error('超级管理组不可删除');
        }
        if ($this->auth_group_model->destroy($id)) {
            $this->success('删除成功');
        } else {
            $this->error('删除失败');
        }
    }


    public function auth($id)
    {
		$this->tabs['auth'] = array(
				'name'=>'授权',
				'url' => url('admin/'.$this->controller.'/auth'),
				'url_str' =>'admin/'.$this->controller.'/auth',
				'status'=>true
		);
		$this->assign('tabs',$this->tabs);
		$this->assign('btns',$this->btns);
        return $this->fetch('auth', ['id' => $id]);
    }

    public function getJson()
    {
		if ($this->request->isPost()){
			$data = $this->request->post();
			$id = $data['id'];
			$auth_group_data = $this->auth_group_model->find($id)->toArray();
			$auth_rules      = explode(',', $auth_group_data['rules']);
			$auth_rule_list  = $this->auth_rule_model->field('id,pid,title')->select();

			foreach ($auth_rule_list as $key => $value) {
				in_array($value['id'], $auth_rules) && $auth_rule_list[$key]['checked'] = true;
			}
			return $auth_rule_list;
		 }
    }

    public function updateAuthGroupRule()
    {
        if ($this->request->isPost()) {
			$data = $this->request->post();
            if ($data['id']) {
                $group_data['id']    = $data['id'];
                $group_data['rules'] = is_array($data['auth_rule_ids']) ? implode(',', $data['auth_rule_ids']) : '';

                if ($this->auth_group_model->save($group_data, $data['id']) !== false) {
                    $this->success('授权成功');
                } else {
                    $this->error('授权失败');
                }
            }
        }
    }
}