<?php
namespace app\admin\validate;

use think\Validate;

/**
 * 用户验证器
 * Class User
 * @package app\admin\validate
 */
class FontCategory extends Validate
{
    protected $rule = [
        'name'         => 'require|unique:font_category',
        'status'           => 'require'
    ];

    protected $message = [
        'name.require'         => '请输入用户名',
        'name.unique'          => '用户名已存在',
        'status.require'       => '请选择状态'
    ];
}