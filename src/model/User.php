<?php
/**
 * Created by PhpStorm.
 * User: 周新国
 * Date: 2020/5/15
 * Time: 17:25
 * Function：文件描述
 */

namespace iset\rbac\model;


class User extends Base
{
    /**
     * @var string 时间自动填写
     * @author zhouxinguo
     */
    protected $autoWriteTimestamp = 'datetime';
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';

}