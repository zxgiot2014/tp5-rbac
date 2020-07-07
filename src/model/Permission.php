<?php
/**
 * Created by PhpStorm.
 * User: zhouxinguo <iszhouxinguo@outlook.com>
 * Date: 2020/5/15
 * Time: 17:25
 * Function：permission模型相关操作
 */

namespace iset\rbac\model;


use think\Db;
use think\Exception;
use think\facade\Cache;
use think\facade\Config;
use think\facade\Session;

class Permission extends Base
{
    /**
     * @var string 时间自动填写
     * @author zhouxinguo
     */
    protected $autoWriteTimestamp = 'datetime';
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';
    
    protected $auto = ['path_id'];

    protected function setPathIdAttr()
    {
        return md5($this->getData('path'));
    }

    /**
     * 编辑权限数据
     * @param array $data
     * @return $this
     * @throws Exception
     */
    public function savePermission($data = [])
    {
        if (!empty($data)) {
            $this->data($data);
        }
        $data = $this->getData();
        if (isset($data['id']) && !empty($data['id'])) {
            $this->isUpdate(true);
        } else {
            $validate = new \iset\rbac\validate\Permission();
            if (!$validate->check($this)) {
                throw new Exception($validate->getError());
            }
        }
        $this->save();
        return $this;
    }

    /**
     * 删除权限
     * @param $id
     * @return bool
     * @throws Exception
     */
    public function delPermission($id)
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
            throw new Exception('删除权限出错');
        }
        return true;
    }

    /**
     * 获取用户权限
     * @param $userId
     * @param int $timeOut
     * @return array|mixed|\PDOStatement|string|\think\Collection
     * @throws Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function userPermission($userId)
    {
        if (empty($userId)) {
            throw new Exception('参数错误');
        }
        // 判断是否已经有缓存
        $permission = Cache::get(Config::get('app.rbac.permission_cache_prefix') . $userId);
        if (!empty($permission)) {
            return $permission;
        }
        $permission = $this->getPermissionByUserId($userId);
        if (empty($permission)) {
            throw new Exception('未查询到该用户的任何权限');
        }
        $newPermission = [];
        if (!empty($permission)) {
            foreach ($permission as $k => $v) {
                $newPermission[$v['path']] = $v;
            }
        }
        Cache::set(Config::get('app.rbac.permission_cache_prefix') . $userId, $newPermission);
        return $newPermission;
    }

    /**
     * @param $path
     * @return bool
     * @throws Exception
     * 检查用户有没有权限执行某操作
     */
    public function can($userId, $path)
    {
        $permissionList = Cache::get(Config::get('app.rbac.permission_cache_prefix') . $userId);
        if (empty($permissionList)) {
            throw new Exception('您的登录信息已过期请重新登录');
        }

        if (isset($permissionList[$path]) && !empty($permissionList[$path])) {
            return true;
        }
        return false;
    }

    /**
     * 根据userid获取权限
     * @param $userId
     * @return array|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getPermissionByUserId($userId)
    {
        $prefix = $this->getConfig('prefix');
        $permission = Db::name('permission')->setConnection($this->getConnection())->alias('p')
            ->join(["{$prefix}role_permission" => 'rp'], 'p.id = rp.permission_id')
            ->join(["{$prefix}user_role" => 'ur'], 'rp.role_id = ur.role_id')
            ->where('ur.user_id', $userId)->select();
        return $permission;
    }

    /**
     * 获取权限节点
     * @param $condition
     * @return array|\PDOStatement|string|\think\Collection|\think\Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getPermission($condition)
    {
        $model = Db::name('permission')->setConnection($this->getConnection());
        if (is_numeric($condition)) {
            return $model->where('id', $condition)->find();
        } else {
            return $model->where($condition)->select();
        }
    }
}