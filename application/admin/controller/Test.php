<?php
namespace app\admin\controller;

use app\common\controller\AdminBase;
use think\Db;
use think\facade\Config;
use think\facade\Cache;
use think\facade\Session;

class Test extends AdminBase
{
    protected function initialize()
    {
        parent::initialize();
		// 顶部导航栏信息
		$this->tabs = array(
			'index'=>array(
				'name'=>'测试管理',
				'url'=>url('admin/'.$this->controller.'/index'),
				'url_str' =>'admin/'.$this->controller.'/index',
				'status'=>false
			)
		);
    }
	
    public function index()
    {
		$param = $this->request->get();
		if(isset($param['page'])){
			$page = $param['page'];
		}else{
			$page  = 1;
		}
		$map = [];
        if (isset($param['pkgname'])&&$param['pkgname']) {
			$map[] = ['p.pkgname', 'like', '%'.$param['pkgname'].'%'];
        }else{
			$param['pkgname'] = '';
		}
		
		//
		$ormap = [];
		if(isset($param['pkgname']) && $param['pkgname']){
			$ormap[] = ['p.name','like', '%'.$param['pkgname'].'%'];
		}
		
        $project_list = $this->project_model->alias('p')
							->join('chanel c','p.channel_id=c.id','left')
							->field('p.pkgname,p.name,c.outname,p.id,p.channel_id,c.id as cid,c.group_id')
							->where($map)
							->whereOr($ormap)
							->order('p.id DESC')
							->paginate(15, false, ['page' => $page]);
		
		foreach($project_list as $k=>$v)
		{
			if($v['cid']){
				$info = $this->setrule_model->where(['type'=>1,'item_id'=>$v['cid']])->find();
				if($info == null){
					$info = $this->setrule_model->where(['type'=>2,'item_id'=>$v['group_id']])->find();
					if($info == null){
						$info = $this->setrule_model->where(['type'=>3,'item_id'=>$v['id']])->find();
					}
				}
				$dinfo = null;
				if($info != null){
					$dinfo = $this->device_info_model->where(['id'=>$info['device_id']])->find();
				}
				if($dinfo!=null){
					$project_list[$k]['rulename'] = $dinfo['name'];
				}else{
					$project_list[$k]['rulename'] = '';
				}
			}else{
				$project_list[$k]['rulename'] = '';
			}
		}
		
		$this->tabs['index']['status'] = true;
		$this->assign('tabs',$this->tabs);
        return $this->fetch('test', ['project_list' => $project_list, 'pkgname' =>$param['pkgname']]);
    }
	
	public function update()
	{
		if ($this->request->isPost()) {
			$data            = $this->request->post();
			$re = $this->project_model->where(array('id'=>$data['project_id']))->update(array('channel_id'=>$data['id']));
			if($re===false)
			{
				$this->success('更新失败');
			}
			else
			{
				$this->success('更新成功');
			}
		}
	}
	
	public function edit($project_id)
	{
		$this->tabs['eidt'] = array(
				'name'=>'测试编辑',
				'url'=>url('admin/'.$this->controller.'/edit'),
				'url_str' =>'admin/'.$this->controller.'/edit',
				'status'=>true
		);
		
		$project = $this->project_model->where(array('id'=>$project_id))->find();
		
		$channel_list = $this->chanel_model->alias('a')
							->join('task_group b','a.group_id = b.id')
							->join('project c','a.pkgname = c.pkgname')
							->field('a.id,a.outname,a.group_id,b.name as group_name,c.id as pkgid')
							->where(array('a.project_id'=>$project_id,'a.status'=>1))->paginate(15, false);
		foreach($channel_list as $k=>&$v)
		{
			$info = $this->setrule_model->where(['type'=>1,'item_id'=>$v['id']])->find();
			if($info == null){
				$info = $this->setrule_model->where(['type'=>2,'item_id'=>$v['group_id']])->find();
				if($info == null){
					$info = $this->setrule_model->where(['type'=>3,'item_id'=>$v['pkgid']])->find();
				}
			}
			
			if($info != null){
				$dinfo = $this->device_info_model->where(['id'=>$info['device_id']])->find();
				if($dinfo != null){
					$channel_list[$k]['rulename'] = $dinfo['name'];
				}else{
					$channel_list[$k]['rulename'] = '';
				}
			}else{
				$channel_list[$k]['rulename'] = '';
			}
			
			if($v['id']==$project['channel_id'])
			{
				$v['is_select'] = 1;
			}
			else
			{
				$v['is_select'] = 0;
			}
		}
		$this->assign('tabs',$this->tabs);
		return $this->fetch('edit', ['channel_list' => $channel_list,'project_id'=>$project_id]);
	}
	
	public function getChannels()
	{
		if ($this->request->isPost()) {
			$data            = $this->request->post();
			$channels = $this->chanel_model->where(array('project_id'=>$data['project_id'],'status'=>1))->select();
			echo json_encode($channels);
		}
	}
}