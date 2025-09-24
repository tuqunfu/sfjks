<?php
namespace app\admin\controller;
use app\common\controller\AdminBase;
use think\facade\Config;
use think\facade\Cache;
use think\facade\Session;
use think\Db;

class Project extends AdminBase
{
    protected function initialize()
    {
        parent::initialize();
		// 顶部导航栏信息
		$this->tabs = array(
			'index'=>array(
				'name'=>'产品管理',
				'url'=>url('admin/'.$this->controller.'/index'),
				'url_str' =>'admin/'.$this->controller.'/index',
				'status'=>false
			),
			'add'=>array(
				'name'=>'添加产品',
				'url'=>url('admin/'.$this->controller.'/add'),
				'url_str' =>'admin/'.$this->controller.'/add',
				'status'=>false
			)
		);
    }

    public function index()
    {
        $map = [];
		$param = $this->request->get();
		if (isset($param['pkgname'])&&$param['pkgname']) {
			$map[] = ['pkgname','like', '%'.$param['pkgname'].'%'];
        }else{
			$param['pkgname'] = '';
		}
		
		if(isset($param['page'])){
			$page = $param['page'];
		}else{
			$page  = 1;
		}
		
		$mapor = [];
		if (isset($param['pkgname'])&&$param['pkgname']) {
			$mapor[] = ['name','like', '%'.$param['pkgname'].'%'];
        }
		
        $project_list = $this->project_model->where($map)->whereOr($mapor)->order('id DESC')->paginate(15, false, ['page' => $page]);
		$this->tabs['index']['status'] = true;
		$this->assign('tabs',$this->tabs);
        return $this->fetch('index', ['project_list' => $project_list, 'pkgname' => $param['pkgname']]);
    }

    public function add($pkgname = '')
    {
		$user_list = $this->user_model->alias('a')->join('auth_group_access t','a.id=t.uid')->field('a.*')->where(array('t.group_id'=>3))->select();
		$this->tabs['add']['status'] = true;	
		$this->assign('tabs',$this->tabs);
        return $this->fetch('add',['user_list'=>$user_list,'uploadurl' => url('api/upload/uploadapk'),'group_id'=>$this->admin_user['group_id'], 'pkgname' => $pkgname ]);
    }
    
    public function save()
    {
        if ($this->request->isPost()) {
            $data            = $this->request->post();
            $validate_result = $this->validate($data, 'Project');
            if ($validate_result !== true) {
                $this->error($validate_result);
            } else {
				
				if($data['startday']<=1 || $data['startday']>= $data['olddays'] || $data['startday']>=$data['endday']){
					$this->error('开始天数不正确！');
				}
				if($data['endday']<=1 || $data['endday']>= $data['olddays'] || $data['startday']>=$data['endday']){
					$this->error('结束天数不正确！');
				}
				if($data['startper']<=0 || $data['startper']>=1 || $data['startper']<=$data['endper']){
					$this->error('开始系数不正确！');
				}
				if($data['endper']<=0 || $data['endper']>=1 || $data['startper']<=$data['endper']){
					$this->error('结束系数不正确！');
				}
				
				if(($data['active_num']>0 || $data['active_persent']>0) && $data['is_active']==0){
					$this->error('请勾选是否活跃');
				}
				
				if($data['is_active']==1) {
					if($data['active_day']>$data['olddays']) {
						$this->error('活跃天数不能大于留存天数');
					}
					
					if($data['active_day']<0) {
						$this->error('活跃天数必须大于0');
					}
				}
				
				if($data['is_script']==1){
					if($data['script_retained_time']<=0 || $data['script_activtion_time']<=0 || $data['script_random_r_num']<=0 || $data['script_random_a_num']<=0) {
						$this->error('脚本时长或随机数不能小于0');
					}
				}
				$data['extension']  = htmlspecialchars_decode($data['extension']);
				$data['accessJSON'] = htmlspecialchars_decode($data['accessJSON']);
				
				$temp = array();
				$presents = array();
				if(isset($data['time']))
				{
					for($i=0; $i< count($data['time']); $i++)
					{
						if($data['present'][$i]<=0)
						{
							$this->error('留存占比必须大于0');
						}
						
						if($data['time'][$i]>$data['olddays'])
						{
							$this->error('留存天数必须小于规定天数');
						}
						$temp['time'] = $data['time'][$i];
						$temp['present']  = $data['present'][$i];
						$presents[] = $temp;
					}
				}
				if(count($presents)>0){
					$data['presents'] = json_encode($presents);
				}else{
					$data['presents'] = '';
				}
				
				// 唤醒天数占比
				
				$awaken_ratio = array();
				if(isset($data['awaken_present']))
				{
					for($i=0;$i<count($data['awaken_present']);$i++)
					{
						$temp = [];
						if(!empty($data['awaken_present'][$i]))
						{
							$temp['awaken_starttime'] =   $data['awaken_starttime'][$i];
							$temp['awaken_endtime'] = $data['awaken_endtime'][$i];
							if($temp['awaken_starttime']<0)
							{
								$this->error('开始时间必须大于0');
							}
							if($temp['awaken_starttime']>=$temp['awaken_endtime'])
							{
								$this->error('结束时间必须大于开始时间');
							}
							$temp['awaken_present']   =   $data['awaken_present'][$i];
							if($temp['awaken_present']<=0)
							{
								$this->error('唤醒率必须大于0');
							}
							$awaken_ratio[]= $temp;
						}
					}
				}
				unset($data['awaken_starttime']);
				unset($data['awaken_endtime']);
				unset($data['awaken_present']);
				if(isset($awaken_ratio) && count($awaken_ratio)>0){
					$data['awaken_day_ratio'] = json_encode($awaken_ratio);
				}else{
					$data['awaken_day_ratio'] = '';
				}
				
                if ($this->project_model->allowField(true)->save($data)) {
                    $this->success('保存成功');
                } else {
                    $this->error('保存失败');
                }
            }
        }
    }
    
    public function edit($id)
    {
        $project = $this->project_model->find($id);
		$project['presents'] = json_decode($project['presents'],true);
		$project['awaken_day_ratio'] = json_decode($project['awaken_day_ratio'],true);
		
		$user_list = $this->user_model->alias('a')->join('auth_group_access t','a.id=t.uid')->field('a.*')->where(array('t.group_id'=>3))->select();
		$this->tabs['edit'] = array(
				'name'=>'编辑产品',
				'url' => url('admin/'.$this->controller.'/edit'),
				'url_str' =>'admin/'.$this->controller.'/edit',
				'status'=>true
		);
		$this->assign('tabs',$this->tabs);
        return $this->fetch('edit', ['project' => $project,'user_list'=>$user_list,'group_id'=>$this->admin_user['group_id'],'uploadurl' => url('api/upload/uploadapk')]);
    }
	
    public function update()
    {
        if ($this->request->isPost()) {
            $data            = $this->request->post();
			
			if($data['startday']<=1 || $data['startday']>= $data['olddays'] || $data['startday']>=$data['endday']){
				$this->error('开始天数不正确！');
			}
			if($data['endday']<=1 || $data['endday']>= $data['olddays'] || $data['startday']>=$data['endday']){
				$this->error('结束天数不正确！');
			}
			
			if($data['startper']<=0 || $data['startper']>=1 || $data['startper']<=$data['endper']){
				$this->error('开始系数不正确！');
			}
			
			if($data['endper']<=0 || $data['endper']>=1 || $data['startper']<=$data['endper']){
				$this->error('结束系数不正确！');
			}
			
			if(($data['active_num']>0 || $data['active_persent']>0) && $data['is_active']==0){
				$this->error('请勾选是否活跃');
			}
			
			if($data['is_active']==1) {
				if($data['active_day']>$data['olddays']) {
					$this->error('活跃天数不能大于留存天数');
				}
				
				if($data['active_day']<0) {
					$this->error('活跃天数必须大于0');
				}
			}
			
			if($data['is_script']==1){
				if($data['script_retained_time']<=0 || $data['script_activtion_time']<=0 || $data['script_random_r_num']<=0 || $data['script_random_a_num']<=0) {
					$this->error('脚本时长或随机数不能小于0');
				}
			}
			
            $validate_result = $this->validate($data, 'Project');

            if ($validate_result !== true) {
                $this->error($validate_result);
            } else {
                $project           = $this->project_model->find($data['id']);
                $project->id       = $data['id'];
				$project->runtype = $data['runtype'];
                $project->pkgname = $data['pkgname'];
				$project->name = $data['name'];
				$project->newmax = $data['newmax'];
				$project->oldmaxpersent = $data['oldmaxpersent'];
                $project->oldminpersent   = $data['oldminpersent'];
                $project->olddays    = $data['olddays'];
                $project->usevpn   = $data['usevpn'];
				$project->daytime     = $data['daytime'];
				$project->desc     = $data['desc'];
				$project->scriptfile     = $data['scriptfile'];
				$project->log_status     = $data['log_status'];
				$project->check_status     = $data['check_status'];
				$project->extension = htmlspecialchars_decode($data['extension']);
				$project->accessJSON = htmlspecialchars_decode($data['accessJSON']);
				$project->is_use_display = $data['is_use_display'];
				$project->activetime = $data['activetime'];
				$project->patch_version = $data['patch_version'];
				$project->is_balance = $data['is_balance'];
				$project->mobile_num = $data['mobile_num'];
				$project->mobile_max_num = $data['mobile_max_num'];
				// 留存系数
				$project->startday = $data['startday'];
				$project->endday =   $data['endday'];
				$project->startper = $data['startper'];
				$project->endper =   $data['endper'];
				// 活跃
				$project->active_num = $data['active_num'];
				$project->active_persent = $data['active_persent'];
				$project->active_day = $data['active_day'];
				$project->is_active = $data['is_active'];
				$project->isjni = $data['isjni'];
				$project->jniwebview = $data['jniwebview'];
				$project->is_chat = $data['is_chat'];
				$project->is_message = $data['is_message'];
				
				$project->is_script = $data['is_script'];
				$project->script_activtion_time = $data['script_activtion_time'];
				$project->script_retained_time = $data['script_retained_time'];
				$project->script_random_a_num = $data['script_random_a_num'];
				$project->script_random_r_num = $data['script_random_r_num'];
				
				$project->retainedtime = $data['retainedtime'];
				
				$temp = array();
				$presents = array();
				if(isset($data['time']))
				{
					for($i=0;$i<count($data['time']);$i++)
					{
						if($data['present'][$i]<=0)
						{
							$this->error('留存占比必须大于0');
						}
						
						if($data['time'][$i]>$data['olddays'])
						{
							$this->error('留存天数必须小于规定天数');
						}
						$temp['time'] = $data['time'][$i];
						$temp['present']  = $data['present'][$i];
						$presents[] = $temp;
					}
				}
				if(isset($presents) && count($presents)>0){
					$project->presents = json_encode($presents);
				}else{
					$project->presents = '';
				}
				
				// 唤醒天数占比
				$awaken_ratio = array();
				if(isset($data['awaken_present']))
				{
					for($i=0;$i<count($data['awaken_present']);$i++)
					{
						$temp = [];
						if(!empty($data['awaken_present'][$i]))
						{
							$temp['awaken_starttime'] =   $data['awaken_starttime'][$i];
							$temp['awaken_endtime'] = $data['awaken_endtime'][$i];
							if($temp['awaken_starttime']<0)
							{
								$this->error('开始时间必须大于0');
							}
							if($temp['awaken_starttime']>=$temp['awaken_endtime'])
							{
								$this->error('结束时间必须大于开始时间');
							}
							$temp['awaken_present']   =   $data['awaken_present'][$i];
							if($temp['awaken_present']<=0)
							{
								$this->error('唤醒率必须大于0');
							}
							$awaken_ratio[]= $temp;
						}
					}
				}
				
				if(isset($awaken_ratio) && count($awaken_ratio)>0){
					$project->awaken_day_ratio = json_encode($awaken_ratio);
				}else{
					$project->awaken_day_ratio = '';
				}
				
                if ($project->save() !== false) {
                    $this->success('更新成功');
                } else {
                    $this->error('更新失败');
                }
            }
        }
    }
	
    public function delete($id)
    {
        if ($this->project_model->destroy($id)) {
            $this->success('删除成功');
        } else {
            $this->error('删除失败');
        }
    }
}