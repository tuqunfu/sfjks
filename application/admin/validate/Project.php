<?php
namespace app\admin\validate;

use think\Validate;

class Project extends Validate
{
    protected $rule = [
        'pkgname'         => 'require|unique:Project',
		'name'         => 'require',
		'newmax'         => 'require|between:0,10000',
		'oldmaxpersent'         => 'require|between:0,100',
		'oldminpersent'         => 'require|between:0,100',
		'olddays'         => 'require|between:0,500',
		'usevpn'         => 'require',
        'daytime' => 'require',
		'extension'=>'checkJson',
		'accessJSON'=>'checkaccessJSON'
    ];

    protected $message = [
        'username.require'         => '请输入包名',
		'name.require'         => '请输入名称',
		'newmax.require'         => '请输入新增上限',
		'newmax.between'         => '请输入正确的新增上限',
        'pkgname.unique'          => '包名已存在',
        'oldmaxpersent.require'         => '请输入留存上限',
		'oldmaxpersent.between'         => '请输入正确的留存上限',
		'oldminpersent.require'         => '请输入留存下限',
		'oldminpersent.between'         => '请输入正确的留存下限',
		'olddays.require'         => '请输入留存天数',
		'olddays.between'         => '请输入正确的留存天数',
		'olddays.usevpn'         => '请选择是否使用VPN',
		'olddays.daytime'         => '请选择是否使用日间模式',
		'extension.checkJson'	=>'请检查json格式',
		'accessJSON.checkaccessJSON'=>'请检查json格式'
    ];
	
	//自定义验证规则
    protected function checkJson($value)
    {
		if(empty($value))
		{
			return true;
		}
		else
		{
			// 对引号转移&quot，转义回""
			$value = htmlspecialchars_decode($value);
			if (is_string($value)) {
				@json_decode($value);
				return (json_last_error() === JSON_ERROR_NONE);
			}
			return false;
		}
    }
	// [{"pkgName":"","evetType":0,"className":"","operationType":"","findType":"","findText":"","operationMsg":""}]
	protected function  checkaccessJSON($value)
	{
		if(empty($value))
		{
			return true;
		}
		else
		{
			// 对引号转移&quot，转义回""
			$value = htmlspecialchars_decode($value);
			if (is_string($value)) {
				@json_decode($value);
				if(json_last_error() === JSON_ERROR_NONE)
				{
					
					$data = json_decode($value,true);
					if(is_array($data))
					{
						foreach($data as $key=>$val)
						{
							if(!(isset($val['pkgName']) &&  isset($val['evetType']) && isset($val['className']) && isset($val['operationType']) && isset($val['findType']) && isset($val['operationMsg'])))
							{
								return false;
								break;
							}
						}
					}
					else
					{
						return false;
					}
					
					
				}
				else
				{
					return false;
				}
			}
			return true;
		}
	}
	
	
}