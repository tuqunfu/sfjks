<?php
namespace app\admin\controller;
use app\common\controller\AdminBase;
use think\facade\Config;
use think\facade\Cache;
use think\facade\Session;
use think\Db;

class FontCategory extends AdminBase
{
    protected function initialize()
    {
        parent::initialize();
		$this->tabs = array(
			'index'=>array(
				'name'=>'分类管理',
				'url'=>url('admin/'.$this->controller.'/index'),
				'url_str' =>'admin/'.$this->controller.'/index',
				'status'=>false
			),
			'add'=>array(
				'name'=>'添加分类',
				'url'=>url('admin/'.$this->controller.'/add'),
				'url_str' =>'admin/'.$this->controller.'/add',
				'status'=>false
			)
		);
    }

    public function index()
    {
        $font_category_list = $this->font_category_model->select();
		$this->tabs['index']['status'] = true;
		$this->assign('tabs',$this->tabs);
        return $this->fetch('index', ['font_category_list' => $font_category_list]);
    }

    public function add()
    {
		$this->tabs['add']['status'] = true;	
		$this->assign('tabs',$this->tabs);
		$this->assign('btns',$this->btns);
        return $this->fetch('add',['uploadurl' => url('api/upload/upload_font')]);
    }

    public function save()
    {
        if ($this->request->isPost()) {
            $data            = $this->request->param();
            $validate_result = $this->validate($data, 'FontCategory');
            if ($validate_result !== true) {
                $this->error($validate_result);
            } else {
                $data['create_time'] = strtotime('now');
                $data['create_by'] = 'admin';
                if ($this->font_category_model->allowField(true)->save($data)) {
                    $this->success('保存成功');
                } else {
                    $this->error('保存失败');
                }
            }
        }
    }

    public function edit($id)
    {
        $font_category = $this->font_category_model->find($id);
		$this->tabs['edit'] = array(
				'name'=>'编辑分类',
				'url' => url('admin/'.$this->controller.'/edit'),
				'url_str' =>'admin/'.$this->controller.'/edit',
				'status'=>true
		);
		$this->assign('tabs',$this->tabs);
		$this->assign('btns',$this->btns);
        return $this->fetch('edit', ['font_category' => $font_category,'uploadurl' => url('api/upload/upload_font')]);
    }

    public function update()
    {
        if ($this->request->isPost()) {
            $data            = $this->request->param();
            $validate_result = $this->validate($data, 'FontCategory');

            if ($validate_result !== true) {
                $this->error($validate_result);
            } else {
                $font_category = $this->font_category_model->find($data['id']);
                $font_category->id       = $data['id'];
				$font_category->name       = $data['name'];
				$font_category->file_path  = $data['file_path'];
				$font_category->remark     = $data['remark'];
				$font_category->index_sort       = $data['index_sort'];
                $font_category->status = $data['status'];
                if ($font_category->save() !== false) {
                    $this->success('更新成功');
                } else {
                    $this->error('更新失败');
                }
            }
        }
    }

    public function delete($id)
    {
        if ($this->font_category_model->destroy($id)) {
            $this->success('删除成功');
        } else {
            $this->error('删除失败');
        }
    }
}