<?php
namespace app\admin\controller;
use app\common\controller\AdminBase;
use think\Db;
use think\facade\Config;
use think\facade\Cache;
use think\facade\Session;

class Group extends AdminBase
{
    protected function initialize()
    {
        parent::initialize();
        $group_list        = $this->task_group_model->where(array('is_show'=>1))->order(['sort' => 'ASC', 'id' => 'ASC'])->select();
		$parent_group_list        = $this->task_group_model->where(array('pid'=>0))->order(['sort' => 'ASC', 'id' => 'ASC'])->select();
		$groups = array();
		foreach($group_list as $key=>$val)
		{
			$groups[$val['id']] = $val;
		}
		foreach($group_list as $key=>$val)
		{
			if($val['next_id']>0){
				$group_list[$key]['next_name']  = $groups[$val['next_id']]['name'];
			}else{
				$group_list[$key]['next_name'] =  '';
			}
			
		}
        $group_level_list  = array2level($group_list);
        $this->assign('group_level_list', $group_level_list);
        $this->assign('parent_group_level_list', $parent_group_list);
		// 顶部导航栏信息
		$this->tabs = array(
			'index'=>array(
				'name'=>'分组管理',
				'url'=>url('admin/'.$this->controller.'/index'),
				'url_str' =>'admin/'.$this->controller.'/index',
				'status'=>false
			),
			'add'=>array(
				'name'=>'添加分组',
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
        return $this->fetch('add', ['pid' => $pid,'next_id'=>0]);
    }

    public function save()
    {
        if ($this->request->isPost()) {
            $data            = $this->request->post();
            $validate_result = $this->validate($data, 'TaskGroup');
			if(  !($data['three_stage']<=1000 && $data['three_stage']>=0)  || !($data['one_stage']<=1000 && $data['one_stage']>=0) || !($data['two_stage']<=1000 && $data['two_stage']>=0)){
				 $this->error('激活概率必须0-1000整数！');
			}
			
            if ($validate_result !== true) {
                $this->error($validate_result);
            } else {
                if ($this->task_group_model->save($data)) {
                    $this->success('保存成功');
                } else {
                    $this->error('保存失败');
                }
            }
        }
    }

    public function edit($id)
    {
        $taskgroup = $this->task_group_model->find($id);
		$list = $this->chanel_model->where(array('group_id'=>$taskgroup['id']))->select();
		$this->tabs['edit'] = array(
				'name'=>'编辑分组',
				'url' => url('admin/'.$this->controller.'/edit'),
				'url_str' =>'admin/'.$this->controller.'/edit',
				'status'=>true
		);
		$this->assign('tabs',$this->tabs);
        return $this->fetch('edit', ['taskgroup' => $taskgroup,'chanels'=>$list]);
    }

    /**
     * 更新分组
     * @param $id
     */
    public function update()
    {
        if ($this->request->isPost()) {
			$data            = $this->request->post();
            $validate_result = $this->validate($data, 'TaskGroup');
			if(  !($data['three_stage']<=1000 && $data['three_stage']>=0)  || !($data['one_stage']<=1000 && $data['one_stage']>=0) || !($data['two_stage']<=1000 && $data['two_stage']>=0)){
				 $this->error('激活概率必须0-1000整数！');
			}
            if ($validate_result !== true) {
                $this->error($validate_result);
            } else {
                if ($this->task_group_model->save($data, $data['id']) !== false) {
                    $this->success('更新成功');
                } else {
                    $this->error('更新失败');
                }
            }
        }
    }
	
    public function delete($id)
    {
		$sta = $this->chanel->where(array('group_id'=>$id))->select();
		if(count($sta)==0)
		{
			$st = $this->task_group_model->where(array('pid'=>$id))->select();
			if(count($st)==0)
			{
				if ($this->task_group_model->destroy($id)) {
					$this->success('删除成功');
				} else {
					$this->error('删除失败');
				}
			}
			else
			{
				$this->error('分组存在子分组信息，无法删除！');
			}
		}
		else
		{
			$this->error('渠道已选择该分组信息，无法删除！');
		}
    }
	
	public function set($id)
	{
		$list = $this->group_setting_model->where(array('group_id'=>$id))->select();
		$this->tabs['edit'] = array(
				'name'=>'设置时间',
				'url' => url('admin/'.$this->controller.'/set'),
				'url_str' =>'admin/'.$this->controller.'/set',
				'status'=>true
		);
		$this->assign('tabs',$this->tabs);
		$this->assign('id',$id);
		return $this->fetch('set',['list' => $list]);
	}
	
	public function saveset()
	{
		if ($this->request->isPost()) {
			$data            = $this->request->post();
			$save_data = array();
			for($i=0;$i<count($data['hours']);$i++)
			{
				$temp['hours'] = $data['hours'][$i];
				$temp['day'] = $data['day'][$i];
				$temp['group_id'] = $data['id'];
				$save_data[] = $temp;
			}
			$sta = $this->group_setting_model->where(array('group_id'=>$data['id']))->delete();
			if(count($save_data)>0){
				$status = $this->group_setting_model->saveAll($save_data);
			}else{
				$status = true;
			}
			if($status){
				$this->error('保存成功！');
			}else{
				$this->error('保存失败！');
			}
		}
	}
}