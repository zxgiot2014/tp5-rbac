<?php
/**
 * Created by WeiYongQiang.
 * User: weiyongqiang <hayixia606@163.com>
 * Date: 2019-04-17
 * Time: 22:51
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