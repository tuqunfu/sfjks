<?php
namespace app\admin\controller;
use app\common\controller\AdminBase;
use think\facade\Config;
use think\facade\Cache;
use think\facade\Session;
use think\Db;

class Membership extends AdminBase
{
    protected function initialize()
    {
        parent::initialize();
		$this->tabs = array(
			'index'=>array(
				'name'=>'会员设置',
				'url'=>url('admin/'.$this->controller.'/index'),
				'url_str' =>'admin/'.$this->controller.'/index',
				'status'=>false
			),
			'add'=>array(
				'name'=>'添加设置',
				'url'=>url('admin/'.$this->controller.'/add'),
				'url_str' =>'admin/'.$this->controller.'/add',
				'status'=>false
			)
		);
    }

    public function index()
    {
        $membership_plan_list = $this->membership_plan_model->select();
		$this->tabs['index']['status'] = true;
		$this->assign('tabs',$this->tabs);
        return $this->fetch('index', ['membership_plan_list' => $membership_plan_list]);
    }

    public function add()
    {
		$this->tabs['add']['status'] = true;	
		$this->assign('tabs',$this->tabs);
		$this->assign('btns',$this->btns);
        return $this->fetch('add',['uploadurl' => url('api/upload/upload_font')]);
    }

    public function save()
    {
        if ($this->request->isPost()) {
            $data            = $this->request->param();
            $validate_result = $this->validate($data, 'MembershipPlan');
            if ($validate_result !== true) {
                $this->error($validate_result);
            } else {
                $data['created_at'] = date('Y-m-d H:i:s');
                if ($this->membership_plan_model->allowField(true)->save($data)) {
                    $this->success('保存成功');
                } else {
                    $this->error('保存失败');
                }
            }
        }
    }

    public function edit($id)
    {
        $membership_plan = $this->membership_plan_model->find($id);
		$this->tabs['edit'] = array(
				'name'=>'编辑设置',
				'url' => url('admin/'.$this->controller.'/edit'),
				'url_str' =>'admin/'.$this->controller.'/edit',
				'status'=>true
		);
		$this->assign('tabs',$this->tabs);
		$this->assign('btns',$this->btns);
        return $this->fetch('edit', ['membership_plan' => $membership_plan,'uploadurl' => url('api/upload/upload_font')]);
    }

    public function update()
    {
        if ($this->request->isPost()) {
            $data            = $this->request->param();
            $validate_result = $this->validate($data, 'MembershipPlan');

            if ($validate_result !== true) {
                $this->error($validate_result);
            } else {
                $membership_plan = $this->font_category_model->find($data['id']);
                $membership_plan->id       = $data['id'];
				$membership_plan->name       = $data['name'];
				$membership_plan->duration_days  = $data['duration_days'];
				$membership_plan->price     = $data['price'];
				$membership_plan->description       = $data['description'];
                $membership_plan->sort_order = $data['sort_order'];
				$membership_plan->is_active = $data['is_active'];
                if ($membership_plan->save() !== false) {
                    $this->success('更新成功');
                } else {
                    $this->error('更新失败');
                }
            }
        }
    }

    public function delete($id)
    {
        if ($this->membership_plan_model->destroy($id)) {
            $this->success('删除成功');
        } else {
            $this->error('删除失败');
        }
    }
}