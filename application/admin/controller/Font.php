<?php
namespace app\admin\controller;
use app\common\controller\AdminBase;
use think\facade\Config;
use think\facade\Cache;
use think\facade\Session;
use think\Db;

class Font extends AdminBase
{
    protected function initialize()
    {
        parent::initialize();
		$this->tabs = array(
			'index'=>array(
				'name'=>'字体信息',
				'url'=>url('admin/'.$this->controller.'/index'),
				'url_str' =>'admin/'.$this->controller.'/index',
				'status'=>false
			),
			'add'=>array(
				'name'=>'添加字体',
				'url'=>url('admin/'.$this->controller.'/add'),
				'url_str' =>'admin/'.$this->controller.'/add',
				'status'=>false
			)
		);
    }

    public function index()
    {
        $font_list = $this->font_model->alias('a')->join('os_font_category t','a.category_id=t.id')
                                                ->field('a.*,t.name as category_name')
                                                ->where(array('a.status'=>1))->select();;
		$this->tabs['index']['status'] = true;
		$this->assign('tabs',$this->tabs);
        return $this->fetch('index', ['font_list' => $font_list]);
    }
    
    public function add()
    {
        $font_category_list = $this->font_category_model->select();
		$this->tabs['add']['status'] = true;	
		$this->assign('tabs',$this->tabs);
		$this->assign('btns',$this->btns);
        return $this->fetch('add',['font_category_list' => $font_category_list,'uploadurl' => url('api/upload/upload_font')]);
    }

    public function save()
    {
        if ($this->request->isPost()) {
            $data            = $this->request->param();
            $validate_result = $this->validate($data, 'Font');
            if ($validate_result !== true) {
                $this->error($validate_result);
            } else {
                $data['create_time'] = strtotime('now');
                $data['create_by'] = 'admin';
                if ($this->font_model->allowField(true)->save($data)) {
                    $this->success('保存成功');
                } else {
                    $this->error('保存失败');
                }
            }
        }
    }

    public function edit($id)
    {
        $font = $this->font_model->find($id);
		$this->tabs['edit'] = array(
				'name'=>'编辑字体',
				'url' => url('admin/'.$this->controller.'/edit'),
				'url_str' =>'admin/'.$this->controller.'/edit',
				'status'=>true
		);
        $font_category_list = $this->font_category_model->select();
		$this->assign('tabs',$this->tabs);
		$this->assign('btns',$this->btns);
        return $this->fetch('edit', ['font' => $font,'font_category_list'=>$font_category_list,'uploadurl' => url('api/upload/upload_font')]);
    }

    public function update()
    {
        if ($this->request->isPost()) {
            $data            = $this->request->param();
            $validate_result = $this->validate($data, 'Font');
            if ($validate_result !== true) {
                $this->error($validate_result);
            } else {
                $font = $this->font_model->find($data['id']);
                $font->id       = $data['id'];
				$font->name       = $data['name'];
				$font->file_path   = $data['file_path'];
				$font->category_id = $data['category_id'];
				$font->remark       = $data['remark'];
				$font->index_sort   = $data['index_sort'];
                $font->status = $data['status'];
                if ($font->save() !== false) {
                    $this->success('更新成功');
                } else {
                    $this->error('更新失败');
                }
            }
        }
    }

    public function delete($id)
    {
        if ($this->font_model->destroy($id)) {
            $this->success('删除成功');
        } else {
            $this->error('删除失败');
        }
    }
}