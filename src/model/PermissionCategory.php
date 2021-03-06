<?php
/**
 * Created by PhpStorm.
 * User: zhouxinguo <iszhouxinguo@outlook.com>
 * Date: 2020/5/15
 * Time: 17:25
 * Function：PermissionCategory模型相关操作
 */

namespace iset\rbac\model;


use think\Db;
use think\Exception;

class PermissionCategory extends Base
{
    /**
     * @var string 时间自动填写
     * @author zhouxinguo
     */
    protected $autoWriteTimestamp = 'datetime';
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';

    /**
     * 编辑权限分组
     * @param $data
     * @return $this
     * @throws Exception
     */
    public function saveCategory($data = [])
    {
        if (!empty($data)) {
            $this->data($data);
        }
        $data = $this->getData();
        if (isset($data['id']) && !empty($data['id'])) {
            $this->isUpdate(true);
        } else {
            $validate = new \iset\rbac\validate\PermissionCategory();
            if (!$validate->check($this)) {
                throw new Exception($validate->getError());
            }
        }
        $this->save();
        return $this;
    }

    /**
     * 删除权限分组
     * @param $id
     * @return bool
     * @throws Exception
     */
    public function delCategory($id)
    {
        $where = [];
        if (is_array($id)) {
            $where[] = ['id', 'IN', $id];
        } else {
            $id = (int)$id;
            if (is_numeric($id) && $id > 0) {
                $where[] = ['id', '=', $id];
            } else {
                throw new Exception('删除条件错误');
            }
        }

        if ($this->where($where)->delete() === false) {
            throw new Exception('删除权限分组出错');
        }
        return true;
    }

    /**
     * 获取权限分组
     * @param $where
     * @return array|\PDOStatement|string|\think\Collection|\think\Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getCategory($where)
    {
        $model = Db::name('permission_category')->setConnection($this->getConnection());
        if (is_numeric($where)) {
            return $model->where('id', $where)->find();
        } else {
            return $model->where($where)->select();
        }
    }

}