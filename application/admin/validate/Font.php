<?php
namespace app\admin\validate;

use think\Validate;

/**
 * 用户验证器
 * Class User
 * @package app\admin\validate
 */
class Font extends Validate
{
    protected $rule = [
        'name'         => 'require|unique:font',
        'status'           => 'require'
    ];

    protected $message = [
        'name.require'         => '请输入字体名称',
        'name.unique'          => '字体名称已存在',
        'status.require'       => '请选择状态'
    ];
}