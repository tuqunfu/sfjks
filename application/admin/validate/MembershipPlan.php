<?php
namespace app\admin\validate;

use think\Validate;

/**
 * 会员设置验证器
 * Class User
 * @package app\admin\validate
 */
class MembershipPlan extends Validate
{
    protected $rule = [
        'name'         => 'require|unique:membership_plan',
        'price'           => 'require'
    ];

    protected $message = [
        'name.require'         => '请输入会员名称',
        'name.unique'          => '会员名称已存在',
        'price.require'        => '请输入会员价格'
    ];
}