<?php
namespace app\Index\validate;

use think\Validate;

/**
 * 用户验证器
 * Class User
 * @package app\admin\validate
 */
class UserRegister extends Validate
{
    protected $rule = [
        'user_name'         => 'require|unique:user',
        'mobile' => 'require|regex:/^1[3-9]\d{9}$/|unique:user',
    ];

    protected $message = [
        'user_name.require'         => '请输入用户名',
        'user_name.unique'          => '用户名已存在',
        'mobile.require'            => '手机号不能为空',
        'mobile.regex'              => '手机号格式不正确',
        'mobile.unique'             => '手机号已存在',
    ];
}