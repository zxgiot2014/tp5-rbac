<?php
/**
 * Created by PhpStorm.
 * User: zhouxinguo <iszhouxinguo@outlook.com>
 * Date: 2020/5/15
 * Time: 17:25
 * Function：user模型相关操作集合
 */


namespace iset\rbac\model;


use think\facade\Config;
use think\facade\Log;
use think\Db;

class User extends Base
{
    /**
     * @var string 时间自动填写
     * @author zhouxinguo
     */
    protected $autoWriteTimestamp = 'datetime';
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';

    /**
     * 密码自动md5加密
     * @return string
     */
    protected function setPasswordAttr()
    {
        return md5($this->getData('password') . Config::get('rbac.salt_token'));
    }

    /**
     *
     * 新增和编辑用户信息
     * @param $data
     * @return $this
     * @throws Exception
     */
    public function saveUser($data = [])
    {
        if (!empty($data)) {
            $this->data($data);
        }
        $data = $this->getData();
        //对password进行md5加密
        if (isset($data['password']) && !empty($data['password'])) {
            $this->setAttr('password', $data['password']);
        }
        if (isset($data['id']) && !empty($data['id'])) {
            $this->isUpdate(true);
        }
        $this->save();
        return $this;
    }

    /**
     * 删除用户，支持批量删除
     * @param $id
     * @return bool
     * @throws Exception
     */
    public function delUser($id)
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
            throw new Exception('删除用户出错');
        }
        return true;
    }

    /**
     * 获取用户，支持条件查询获取
     * @param $where
     * @return array|\PDOStatement|string|\think\Collection|\think\Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getUser($where)
    {
        $model = Db::name('user')->setConnection($this->getConnection());
        if (is_numeric($where)) {
            return $model->where('id', $where)->find();
        } else {
            return $model->where($where)->select();
        }
    }

}