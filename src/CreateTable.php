<?php
/**
 * Created by PhpStorm.
 * User: zhouxinguo <iszhouxinguo@outlook.com>
 * Date: 2020/5/15
 * Time: 17:25
 * Function：生成数据库相关操作
 */

namespace iset\rbac;


use think\Db;
use think\facade\Env;

class CreateTable
{
    private $_lockFile = '';
    private $_sqlFile = '';
    /**
     * rbac数据库配置
     * @var string
     */
    private $db = '';

    public function __construct()
    {
        $this->_lockFile = Env::get('root_path') . 'runtime/rbac_sql.lock';
        $this->_sqlFile = dirname(__DIR__) . '/iset_rbac.sql';
    }

    /**
     * 创建数据表
     * @param string $db
     */
    public function create($db = '')
    {
        // 判断是否传入rbac数据库的连接信息
        if ($db == '') {
            $dbConfig = Db::getConfig();
            $prefix = $dbConfig['prefix'];
            $this->db=$dbConfig;
        } else {
            $prefix = $db['prefix'];
            $this->db=$db;
        }
        if (file_exists($this->_lockFile)) {
            echo "<b style='color:red'>数据库创建操作被锁定，请删除[{$this->_lockFile}]文件后重试</b>";
            exit;
        }

        if ($this->_generateSql($prefix) === false) {
            echo '执行sql语句出错，请检查配置';
            exit;
        }
        echo '执行成功,如非必要请不要解锁后再次执行，重复执行会清空原有rbac表中的数据';
        $this->_writeLock();
        exit;
    }

    /**
     * 执行sql语句
     * @param string $prefix
     * @return bool
     */
    private function _generateSql($prefix = '')
    {
        $sql = $this->_loadSqlFile();
        $prefix = empty($prefix) ? '' : $prefix;
        $sql = str_replace('###', $prefix, $sql);
        $sqlArr = explode(';', $sql);
        if (Db::connect($this->db)->batchQuery($sqlArr) === false) {
            return false;
        }
        return true;
    }

    /**
     * 加载sql文件
     * @return bool|string
     */
    private function _loadSqlFile()
    {
        $fileObj = fopen($this->_sqlFile, 'r');
        $sql = fread($fileObj, filesize($this->_sqlFile));
        fclose($fileObj);
        return $sql;
    }

    /**
     * 创建数据库操作锁
     */
    private function _writeLock()
    {
        $fileObj = fopen($this->_lockFile, 'w');
        fwrite($fileObj, date("Y-m-d H:i:s") . '执行成功!');
        fclose($fileObj);
    }

}
