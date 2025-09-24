<?php
namespace app\api\controller;

use think\Controller;
use app\common\model\Project as ProjectModel;
use app\common\model\Chanel as ChanelModel;
use think\facade\Config;
use think\facade\Cache;
use think\facade\Session;
use think\Db;
use think\Exception;
use Mongo\MongoClient;

class Index extends Base
{
	protected $mongoClient;
	protected $chanels;
    protected function initialize()
    {
        parent::initialize();
		$this->chanel_model = new ChanelModel();
		$this->project_model = new ProjectModel();
		if(!count($this->chanels)>0)
		{
			$data = $this->chanel_model->select();
			foreach($data as $key=>$value)
			{
				$this->chanels[$value['outid']] = $value;
			}
		}
    }
	
	public function info()
	{
		$this->mongoClient = new MongoClient(config('mongo_str'),config('dbname'));
		$keys = array('ip'=>1);
		$initial = array('items'=>array());
		$reduce = "function (obj,prev) {prev.items.push(obj.name);}";
		$data = $this->mongoClient->SelectData('activation_2018-07-16',$keys,$initial,$reduce);
		print_r($data);
	}
	
	
	// 更新
	public function updateinfo()
	{
		$chanels = $this->chanel_model->select();
		foreach($chanels as $key=>$val)
		{
			$project_info = $this->project_model->where(array('pkgname'=>$val['pkgname']))->find();
			$this->chanel_model->where(array('id'=>$val['id']))->update(array('project_id'=>$project_info['id']));	
		}
		echo 'success';
	}
}