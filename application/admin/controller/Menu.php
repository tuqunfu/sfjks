<?php
namespace app\admin\controller;
use app\common\controller\AdminBase;
use think\Db;
use think\facade\Config;
use think\facade\Cache;
use think\facade\Session;

class Menu extends AdminBase
{
    protected function initialize()
    {
        parent::initialize();
        $admin_menu_list       = $this->auth_rule_model->order(['sort' => 'DESC', 'id' => 'ASC'])->select();
        $admin_menu_level_list = array2level($admin_menu_list);
        $this->assign('admin_menu_level_list', $admin_menu_level_list);
		$this->controller = $this->request->controller();
		// 顶部导航栏信息
		$this->tabs = array(
			'index'=>array(
				'name'=>'菜单管理',
				'url'=>url('admin/'.$this->controller.'/index'),
				'url_str' =>'admin/'.$this->controller.'/index',
				'status'=>false
			),
			'add'=>array(
				'name'=>'添加菜单',
				'url'=>url('admin/'.$this->controller.'/add'),
				'url_str' =>'admin/'.$this->controller.'/add',
				'status'=>false
			)
		);
    }

    public function index()
    {
		$this->tabs['index']['status'] = true;
		$this->assign('tabs',$this->tabs);
        return $this->fetch();
    }

    public function add($pid = '')
    {
		$this->tabs['add']['status'] = true;	
		$this->assign('tabs',$this->tabs);
        return $this->fetch('add', ['pid' => $pid]);
    }

    public function save()
    {
        if ($this->request->isPost()) {
            $data            = $this->request->post();
            $validate_result = $this->validate($data, 'Menu');

            if ($validate_result !== true) {
                $this->error($validate_result);
            } else {
                if ($this->auth_rule_model->save($data)) {
                    $this->success('保存成功');
                } else {
                    $this->error('保存失败');
                }
            }
        }
    }

    public function edit($id)
    {
        $admin_menu = $this->auth_rule_model->find($id);
		$this->tabs['edit'] = array(
				'name'=>'编辑菜单',
				'url' => url('admin/'.$this->controller.'/edit'),
				'url_str' =>'admin/'.$this->controller.'/edit',
				'status'=>true
		);
		$this->assign('tabs',$this->tabs);
		$this->assign('btns',$this->btns);
        return $this->fetch('edit', ['admin_menu' => $admin_menu]);
    }

    public function update()
    {
        if ($this->request->isPost()) {
            $data            = $this->request->post();
            $validate_result = $this->validate($data, 'Menu');

            if ($validate_result !== true) {
                $this->error($validate_result);
            } else {
                if ($this->auth_rule_model->save($data, $data['id']) !== false) {
                    $this->success('更新成功');
                } else {
                    $this->error('更新失败');
                }
            }
        }
    }

    public function delete($id)
    {
        $sub_menu = $this->auth_rule_model->where(['pid' => $id])->find();
        if (!empty($sub_menu)) {
            $this->error('此菜单下存在子菜单，不可删除');
        }
        if ($this->auth_rule_model->destroy($id)) {
            $this->success('删除成功');
        } else {
            $this->error('删除失败');
        }
    }
}