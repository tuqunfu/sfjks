<?php
namespace app\admin\controller;

use app\common\controller\AdminBase;
use think\Db;
use think\facade\Config;
use think\facade\Cache;
use think\facade\Session;

class ToolBranchPkg extends AdminBase
{
    protected function initialize()
    {
        parent::initialize();
		$this->tabs = array(
			'index'=>array(
				'name'=>'分支包管理',
				'url'=>url('admin/'.$this->controller.'/index'),
				'url_str' =>'admin/'.$this->controller.'/index',
				'status'=>false
			),
			'add'=>array(
				'name'=>'添加分支包',
				'url'=>url('admin/'.$this->controller.'/add'),
				'url_str' =>'admin/'.$this->controller.'/add',
				'status'=>false
			)
		);
		
		$types = $this->tool_branch_pkg_model->field('type')->group('type')->select();
		$this->assign('types', $types);
		
		$pkgs = $this->toolpkg_model->field('id,pkg_name,title')->where(array('api_version'=>25))->select();
		$this->assign('pkgs', $pkgs);
    }
	
    public function index()
    {
		$map = [];
		$param =$this->request->get();
		if (isset($param['type'])&&$param['type']) {
			$map[] = ['tb.type','like', '%'.$param['type'].'%'];
        }else{
			$param['type'] = '';
		}
		
		if (isset($param['tool_id'])&&$param['tool_id']) {
			$map[] = ['tb.tool_id','=',$param['tool_id']];
        }else{
			$param['tool_id'] = 0;
		}
		
        $toolpkg_list = $this->tool_branch_pkg_model->alias('tb')
							 ->join('ToolPkg tp','tb.tool_id = tp.id','left')
							 ->field('tb.*,tp.pkg_name,tp.title')
							 ->where($map)
							 ->order('tb.type ASC')
							 ->paginate(15, false,['query' => ['type'=>$param['type'],'tool_id'=>$param['tool_id'] ]]);
		
		
		$this->tabs['index']['status'] = true;
		$this->assign('tabs',$this->tabs);
        return $this->fetch('index', ['toolpkg_list' => $toolpkg_list, 'uploadurl' => url('api/upload/uploadso'),'type'=>$param['type'],'tool_id'=>$param['tool_id'] ]);
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
            $validate_result = $this->validate($data, 'ToolBranchPkg');
			
			$pkg = $this->toolpkg_model->where(array('id'=>$data['tool_id']))->find();
			if($pkg['app_version']>=$data['app_version'])
			{
				$this->error('分支版本必须大于主分支版本');
			}
			
			$res = $this->tool_branch_pkg_model->where(array('type'=>$data['type'],'tool_id'=>$data['tool_id']))->find();
			if($res){
				$this->error('分支版本已经存在');
			}
			
            if ($validate_result !== true) {
                $this->error($validate_result);
            } else {
				$data['create_time'] = time();
                if ($this->tool_branch_pkg_model->allowField(true)->save($data)) {
                    $this->success('保存成功');
                } else {
                    $this->error('保存失败');
                }
            }
        }
    }

    public function edit($id)
    {
        $toolpkg = $this->tool_branch_pkg_model->find($id);
		$this->tabs['edit'] = array(
				'name'=>'编辑分支包',
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
            $validate_result = $this->validate($data, 'ToolBranchPkg');
            if ($validate_result !== true) {
                $this->error($validate_result);
            } else {
				
				$pkg = $this->toolpkg_model->where(array('id'=>$data['tool_id']))->find();
				if($pkg['app_version']>=$data['app_version'])
				{
					$this->error('分支版本必须大于主分支版本');
				}
				
                $tool_branch_pkg           = $this->tool_branch_pkg_model->find($data['id']);
                $tool_branch_pkg->id       = $data['id'];
				$tool_branch_pkg->is_system = $data['is_system'];
				$tool_branch_pkg->sort = $data['sort'];
				$tool_branch_pkg->appfile = $data['appfile'];
				$tool_branch_pkg->app_version = $data['app_version'];
				$tool_branch_pkg->tool_id = $data['tool_id'];
				$tool_branch_pkg->type = $data['type'];
                if ($tool_branch_pkg->save() !== false) {
                    $this->success('更新成功');
                } else {
                    $this->error('更新失败');
                }
            }
        }
    }

    public function delete($id)
    {
        if ($this->tool_branch_pkg_model->destroy($id)) {
            $this->success('删除成功');
        } else {
            $this->error('删除失败');
        }
    }
}