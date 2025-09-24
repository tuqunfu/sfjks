<?php
namespace app\admin\controller;
use app\common\controller\AdminBase;
use think\facade\Config;
use think\facade\Cache;
use think\facade\Session;
use think\Db;

class SystemLog extends AdminBase
{
    protected function initialize()
    {
        parent::initialize();
		$this->tabs = array(
			'index'=>array(
				'name'=>'记录管理',
				'url'=>url('admin/'.$this->controller.'/index'),
				'url_str' =>'admin/'.$this->controller.'/index',
				'status'=>false
			)
		);
    }

    public function index()
    {
        $map = [];
		$param =$this->request->get();
        if (isset($param['keyword'])&&$param['keyword']) {
			$map[] = ['record','like', '%'.$param['keyword'].'%'];
        }else{
			$param['keyword'] = '';
		}
		
		if(isset($param['cid'])&&$param['cid']>0){
			$map[] = ['type','=',$param['cid']];
		}else{
			$param['cid'] = 0;
		}
		
		if(isset($param['page'])){
			$param['page'] = $param['page'];
		}else{
			$param['page']  = 1;
		}
		
        $user_list = $this->system_log_model->where($map)->order('id DESC')->paginate(15, false,['query' => ['keyword'=>$param['keyword'],'cid'=>$param['cid']]]);
		$this->tabs['index']['status'] = true;
		$this->assign('tabs',$this->tabs);
        return $this->fetch('index', ['user_list' => $user_list, 'keyword' => $param['keyword'],'cid'=>$param['cid']]);
    }
}