<?php
/**
 * Created by PhpStorm.
 * User: zhouxinguo <iszhouxinguo@outlook.com>
 * Date: 2020/5/15
 * Time: 17:25
 * Function：PermissionCategory模型参数校验器
 */

namespace iset\rbac\validate;


use think\Validate;

class PermissionCategory extends Validate
{
    protected $rule = [
        'name' => 'require|max:50|unique:iset\rbac\model\permissioncategory,name',
    ];

    protected $message = [
        'name.require' => '分组名不能为空',
        'name.max' => '分组名不能长于50个字符',
        'name.unique' => '分组名称不能重复',
    ];

}