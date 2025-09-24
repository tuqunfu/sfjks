<?php
namespace app\admin\controller;

use app\common\controller\AdminBase;
use think\Db;
use think\facade\Config;
use think\facade\Cache;
use think\facade\Session;

class ToolPkg extends AdminBase
{
    protected function initialize()
    {
        parent::initialize();
		$this->tabs = array(
			'index'=>array(
				'name'=>'工具包管理',
				'url'=>url('admin/'.$this->controller.'/index'),
				'url_str' =>'admin/'.$this->controller.'/index',
				'status'=>false
			),
			'add'=>array(
				'name'=>'添加工具包',
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
		if (isset($param['title'])&&$param['title']) {
			$map[] = ['title','like', '%'.$param['title'].'%'];
        }else{
			$param['title'] = '';
		}
        $toolpkg_list = $this->toolpkg_model->where($map)->order('sort ASC')->paginate(15, false);
		$this->tabs['index']['status'] = true;
		$this->assign('tabs',$this->tabs);
        return $this->fetch('index', ['toolpkg_list' => $toolpkg_list, 'uploadurl' => url('api/upload/uploadso'),'title'=>$param['title']]);
    }
	
    public function add()
    {
		$this->tabs['add']['status'] = true;	
		$this->assign('tabs',$this->tabs);
        return $this->fetch('add',['uploadurl' => url('api/upload/uploadapk')]);
    }

    public function save()
    {
        if ($this->request->isPost()) {
            $data            = $this->request->post();
            $validate_result = $this->validate($data, 'ToolPkg');

            if ($validate_result !== true) {
                $this->error($validate_result);
            } else {
				$data['create_time'] = time();
                if ($this->toolpkg_model->allowField(true)->save($data)) {
                    $this->success('保存成功');
                } else {
                    $this->error('保存失败');
                }
            }
        }
    }

    public function edit($id)
    {
        $toolpkg = $this->toolpkg_model->find($id);
		$this->tabs['edit'] = array(
				'name'=>'编辑工具包',
				'url' => url('admin/'.$this->controller.'/edit'),
				'url_str' =>'admin/'.$this->controller.'/edit',
				'status'=>true
		);
		$this->assign('tabs',$this->tabs);
        return $this->fetch('edit', ['toolpkg' => $toolpkg, 'uploadurl' => url('api/upload/uploadapk')]);
    }
	
    public function update()
    {
        if ($this->request->isPost()) {
            $data            = $this->request->post();
            $validate_result = $this->validate($data, 'ToolPkg');
            if ($validate_result !== true) {
                $this->error($validate_result);
            } else {
                $toolpkg           = $this->toolpkg_model->find($data['id']);
                $toolpkg->id       = $data['id'];
				$toolpkg->is_system = $data['is_system'];
				$toolpkg->pkg_name = $data['pkg_name'];
                $toolpkg->title = $data['title'];
				$toolpkg->sort = $data['sort'];
				$toolpkg->appfile = $data['appfile'];
				$toolpkg->app_version = $data['app_version'];
				$toolpkg->api_version = $data['api_version'];
                if ($toolpkg->save() !== false) {
                    $this->success('更新成功');
                } else {
                    $this->error('更新失败');
                }
            }
        }
    }

    public function delete($id)
    {
        if ($this->toolpkg_model->destroy($id)) {
            $this->success('删除成功');
        } else {
            $this->error('删除失败');
        }
    }
}