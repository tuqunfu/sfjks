<?php
namespace app\admin\controller;

use app\common\controller\AdminBase;
use think\facade\Config;
use think\facade\Cache;
use think\facade\Session;
use think\Db;

class UpdateUrl extends AdminBase
{
    protected function initialize()
    {
        parent::initialize();
		$this->tabs = array(
			'index'=>array(
				'name'=>'更新链接',
				'url'=>url('admin/'.$this->controller.'/index'),
				'url_str' =>'admin/'.$this->controller.'/index',
				'status'=>false
			)
		);
		
        $awakens = $this->chanel_model->field('project_id,name,pkgname')->where(array('status'=>1,'type'=>2))->group('project_id')->select();
	    $this->assign('awakens', $awakens);
    }
	
    public function index($page = 1)
    {
        $map = [];
		$this->tabs['index']['status'] = true;
		$this->assign('tabs',$this->tabs);
        return $this->fetch('index');
    }
	
    public function update()
    {
		if ($this->request->isPost()) {
			$data  = $this->request->post();
			if($data['project_id']<=0){
				$this->error('请选择唤醒');
			}
			
			if(isset($data['url']))
			{
				$urls = array();
				for($i =0;$i<count($data['url']);$i++)
				{
					if(!empty($data['url'][$i]))
					{
						$urls[] = htmlspecialchars_decode($data['url'][$i]);
					}
				}
				if(count($urls)>0){
					$urls_new =  implode(",", $urls);
					$list = $this->chanel_model->field('id,outid')->where(array('type'=>2,'project_id'=>$data['project_id'],'status'=>1))->select();
					$data = array();
					foreach($list as $k=>$v){
						$ljwid = "from=".$v['outid'];
						$item['url'] = str_replace("from=ljwid",$ljwid,$urls_new);
						$this->chanel_model->where(array('id'=> $v['id']))->update($item);
					}
					$this->success('更新成功');
				}else{
					$this->error('请输入唤醒链接');
				}
			}
			else
			{
				$this->error('请输入唤醒链接');
			}
		}
    }
}