<?php
namespace app\admin\controller;

use app\common\controller\AdminBase;
use think\facade\Config;
use think\facade\Cache;
use think\facade\Session;
use think\Db;

class System extends AdminBase
{
    public function initialize()
    {
        parent::initialize();
		$this->tabs = array(
			'siteConfig'=>array(
				'name'=>'站点配置',
				'url'=>url('admin/'.$this->controller.'/siteConfig'),
				'url_str' =>'admin/'.$this->controller.'/siteConfig',
				'status'=>false
			)
		);
    }

    public function siteConfig()
    {
        $site_config = Db::name('system')->field('value')->where('name', 'site_config')->find();
        $site_config = unserialize($site_config['value']);
		$this->tabs['siteConfig']['status'] = true;
		$this->assign('tabs',$this->tabs);
        return $this->fetch('site_config', ['site_config' => $site_config]);
    }

    public function updateSiteConfig()
    {
        if ($this->request->isPost()) {
            $site_config                = $this->request->post('site_config/a');
            $site_config['site_tongji'] = htmlspecialchars_decode($site_config['site_tongji']);
            $data['value']              = serialize($site_config);
            if (Db::name('system')->where('name', 'site_config')->update($data) !== false) {
                $this->success('提交成功');
            } else {
                $this->error('提交失败');
            }
        }
    }

    public function clear()
    {
        if (delete_dir_file(CACHE_PATH) || delete_dir_file(TEMP_PATH)) {
            $this->success('清除缓存成功');
        } else {
            $this->error('清除缓存失败');
        }
    }
}