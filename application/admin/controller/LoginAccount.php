<?php
namespace app\admin\controller;

use app\common\controller\AdminBase;
use think\facade\Config;
use think\facade\Cache;
use think\facade\Session;
use think\Db;

class LoginAccount extends AdminBase
{
    protected function initialize()
    {
        parent::initialize();
		$this->tabs = array(
			'index'=>array(
				'name'=>'账号管理',
				'url'=>url('admin/'.$this->controller.'/index'),
				'url_str' =>'admin/'.$this->controller.'/index',
				'status'=>false
			),
			'add'=>array(
				'name'=>'添加账号',
				'url'=>url('admin/'.$this->controller.'/add'),
				'url_str' =>'admin/'.$this->controller.'/add',
				'status'=>false
			)
		);
    }

    public function index()
    {
		$map = [];
		$param =$this->request->get();
		if (isset($param['name'])&&$param['name']) {
			$map[] = ['name','like', '%'.$param['name'].'%'];
        }else{
			$param['name'] = '';
		}
		
		if(isset($param['status'])&&$param['status']>0){
			$map[] = ['status','=',$param['status']];
		}else{
			$param['status'] = 0;
		}
		
		if(isset($param['type'])&&$param['type']>0){
			$map[] = ['type','=',$param['type']];
		}else{
			$param['type'] = 0;
		}
		
		$login_account_list = $this->login_account_model->where($map)->order('create_time DESC')->paginate(15, false, ['query' => ['name'=>$param['name'],'status'=>$param['status'],'type'=>$param['type']]]);
		foreach($login_account_list as $key=>&$val)
		{
			if($val['type']==1)
			{
				$val['type'] = '微信';
			}
			
			if($val['type']==2)
			{
				$val['type'] = 'QQ';
			}
			
			if($val['type']==3)
			{
				$val['type'] = '九游';
			}
		}
		$this->tabs['index']['status'] = true;
		$this->assign('tabs',$this->tabs);
        return $this->fetch('index',['login_account_list'=>$login_account_list,'name'=>$param['name'],'status'=>$param['status'],'type'=>$param['type']]);
    }
	
	
	
	public function add()
    {
		$this->tabs['add']['status'] = true;	
		$this->assign('tabs',$this->tabs);
        return $this->fetch('add');
    }

    public function save()
    {
        if ($this->request->isPost()) {
            $data            = $this->request->param();
            $validate_result = $this->validate($data, 'LoginAccount');
			$data['create_time'] = time();
			$data['status'] = 1;
			
            if ($validate_result !== true) {
                $this->error($validate_result);
            } else {
                if ($this->login_account_model->allowField(true)->save($data)) {
                    $this->success('保存成功',url('admin/'.$this->controller.'/index'));
                } else {
                    $this->error('保存失败');
                }
            }
        }
    }

    public function edit($id)
    {
        $login_account  = $this->login_account_model->find($id);
		$this->tabs['edit'] = array(
			'name'=>'编辑账号',
			'url' => url('admin/'.$this->controller.'/edit'),
			'url_str' =>'admin/'.$this->controller.'/edit',
			'status'=>true
		);
		$this->assign('tabs',$this->tabs);
        return $this->fetch('edit', ['login_account' => $login_account]);
    }

    public function update()
    {
        if ($this->request->isPost()) {
            $data            = $this->request->param();
            $validate_result = $this->validate($data, 'LoginAccount');

            if ($validate_result !== true) {
                $this->error($validate_result);
            } else {
				$login_account           = $this->login_account_model->find($data['id']);
                $login_account->id       = $data['id'];
				$login_account->type = $data['type'];
				$login_account->name = $data['name'];
                $login_account->password = $data['password'];
				$login_account->status = $data['status'];
				$login_account->ext1 = $data['ext1'];
				$login_account->ext2 = $data['ext2'];
				$login_account->ext3 = $data['ext3'];
				
                if ($login_account->save() !== false) {
                    $this->success('更新成功');
                } else {
                    $this->error('更新失败');
                }
            }
        }
    }

    public function delete($id)
    {
		$where['id'] = $id;
		$sta = $this->login_account_model->where($where)->delete();
		if($sta){
			$this->success('删除成功');
		} else {
			$this->error('删除失败');
		}
    }
	
	
	//更改状态
	public function change()
	{
		if ($this->request->isPost()) {
			$data            = $this->request->post();
			if($data['status']==1){
				$update['status'] = 2;
			}else{
				$update['status'] = 1;
			}
			$sta = $this->login_account_model->where(array('id'=>$data['id']))->update($update);
			if($sta){
				$this->success('编辑成功！');
			}
			else{
				$this->error('编辑失败！');
			}
		}
	}
	
}