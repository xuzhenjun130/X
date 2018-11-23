<?php
/**
 * DB数据库
 *
 * @author xu
 * @version $Id$
 */

namespace X;

class Db extends Component
{
    /**
     * 用于存放PDO对象
     * @var \PDO
     */
    private $pdo;
    //sql 语句
    protected $sql;
    //存放实例化本类对象
    private static $instance;

    public $dns;
    public $username;
    public $password;
    public $charset;

    private $_table;

    private $_fields = "*";
    /**
     * @var string 表前缀
     */
    protected $tablePre = '';


    //禁止克隆
    private function __clone()
    {
        exit('禁止克隆DB类');
    }

    /**
     * 初始化数据库连接
     */
    public function init()
    {
        try {
            $this->pdo = new \PDO($this->dns, $this->username, $this->password, array(\PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES ' . $this->charset));
            $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        } catch (\PDOException $e) {
            exit('数据库连接错误：' . $e->getMessage());
        }
    }

    /**
     * set table pre
     * @param $pre
     * @return $this
     */
    public function setTablePre($pre){
        $this->tablePre = $pre;
        return $this;
    }

    /**
     * set table
     * @param $table
     * @return $this
     */
    public function table($table)
    {
        $this->_table = $this->tablePre . $table;
        return $this;
    }

    /**
     * set sql
     * @param $sql
     * @return $this
     */
    public function sql($sql)
    {
        $this->sql = $sql;
        return $this;
    }

    /**
     * set select
     * @param array|null $fields
     * @return $this
     */
    public function select(Array $fields = null)
    {
        $this->_fields = empty($fields) ? '*' : implode(',', $fields);
        return $this;
    }

    /**
     * 执行传入的sql，prepare、execute。并返$stmt
     * @param array $parameters 绑定参数
     * @return bool|\PDOStatement
     * @throws \Exception
     */
    public function execute($parameters = [])
    {
        $this->_fields = "*"; //重置 select
        try {
            $stmt = $this->pdo->prepare($this->sql);
            $stmt->execute($parameters);
            $stmt->setFetchMode(\PDO::FETCH_ASSOC);
            return $stmt;
        } catch (\PDOException $e) {
            \X::app()->exception($e, $this->sql);
        }
        return false;
    }

    /**
     * 新增数据
     *
     * @param array $addData 要新增的数据
     * @return int 新增行数
     * @throws \Exception
     */
    public function add(Array $addData)
    {
        $addFields = array();
        $addValues = array();
        foreach ($addData as $key => $value) {
            $addFields[] = $key;
            $addValues[] = '\'' . $value . '\'';
        }
        $fields = implode(',', $addFields);
        $values = implode(',', $addValues);
        $this->sql = "INSERT INTO {$this->_table} ($fields)VALUES($values)";
        return $this->execute()->rowCount();
    }

    /**
     * 查询表中是否存在一条数据
     * @param array $where 查询条件 没有指定下标，则不用下标拼装sql
     * @return int  0 or 1
     * @throws \Exception
     */
    public function isOne(Array $where)
    {
        $param = '';
        foreach ($where as $key => $value) {
            if (is_int($key)) { //如果下标是数字，则不用下标拼装sql
                $param .= $value . ' AND ';
            } else {
                $param .= "$key='$value' AND ";
            }
        }
        $param = substr($param, 0, -4);
        $this->sql = "SELECT count(*) FROM { $this->_table } WHERE $param LIMIT 1";
        return $this->execute()->rowCount();
    }

    /**
     * 查询表
     *
     * @param mixed $param string or array 限制参数 没有指定下标，则不用下标拼装sql
     * @param string $order
     * @param string $limit
     * @return array       返回查询结果
     * @throws \Exception
     */
    public function findAll($param = null, $order = "",$limit="")
    {
        $bindParam = [];
        $where = '';
        if (is_array($param) && !empty($param)) { //如果where是数组，则拼装where sql
            foreach ($param as $key => $value) {
                if (is_int($key)) {  //如果下标是数字，则不用下标拼装sql
                    $where .= $value . ' AND ';
                } else {
                    $where .= "$key=:$key AND ";
                    $bindParam[':' . $key] = $value;
                }
            }
            $where = 'WHERE ' . substr($where, 0, -4);
        } else { //如果$param是字符串 加 WHERE
            $where = empty($param) ? '': 'WHERE ' . $param;
        }
        if($order){
            $order = 'order by '.$order;
        }
        if(!empty($limit)){
            $limit = 'limit '.$limit;
        }
        $this->sql = "SELECT {$this->_fields} FROM {$this->_table} $where $order $limit";
        $stmt = $this->execute($bindParam);
        $result = array();
        while ($obj = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $result[] = $obj;
        }
        return $result;
    }

    /**
     * 查询单条
     * @param null $param
     * @return mixed
     * @throws \Exception
     */
    public function find($param = null)
    {
        $bindParam = [];
        $where = '';
        if (is_array($param)) { //如果where是数组，则拼装where sql
            foreach ($param as $key => $value) {
                if (is_int($key)) {  //如果下标是数字，则不用下标拼装sql
                    $where .= $value . ' AND ';
                } else {
                    $where .= "$key=:$key AND ";
                    $bindParam[':' . $key] = $value;
                }
            }
            $where = 'WHERE ' . substr($where, 0, -4);
        } else { //如果$param是字符串 加 WHERE
            $where = empty($param) ? '': 'WHERE ' . $param;
        }
        $this->sql = "SELECT {$this->_fields} FROM {$this->_table} $where limit 1";
        $stmt = $this->execute($bindParam);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * 根据id查找单条
     * @param $id
     * @param $idName
     * @return mixed
     * @throws \Exception
     */
    public function findById($id,$idName = 'id')
    {

        $this->sql = "SELECT {$this->_fields} FROM {$this->_table} where $idName= " . (int)$id;
        $stmt = $this->execute();
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * 查询有多少条记录
     *
     * @param mixed $param 限制条件
     * @return int 多少条记录
     * @throws \Exception
     */
    public function total($param = null)
    {
        $bindParam = [];
        if (is_array($param) && count($param)) {
            $where = null;
            foreach ($param as $key => $value) {
                if (is_int($key)) {  //如果下标是数字，则不用下标拼装sql
                    $where .= $value . ' AND ';
                } else {
                    $where .= "$key=:$key AND ";
                    $bindParam[':' . $key] = $value;
                }
            }
            $where = 'WHERE ' . substr($where, 0, -4);
        } else {
            $where = empty($param) ? '': 'WHERE ' . $param;
        }
        $this->sql = "SELECT COUNT(*) as c FROM {$this->_table} $where";
        $stmt = $this->execute($bindParam);
        return $stmt->fetchObject()->c;
    }

    /**
     *删除一条数据
     * @param array $where 限制条件
     * @return int  影响行数
     * @throws \Exception
     */
    public function delete(Array $where)
    {
        $param = '';
        foreach ($where as $key => $value) {
            if (is_int($key)) { //如果下标是数字，则不用下标拼装sql
                $param .= $value . ' AND ';
            } else {
                $param .= "$key='$value' AND ";
            }
        }
        $param = substr($param, 0, -4);
        $this->sql = "DELETE FROM {$this->_table} WHERE $param LIMIT 1";
        return $this->execute()->rowCount();
    }

    /**
     *更新一条是数据
     * @param array $where where
     * @param array $updateData set
     * @return int  影响行数
     * @throws \Exception
     */
    public function update(Array $where, Array $updateData)
    {
        $param = $set = '';
        foreach ($where as $key => $value) {
            if (is_int($key)) { //如果下标是数字，则不用下标拼装sql
                $param .= $value . ' AND ';
            } else {
                $param .= "$key='$value' AND ";
            }
        }
        $param = substr($param, 0, -4);

        foreach ($updateData as $key => $value) { //拼装set
            if (is_int($key)) {
                $set .= $value . ',';
            } else {
                $set .= "$key='$value',";
            }
        }
        $set = substr($set, 0, -1);
        $this->sql = "UPDATE {$this->_table} SET $set WHERE $param";
        return $this->execute()->rowCount();

    }

    /**
     * 获取指定表所有的字段的名称
     * @param $table
     * @param bool $onlyField true 仅仅返回 表的字段名称，不包含其他额外信息
     * @return array
     * @throws \Exception
     */
    public function getFields($table,$onlyField=true)
    {
        $this->sql = 'SHOW full columns FROM ' .$this->tablePre. $table;
        $stmt = $this->execute();
        $fields = array();
        $info = $stmt->fetchAll();
        if(!$onlyField) return $info;
        foreach ($info as $value) {
            $fields[] = $value['Field'];
        }
        return $fields;
    }

    /**
     *获取下一个自动 id
     * @param array $table 要查询的表
     * @return int
     * @throws \Exception
     */
    public function nextId($table)
    {
        $this->sql = "SHOW TABLE STATUS LIKE '{$this->tablePre}$table'";
        $stmt = $this->execute();
        return $stmt->fetchObject()->Auto_increment;
    }


}