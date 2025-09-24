<?php
namespace app\admin\validate;

use think\Validate;

/**
 * 用户验证器
 * Class User
 * @package app\admin\validate
 */
class User extends Validate
{
    protected $rule = [
        'user_name'         => 'require|unique:user',
        'status'           => 'require'
    ];

    protected $message = [
        'user_name.require'         => '请输入用户名',
        'user_name.unique'          => '用户名已存在',
        'status.require'           => '请选择状态'
    ];
}