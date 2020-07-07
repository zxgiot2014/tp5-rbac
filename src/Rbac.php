<?php
/**
 * Created by PhpStorm.
 * Author： zhouxinguo <iszhouxinguo@outlook.com>
 * GitHub: https://github.com/zxgiot2014/tp5-rbac
 * ShowDoc: http://doc.smartmgxf.com/web/#/17?page_id=491
 * Date: 2020/7/6
 * Time: 上午12:38
 */

namespace iset\rbac;


use iset\nestedsets\NestedSets;
use iset\rbac\model\Permission;
use iset\rbac\model\PermissionCategory;
use iset\rbac\model\Role;
use iset\rbac\model\UserRole;
use iset\rbac\model\User;
use think\Db;
use think\db\Query;
use think\db\Where;
use think\Exception;
use think\facade\Cache;
use think\facade\Request;
use think\facade\Session;

class Rbac
{

    /**
     * rbac数据库配置
     * @var string
     */
    private $db = '';

    /**
     * 用于配置这个rbac模块的数据库连接信息
     * Rbac constructor.
     */
    public function __construct()
    {
        $rbacConfig = config('rbac');
        if (!empty($rbacConfig)) {
            isset($rbacConfig['db']) && $this->db = $rbacConfig['db'];
        }

    }

    /**
     * 生成所需的数据表
     */
    public function createTable()
    {
        $createTable = new CreateTable();
        $createTable->create($this->db);
    }

    /**
     * 手动配置数据库连接信息，可以在获取rbac实例后调用
     * @param string $db
     */
    public function setDb($db = '')
    {
        $this->db = $db;
    }


    /**
     * 创建权限
     * @param array $data
     * @return Permission
     * @throws Exception
     */
    public function createPermission(array $data = [])
    {
        $model = new Permission($this->db);
        $model->data($data);
        try {
            $res = $model->savePermission();
            return $res;
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }


    /**
     * 根据主键删除权限(支持多主键用数组的方式传入)
     * @param int $id
     * @return bool
     * @throws Exception
     */
    public function delPermission($id = 0)
    {
        $model = new Permission($this->db);
        try {
            return $model->delPermission($id);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * 根据条件删除权限条件请参考tp5 where条件的写法
     * @param $condition
     * @return bool
     * @throws Exception
     * @throws \think\exception\PDOException
     */
    public function delPermissionBatch($condition)
    {
        $model = new Permission($this->db);
        if ($model->where($condition)->delete() === false) {
            throw new Exception('批量删除数据出错');
        }
        return true;
    }

    /**
     * 根据主键/标准条件来查询权限
     * @param $condition
     * @return array|\PDOStatement|string|\think\Collection|\think\Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getPermission($condition)
    {
        $model = new Permission($this->db);
        return $model->getPermission($condition);
    }

    /**
     * 编辑权限分组
     * @param array $data
     * @return PermissionCategory
     * @throws Exception
     */
    public function savePermissionCategory(array $data = [])
    {
        $model = new PermissionCategory($this->db);
        $model->data($data);
        try {
            $res = $model->saveCategory();
            return $res;
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * 根据主键删除权限分组(支持多主键用数组的方式传入)
     * @param int $id
     * @return bool
     * @throws Exception
     */
    public function delPermissionCategory($id = 0)
    {
        $model = new PermissionCategory($this->db);
        try {
            $res = $model->delCategory($id);
            return $res;
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * 获取权限分组
     * @param $where
     * @return array|\PDOStatement|string|\think\Collection|\think\Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getPermissionCategory($where)
    {
        $model = new PermissionCategory($this->db);
        return $model->getCategory($where);
    }

    /**
     * 编辑角色
     * @param array $data
     * @param string $permissionIds
     * @return Role
     * @throws Exception
     */
    public function createRole(array $data = [], $permissionIds = '')
    {
        $model = new Role($this->db);
        $model->data($data);
        try {
            $res = $model->saveRole($permissionIds);
            return $res;
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }

    }

    /**
     * 根据id或标准条件获取角色
     * @param $condition
     * @param bool $withPermissionId
     * @return array|\PDOStatement|string|\think\Collection|\think\Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getRole($condition, $withPermissionId = true)
    {
        $model = new Role($this->db);
        return $model->getRole($condition, $withPermissionId);
    }

    /**
     * @param $id
     * @return bool
     * @throws Exception
     * 删除角色同时将角色权限对应关系删除(注意，会删除角色分配的权限关联数据)
     */
    public function delRole($id)
    {
        $model = new Role($this->db);
        try {
            $res = $model->delRole($id);
            return $res;
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }


    /**
     * @param $userId
     * @param array $role
     * @return int|string
     * @throws Exception
     * 为用户分配角色
     */
    public function assignUserRole($userId, array $role = [])
    {
        if (empty($userId) || empty($role)) {
            throw new Exception('参数错误');
        }
        $model = new UserRole($this->db);
        $model->startTrans();
        if ($model->where('user_id', $userId)->delete() === false) {
            $model->rollback();
            throw new Exception('删除用户原有角色出错');
        }
        $userRole = [];
        foreach ($role as $v) {
            $userRole [] = ['user_id' => $userId, 'role_id' => $v];
        }
        if ($model->saveAll($userRole) === false) {
            $model->rollback();
            throw new Exception('给用户分配角色出错');
        }
        $model->commit();
        return;
    }

    /**
     * 删除用户角色
     * @param $id
     * @return bool
     * @throws Exception
     * @throws \think\exception\PDOException
     */
    public function delUserRole($id)
    {
        if (empty($id)) {
            throw new Exception('参数错误');
        }
        $model = new UserRole($this->db);
        if ($model->where('user_id', $id)->delete() === false) {
            throw new Exception('删除用户角色出错');
        }
        return true;
    }

    /**
     * 获取用户权限并缓存
     * @param $id
     * @param int $timeOut
     * @return array|bool|mixed|\PDOStatement|string|\think\Collection
     * @throws Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function cachePermission($id, $timeOut = 3600)
    {
        if (empty($id)) {
            throw new Exception('参数错误');
        }
        $model = new Permission($this->db);
        $permission = $model->userPermission($id, $timeOut);
        return $permission;
    }

    /**
     * @param $path
     * @return bool 是否有权限
     * @throws Exception
     * 检查用户有没有权限执行某操作
     */
    public function can($userId, $path)
    {
        if (empty($userId) || empty($path)) {
            throw new Exception('参数错误');
        }
        $model = new Permission($this->db);
        $isHavePermission = $model->can($userId, $path);
        return $isHavePermission;
    }

    /****************************************************************************************
     * 用户相关接口
     ***************************************************************************************/
    /**
     * 保存和编辑用户
     * @param array $data
     * @return PermissionCategory
     * @throws Exception
     */
    public function saveUser(array $data = [])
    {
        $model = new User($this->db);
        $model->data($data);
        try {
            $res = $model->saveUser();
            return $res;
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * 获取用户，支持传入查询条件
     * @param $where
     * @return array|null|\PDOStatement|string|\think\Collection|\think\Model
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @author zhouxinguo
     * @data 2020/5/21 10:19
     */
    public function getUser($where)
    {
        $model = new User($this->db);
        return $model->getUser($where);
    }


    /**
     * 根据主键删除用户(支持多主键用数组的方式传入)
     * @param int $id
     * @return bool
     * @throws Exception
     */
    public function delUser($id = 0)
    {
        $model = new User($this->db);
        try {
            $res = $model->delUser($id);
            return $res;
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

}