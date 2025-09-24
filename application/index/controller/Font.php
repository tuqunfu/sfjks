<?php
namespace app\index\controller;

use app\common\controller\HomeBase;
use think\Db;
use think\Exception;

class Font extends HomeBase
{
    protected function initialize()
    {
        parent::initialize();
    }
	
    public function index()
    {
		$template = array(
			"title" => "书法教科书",
			"show_header" => true,
			"show_footer" => true,
			"footer_active" => 1
		);
        $font_category_list = $this->font_category_model->select();
		$this->assign('template', $template);
        $this->assign('font_category_list', $font_category_list);
        return $this->fetch();
    }

    public function get_font_list(){
        if ($this->request->isPost()) {
            $param            = $this->request->post();
            $font_list = $this->font_model->where(array('category_id'=>$param['category_id'],'status'=>1))->select();
            return json($font_list);
        }else{
            return json([]);
        }
    }
}
