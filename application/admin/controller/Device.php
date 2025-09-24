<?php
namespace app\admin\controller;

use app\common\controller\AdminBase;
use think\Db;
use think\facade\Config;
use think\facade\Cache;
use think\facade\Session;

class Device extends AdminBase
{
    protected function initialize()
    {
        parent::initialize();
		// 顶部导航栏信息
		$this->tabs = array(
			'index'=>array(
				'name'=>'设备源管理',
				'url'=>url('admin/'.$this->controller.'/index'),
				'url_str' =>'admin/'.$this->controller.'/index',
				'status'=>false
			),
			'add'=>array(
				'name'=>'添加设备源',
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
        if (isset($param['name'])&&$param['name']) {
			$map[] = ['name','like', '%'.$param['name'].'%'];
        }else{
			$param['name'] = '';
		}
        $list = $this->device_info_model->where($map)->order('create_time DESC')->paginate(15, false);
		foreach($list as $key=>$val)
		{
			$list[$key]['download_url'] = Config('application.apiurl').$val['path'];
		}
		$this->tabs['index']['status'] = true;
		$this->assign('tabs',$this->tabs);
        return $this->fetch('index', ['list' => $list,'name'=>$param['name']]);
    }
	
    public function add()
    {
		$this->tabs['add']['status'] = true;	
		$this->assign('tabs',$this->tabs);
        return $this->fetch('add',['uploadurl' => url('api/upload/uploadjson')]);
    }


    public function save()
    {
        if ($this->request->isPost()) {
            $data            = $this->request->post();
			// 对json文件进行校检
			$json = '';
			$file_path = Config('application.jsonurl').'/'.$data['path'];
			if(file_exists($file_path)){
				$fp = fopen($file_path,"r");
				$str = fread($fp,filesize($file_path));//指定读取大小，这里把整个文件内容读取出来
				$json = str_replace("\r\n","<br />",$str);
				fclose($fp);
			}
			
			if(!empty($json))
			{
				if($this->checkJson($json))
				{
					$arr = json_decode($json,true);
					foreach($arr as $key=>$val)
					{
						if(empty($val['cpufile']) || empty($val['strs']))
						{
							$this->error('上传文件内容中key值为'.$val['key'].'不正确！');
						}
					}
				}
				else
				{
					$this->error('上传文件内容json格式不正确！');
				}
			}
			else
			{
				$this->error('上传文件内容不能为空！');
			}
			
			$data['create_time'] = time();
			if ($this->device_info_model->allowField(true)->save($data)) {
				$this->success('保存成功');
			} else {
				$this->error('保存失败');
			}
		}
	}
	
	//自定义验证规则
    public function checkJson($value)
    {
		if(empty($value))
		{
			return true;
		}
		else
		{
			// 对引号转移&quot，转义回""
			$value = htmlspecialchars_decode($value);
			if (is_string($value)) {
				@json_decode($value);
				return (json_last_error() === JSON_ERROR_NONE);
			}
			return false;
		}
    }
	
}