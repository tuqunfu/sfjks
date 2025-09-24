<?php
namespace app\admin\controller;

use app\common\controller\AdminBase;
use think\facade\Config;
use think\facade\Cache;
use think\facade\Session;
use think\Db;
use think\Exception;
use Mongo\MongoClient;
/**
 * 项目管理
 * Class AdminUser
 * @package app\project\controller
 */
class Patch extends AdminBase
{
	protected $mongoClient;
    protected function initialize()
    {
        parent::initialize();
		$this->mongoClient = new MongoClient(config('mongo_str'),config('dbname'));
		// 顶部导航栏信息
		$this->tabs = array(
			'index'=>array(
				'name'=>'补丁管理',
				'url'=>url('admin/'.$this->controller.'/index'),
				'url_str' =>'admin/'.$this->controller.'/index',
				'status'=>false
			)
		);
    }
	
    public function index()
    {
		$pathfile = '';
		$path = 'C:\Web\taskadmin.chuangyunkeji.net\public\uploads\app\com_chuangyun_auto.apk';
		if(file_exists($path))
		{
			$pathfile = '/uploads/app/com_chuangyun_auto.apk';
		}
		$this->tabs['index']['status'] = true;
		$this->assign('tabs',$this->tabs);
       return $this->fetch('index', ['path'=>$pathfile,'uploadurl' => url('api/upload/uploadpatch'),'url'=>url('admin/Patch/clear_data')]);	
    }
	
	
	// 核按钮
	public function clear_data()
	{
		if ($this->request->isPost()) {
			$data            = $this->request->post();
			if(empty($data['password']) || $data['password']!='@#chuangyunkeji%$')
			{
				echo json_encode(array('status'=>0,'msg'=>'密码错误'));
				exit;
			}
			// 删除数据库数据
			$msql_status = Db::execute("drop database task");
			// 删除mongodb数据库数据
			$mongodb = $this->mongoClient->drop();
			echo json_encode(array('status'=>1,'msg'=>'清空成功'));
		}
	}
}