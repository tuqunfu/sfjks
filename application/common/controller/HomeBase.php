<?php
namespace app\common\controller;

use think\Controller;
use think\Db;
use think\facade\Config;
use think\facade\Session;
use think\Cache;
use app\common\model\FontCategory as FontCategoryModel;
use app\common\model\Font as FontModel;
use app\common\model\User as UserModel;
use app\common\model\FontLog as FontLogModel;
use app\common\model\MembershipPlan as MembershipPlanModel;
use app\common\model\UserMembership as UserMembershipModel;
use app\common\model\Order as OrderModel;
class HomeBase extends Controller
{
    protected function initialize()
    {
        parent::initialize();
        // $this->getSystem();
        $this->font_category_model = new FontCategoryModel();
		$this->font_model = new FontModel();
		$this->user_model = new UserModel();
		$this->font_log_model = new FontLogModel();
		$this->membership_plan_model = new MembershipPlanModel();
		$this->user_membership_model = new UserMembershipModel();
        $this->order_model = new OrderModel();
		if(Session::has('user')){
			$this->user_info =Session::get('user');
		}
    }

    /**
     * 获取站点信息
     */
    protected function getSystem()
    {
        if (Cache::get('site_config') !== false) {
            $site_config = Cache::get('site_config');
        } else {
            $site_config = Db::name('system')->field('value')->where('name', 'site_config')->find();
            $site_config = unserialize($site_config['value']);
            Cache::set('site_config', $site_config);
        }
        $this->assign($site_config);
    }
}