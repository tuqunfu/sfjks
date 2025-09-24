<?php
namespace app\common\model;

use think\Model;

class Project extends Model
{

    public function chanel()
    {
        return $this->hasMany('chanel','pkgname','pkgname','LEFT JOIN');
    }
}