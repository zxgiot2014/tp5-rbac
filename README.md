# tp51-rbac
>本扩展包是tp5的rbac包，使用了部分tp5的特性实现了关系型数据库中特殊数据结构的处理。

## 安装方法
先安装composer如果不知道怎么安装使用composer请自行百度。
打开命令行工具切换到你的tp5项目根目录

```
composer require iset/tp5-rbac
```

# 使用说明
## 配置
请将此配置加在rbac/config/app.php的配置中
```php
    // +----------------------------------------------------------------------
    // | RBAC设置,rbac模块配置依赖
    // +----------------------------------------------------------------------
    'rbac' => [
        // 验证方式 jwt(token方式)形式或者service(基于cookie)方式
        'type' => 'jwt',
        // rbac要使用的数据库配置为空则为默认库(生成表的前缀依赖此配置)
        'db' => [
            // 数据库表前缀
            'prefix' => 'xt_tp_',
            // 数据库类型
            'type' => 'mysql',
            // 服务器地址
            'hostname' => '127.0.0.1',
            // 数据库名
            'database' => 'iset_demo',
            // 用户名
            'username' => 'root',
            // 密码
            'password' => '',
            // 端口
            'hostport' => '',
        ],
        // 密码加密密钥
        'salt_token' => 'abcddfasdfsd',
        // 权限缓存前缀
        'permission_cache_prefix' => '_RBAC_PERMISSION_CACHE_'
    ],
```
## 使用说明
实例化rbac
```php
$rbac = new Rbac();
```
### 管理操作
#### 初始化rbac所需的表
```php
//可传入参数$db为数据库配置项默认为空则为默认数据库(考虑到多库的情形)
$rbac->createTable();
```
该方法会生成rbac所需要的表，一般只执行一次，为了安全，执行后会加锁，下次要执行需要删除锁文件再执行。

#### 创建权限分组
```php
$rbac->savePermissionCategory([
    'name' => '用户管理组',
    'description' => '网站用户的管理',
    'status' => 1
]);
```
编辑和修改调用同一个方法编辑时请在参数中包含主键id的值

#### 创建权限节点
```php
$rbac->createPermission([
    'name' => '文章列表查询',
    'description' => '文章列表查询',
    'status' => 1,
    'type' => 1,
    'category_id' => 1,
    'path' => 'article/content/list',
]);
```
- 如果为修改则在传入参数数组中加入主键id的键值
- type为权限类型1为后端权限2为前端权限主要考虑到spa使用
- category_id为上一步创建的权限分组的id
- 创建成功返回添加的该条权限数据，错误抛出异常
#### 创建角色&给角色分配权限
```php
$rbac->createRole([
    'name' => '内容管理员',
    'description' => '负责网站内容管理',
    'status' => 1
], '1,2,3');
```
- 如果修改请在第一个参数中传入主键的键值
- 第二个参数为权限节点的id拼接的字符串请使用英文逗号

#### 给用户分配角色
```php
$rbac->assignUserRole(1, [1]);
```
- 该方法会删除用户之前被分配的角色
- 第一个参数为用户id
- 第二个参数为角色id的数组
#### 获取权限分组列表
```php
$rbac->getPermissionCategory([['status', '=', 1]]);
```
- 参数支持传入id查询单条数据和标准的where表达式查询列表传为空数组则查询所有

#### 获取权限列表
```php
$rbac->getPermission([['status', '=', 1]]);
```
- 参数支持传入id查询单条数据和标准的where表达式查询列表传为空数组则查询所有

#### 获取角色列表
```php
$rbac->getRole([], true);
```
- 第一个参数支持传入id查询单条数据和标准的where表达式查询列表传为空数组则查询所有
- 第二个参数选择是否查询角色分配的所有权限id默认为true

#### 删除权限分组
```php
$rbac->delPermissionCategory([1,2,3,4]);
```
- 参数支持传入单个id或者id列表

#### 删除权限
```php
$rbac->delPermission([1,2,3,4]);
```
- 参数支持传入单个id或者id列表

#### 删除角色
```php
$rbac->delRole([1,2,3,4]);
```
- 参数支持传入单个id或者id列表
- 删除角色会删除给角色分配的权限[关联关系]

#### 用户请求时验证
```php
$rbac->can('article/channel/list');
```

#### 保存和编辑用户信息
```php
$rbac->saveUser([]);
```

#### 获取用户信息
```php
$rbac->getUser([]);
```
- 获取用户，支持传入查询条件

#### 删除用户信息
```php
$rbac->delUser([]);
```
- 根据主键删除用户(支持多主键用数组的方式传入)
