<?php
namespace app\admin\controller;

use app\common\controller\AdminBase;
use think\Db;
use think\facade\Config;
use think\facade\Cache;
use think\facade\Session;

class Index extends AdminBase
{
    protected function initialize()
    {
        parent::initialize();
		// 顶部导航栏信息
		$this->tabs = array(
			'index'=>array(
				'name'=>'首页管理',
				'url'=>url('admin/'.$this->controller.'/index'),
				'url_str' =>'admin/'.$this->controller.'/index',
				'status'=>false
			)
		);
    }

    public function index()
    {
		// 唤醒数据
		$param =$this->request->get();
		$this->tabs['index']['status'] = true;
		$this->assign('tabs',$this->tabs);
        return $this->fetch('index',['system_mobile'=>"",'wuji_mobile'=>""]);
    }
}