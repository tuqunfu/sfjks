<?php
namespace app\admin\controller;
use app\common\controller\AdminBase;
use think\facade\Config;
use think\facade\Cache;
use think\facade\Session;
use think\Db;

class FontLog extends AdminBase
{
    protected function initialize()
    {
        parent::initialize();
		$this->tabs = array(
			'index'=>array(
				'name'=>'字体日志',
				'url'=>url('admin/'.$this->controller.'/index'),
				'url_str' =>'admin/'.$this->controller.'/index',
				'status'=>false
			)
		);
    }

    public function index()
    {
        $user_list = $this->user_model->select();
		$this->tabs['index']['status'] = true;
		$this->assign('tabs',$this->tabs);
        return $this->fetch('index', ['user_list' => $user_list]);
    }
}