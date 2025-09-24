<?php
namespace app\admin\controller;

use app\common\controller\AdminBase;
use think\Db;
use think\facade\Config;
use think\facade\Cache;
use think\facade\Session;

class Rule extends AdminBase
{
	protected $taskgroup_level_list;
	protected $group_arr;
	protected $chanel_arr;
	protected $project_arr;
	
    protected function initialize()
    {
        parent::initialize();
		// 分组信息
		$group_list        = $this->task_group_model->where(array('status'=>1,'is_show'=>1))->order(['sort' => 'ASC', 'id' => 'ASC'])->select();
		foreach($group_list as $key=>$val)
		{
			$this->group_arr[$val['id']] = $val;
		}
		$group_level_list  = array2level($group_list);
        $this->assign('group_level_list', $group_level_list);
		
		// 渠道信息
		$chanel_list = $this->chanel_model->where(array('status'=>1))->select();
		foreach($chanel_list as $key=>$val)
		{
			$this->chanel_arr[$val['id']] = $val;
		}
		
        // 包信息
		$project_list = $this->project_model->select();
		foreach($project_list as $key=>$val)
		{
			$this->project_arr[$val['id']] = $val;
		}
		
		
		// 顶部导航栏信息
		$this->tabs = array(
			'index'=>array(
				'name'=>'分配规则',
				'url'=>url('admin/'.$this->controller.'/index'),
				'url_str' =>'admin/'.$this->controller.'/index',
				'status'=>false
			),
			'add'=>array(
				'name'=>'添加规则',
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
		if(isset($param['type'])&&$param['type']>0)
		{
			$map[] = ['s.type','=',$param['type']];
		}else{
			$param['type'] = 0;
		}
		
		if(isset($param['page'])&&$param['page']>0){
			$page = $param['page'];
		}else{
			$page = 0;
		}
		
        $list = $this->setrule_model->alias('s')->join('DeviceInfo c','s.device_id=c.id','left')->where($map)->field('s.*,c.name as devicename')->order('s.create_time DESC')->paginate(15, false, ['page' => $page]);
		foreach($list as $key=>$val)
		{
			if( isset($this->chanel_arr[$val['item_id']]) && $val['type']==1)
			{
				$list[$key]['type'] = '渠道';
				$list[$key]['name'] = $this->chanel_arr[$val['item_id']]['outname'];
			}
			elseif( isset($this->group_arr[$val['item_id']]) && $val['type']==2)
			{
				$list[$key]['type'] = '分组';
				$list[$key]['name'] = $this->group_arr[$val['item_id']]['name'];
			}elseif(isset($this->project_arr[$val['item_id']]) && $val['type']==3){
				$list[$key]['type'] = '包';
				$list[$key]['name'] = $this->project_arr[$val['item_id']]['name'];
			}else{
				unset($list[$key]);
			}
		}
		
		$this->tabs['index']['status'] = true;
		$this->assign('tabs',$this->tabs);
        return $this->fetch('index', ['list' => $list,'type'=>$param['type']]);
    }
	
    public function add()
    {
		$device_list = $this->device_info_model->select();
		$project_list = $this->project_model->select();
		$this->assign('device_list',$device_list);
		$this->assign('project_list',$project_list);
		$this->tabs['add']['status'] = true;	
		$this->assign('tabs',$this->tabs);
        return $this->fetch('add',['uploadurl'=>url('api/upload/uploadapk')]);
    }
	
    public function save()
    {
        if ($this->request->isPost()) {
            $data            = $this->request->post();
			$save_data = array();
			$save_data['type'] = $data['type'];
			$save_data['device_id'] = $data['device_id'];
			if($data['type']==1){
				$save_data['item_id'] = $data['chanel_id'];
			}else if($data['type']==2){
				$save_data['item_id'] = $data['group_id'];
			}else{
				$save_data['item_id'] = $data['project_id'];
			}
			// 判断是否存在规则
			$sta = $this->setrule_model->where(array('item_id'=>$save_data['item_id'],'type'=>$save_data['type']))->find();
			if($sta)
			{
				$this->error('该分组或渠道的规则已存在！'); 
			}
			$save_data['create_time'] = time();
			if ($this->setrule_model->allowField(true)->save($save_data)) {
				$this->success('保存成功');
			} else {
				$this->error('保存失败');
			}
        }
    }
	
	public function select()
	{
		if ($this->request->isPost()) {
            $data            = $this->request->post();
			$list = $this->chanel_model->where(array('group_id'=>$data['id'],'status'=>1))->select();
			echo  json_encode($list);
			die;
        }
	}
	
	public function delete($id)
	{
		if ($this->setrule_model->destroy($id)) {
            $this->success('删除成功');
        } else {
            $this->error('删除失败');
        }
	}
}