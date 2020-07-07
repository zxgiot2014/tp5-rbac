<?php
/**
 * Created by PhpStorm.
 * User: zhouxinguo <iszhouxinguo@outlook.com>
 * Date: 2020/5/15
 * Time: 17:25
 * Function：UserRole模型相关操作集合
 */

namespace iset\rbac\model;


class UserRole extends Base
{
    /**
     * @var string 时间自动填写
     * @author zhouxinguo
     */
    protected $autoWriteTimestamp = 'datetime';
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';

}