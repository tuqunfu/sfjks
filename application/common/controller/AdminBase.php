<?php
namespace app\common\controller;

use org\Auth;
use think\Loader;
use think\Controller;
use think\Db;
use think\facade\Config;
use think\facade\Cache;
use think\facade\Session;

use app\common\model\SystemLog as SystemLogModel;
use app\common\model\AdminUser as AdminUserModel;
use app\common\model\AuthGroup as AuthGroupModel;
use app\common\model\AuthGroupAccess as AuthGroupAccessModel;
use app\common\model\AuthRule as AuthRuleModel;
use app\common\model\Project as ProjectModel;
use app\common\model\DeviceInfo as DeviceInfoModel;
use app\common\model\ToolPkg as ToolPkgModel;
use app\common\model\ToolBranchPkg as ToolBranchPkgModel;
use app\common\model\LoginAccount as LoginAccountModel;
use app\common\model\User as UserModel;
use app\common\model\FontCategory as FontCategoryModel;
use app\common\model\Font as FontModel;
use app\common\model\FontLog as FontLogModel;
use app\common\model\Order as OrderModel;
use app\common\model\MembershipPlan as MembershipPlanModel;
use app\common\model\UserMembership as UserMembershipModel;

use Mongo\MongoClient;

class AdminBase extends Controller
{
	protected $tabs;
	protected $btns;
	
    protected function initialize()
    {
        parent::initialize();
		$this->controller = $this->request->controller();
		$this->mongoClient = new MongoClient(config('application.mongo_str'),config('application.dbname'));
		// 模型/数据库表
		$this->admin_user_model        = new AdminUserModel();
        $this->auth_group_model        = new AuthGroupModel();
        $this->auth_group_access_model = new AuthGroupAccessModel();
        $this->auth_rule_model  	   = new AuthRuleModel();
		$this->project_model           = new ProjectModel();
		$this->user_model = new AdminUserModel();
		$this->device_info_model = new DeviceInfoModel();
		$this->toolpkg_model = new ToolPkgModel();
		$this->system_log_model = new SystemLogModel();
		$this->tool_branch_pkg_model = new ToolBranchPkgModel();
		$this->login_account_model = new LoginAccountModel();
		$this->user_model = new UserModel();
		$this->font_category_model = new FontCategoryModel();
		$this->font_model = new FontModel();
		$this->Font_log_model = new FontLogModel();
		$this->order_model = new OrderModel();
		$this->membership_plan_model = new MembershipPlanModel();
		$this->user_membership_model = new UserMembershipModel();
		// 权限按钮
        $this->checkAuth();
        $this->getMenu();
		$this->auth = Auth::instance();
		if(Session::has('admin_user'))
		{
			$this->admin_user =Session::get('admin_user');
		}
		// 输出当前请求控制器（配合后台侧边菜单选中状态）
        $this->assign('controller', Loader::parseName($this->request->controller()));
		
		//渲染权限对象
        $this->assign('auth', $this->auth);
		$this->assign('btns', []);
    }
	
    protected function checkAuth()
    {
        if (!Session::has('admin_id')) {
            $this->redirect('admin/login/index');
        }

        $module     = $this->request->module();
        $controller = $this->request->controller();
        $action     = $this->request->action();
		$data     = $this->request->url();
        // 排除权限
        $not_check = ['admin/Index/index', 'admin/AuthGroup/getjson'];

        if (!in_array($module . '/' . $controller . '/' . $action, $not_check)) {
            $auth     = new Auth();
            $admin_id = Session::get('admin_id');
			if($action != 'index'){
				$this->system_log = new SystemLogModel();
				$slog = array();
				$slog['type_id'] = 3;
				$slog['admin_id'] = $admin_id;
				$slog['module'] = $module;
				$slog['method'] = $controller.'/'.$action;
				$slog['c_time'] = time();
				$slog['ip'] = $this->request->ip();
				$slog['record'] = '操作'.$data;
				$this->system_log->allowField(true)->save($slog);
			}
        }
    }
	
    /**
     * 获取侧边栏菜单
     */
    protected function getMenu()
    {
        $menu     = [];
        $admin_id = Session::get('admin_id');
        $auth     = new Auth();

        $auth_rule_list = Db::name('auth_rule')->where('status', 1)->order(['sort' => 'DESC', 'id' => 'ASC'])->select();

        foreach ($auth_rule_list as $value) {
            if ($auth->check($value['name'], $admin_id) || $admin_id == 1) {
                $menu[] = $value;
            }
        }
        $menu = !empty($menu) ? array2tree($menu) : [];
        $this->assign('menu', $menu);
    }
	
	function object_array($array) {  
		if(is_object($array)) {  
			$array = (array)$array;  
		} 
		if(is_array($array)) {
			foreach($array as $key=>$value) {  
				$array[$key] = $this->object_array($value);  
			}  
		}  
		return $array;  
	}
}