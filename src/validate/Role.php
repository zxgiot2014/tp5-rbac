<?php
/**
 * Created by PhpStorm.
 * User: zhouxinguo <iszhouxinguo@outlook.com>
 * Date: 2020/5/15
 * Time: 17:25
 * Function：Role模型参数校验器
 */

namespace iset\rbac\validate;


use think\Validate;

class Role extends Validate
{
    protected $rule = [
        'name' => 'require|max:50|unique:iset\rbac\model\role,name^id'
    ];

    protected $message = [
        'name.require' => '角色名不能为空',
        'name.max' => '角色名不能长于50个字符',
        'name.unique' => '角色名称不能重复'
    ];

}