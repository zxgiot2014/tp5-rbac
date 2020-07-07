<?php
/**
 * Created by PhpStorm.
 * User: zhouxinguo <iszhouxinguo@outlook.com>
 * Date: 2020/5/15
 * Time: 17:25
 * Function：model基类，用于确定每个model的数据库连接信息
 */

namespace iset\rbac\model;


use think\Model;

class Base extends Model
{
    protected $connection = '';

    public function __construct($db = '', $data = [])
    {
        parent::__construct($data);
        $this->connection = empty($db) ? config('rbac')['db'] : $db;
    }

}