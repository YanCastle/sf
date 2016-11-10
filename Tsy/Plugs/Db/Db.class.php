<?php
/**
 * 创建数据库、获取字段结构等数据库操作方法
 */
namespace Tsy\Plugs\Db;

use Tsy\Library\Model;

class Db
{
    static $BACKUP_TYPE_FILE = 'file';//备份到文件
    static $BACKUP_TYPE_RETURN = 'content';//返回数据库内容
    public $Model;
    public $tablePrefix = '';
    public $db_name;
    public $tables = [];
    public $views = [];

    function __construct($name = '', $tablePrefix = '', $connection = '', $db_name = '')
    {
        $this->Model = new Model($name, $tablePrefix, $connection);
        $this->tablePrefix = $tablePrefix ? $tablePrefix : C('DB_PREFIX');
        $this->db_name = $db_name ? $db_name : C('DB_NAME');
    }

    /**
     * 获取表
     * @param string $db_prefix
     * @return mixed
     */
    function getTableList($db_prefix = '', $no_prefix = false)
    {
        if ($this->tablePrefix && !$db_prefix) {
            $db_prefix = $this->tablePrefix;
        }
        $tables = $this->Model->query('SHOW TABLES WHERE tables_in_' . $this->db_name . ' like "' . $db_prefix . '%"');
        $Tables = [];
        foreach ($tables as $table) {
            $Tables[] = $no_prefix ? str_replace($db_prefix, '', array_values($table)[0]) : array_values($table)[0];
        }
        return $Tables;
    }

    function clearAutoIncrease()
    {
    }

    function compare($from, $to)
    {
        $FromModel = new Model('', $from['DB_PREFIX'], $from);
        $ToModel = new Model('', $to['DB_PREFIX'], $to);
        $FromTables = $this->getTableList($from['DB_PREFIX'], true);
        $ToTables = $this->getTableList($to['DB_PREFIX'], true);
        $RS = [
            'From' => [
            ],
            'To' => [
            ]
        ];
        foreach (array_intersect($FromTables, $ToTables) as $TableName) {
            $FromColumns = $this->getColumns($TableName, $from['DB_PREFIX']);
            $ToColumns = $this->getColumns($TableName, $to['DB_PREFIX']);
            $FromColumns = array_map(function ($d) {
                return serialize($d);
            }, $FromColumns);
            $ToColumns = array_map(function ($d) {
                return serialize($d);
            }, $ToColumns);
            if ($FromColumns != $ToColumns) {
                $Intersect = array_intersect($ToColumns, $FromColumns);
                $RS['To'][$TableName] = $RS['From'][$TableName] = ['+' => [], '-' => []];
                $RS['To'][$TableName]['-'] = $RS['From'][$TableName]['+'] = array_map(function ($d) {
                    return unserialize($d);
                }, array_diff($FromColumns, $Intersect));
                $RS['To'][$TableName]['+'] = $RS['From'][$TableName]['-'] = array_map(function ($d) {
                    return unserialize($d);
                }, array_diff($ToColumns, $Intersect));
            }
        }
        return $RS;
    }

    function reportCompare($from, $to)
    {
        $RS = $this->compare($from, $to);
        $Tables = [];
        foreach ($RS['To'] as $TableName => $Row) {
            if (!$Row['+'] && !$Row['-']) continue;
            echo $TableName, ':', "\r\n    +".count($Row['+']).":\r\n", implode("\r\n",array_map(function ($d) {
                return "{$d['field']}({$d['type']})";
            }, $Row['+'])), "\r\n    -".count($Row['-']).":\r\n", implode("\r\n",array_map(function ($d) {
                return "{$d['field']}({$d['type']})";
            }, $Row['-'])), "\r\n\r\n";
        }
    }

    function sync($from, $to)
    {
        $FromModel = new Model('', $from['DB_PREFIX'], $from);
        $ToModel = new Model('', $to['DB_PREFIX'], $to);
        $FromTables = $this->getTableList($from['DB_PREFIX'], true);
        $ToTables = $this->getTableList($to['DB_PREFIX'], true);
        //去除前缀后坐比较
        $FromTables = str_replace($from['DB_PREFIX'], '', $FromTables);
        $ToTables = str_replace($to['DB_PREFIX'], '', $ToTables);
        $ToModel->execute('SET FOREIGN_KEY_CHECKS=0;');
        foreach (array_intersect($FromTables, $ToTables) as $TableName) {
            $FromTableModel = M($TableName,$from['DB_PREFIX'],$from);
            $ToTableModel = M($TableName,$to['DB_PREFIX'],$to);
            for ($i = 0; $i * 50 < $FromTableModel->count(); $i++) {
                $data = $FromTableModel->page($i + 1, 50)->select();
                echo $TableName,":",strval($ToTableModel->addAll($data)),"\r\n";
            }
        }
    }

    /**
     * 获取视图
     * @param string $db_prefix
     * @return mixed
     */
    function getViewList($db_prefix = '', $no_prefix = false)
    {
        if ($this->tablePrefix && !$db_prefix) {
            $db_prefix = $this->tablePrefix;
        }
        $views = $this->Model->query('SHOW VIEWS WHERE tables_in_' . $this->db_name . ' like "' . $db_prefix . '%"');
        foreach ($views as $view) {
            $this->views[] = $no_prefix ? str_replace($this->tablePrefix, '', $view["tables_in_{$this->db_name}"]) : $view["tables_in_{$this->db_name}"];
        }
        return $this->views;
    }

    /**
     * 检查表或者视图是否存在
     * @param $TableName
     * @param string $db_prefix
     * @return bool
     */
    function existTable($TableName, $db_prefix = '')
    {
        if ($this->tablePrefix) {
            $db_prefix = $this->tablePrefix;
        }
        $tables = $this->Model->query('SHOW TABLES WHERE tables_in_' . $this->db_name . ' = "' . $db_prefix . $TableName . '"');
        $views = $this->Model->query('SHOW VIEWS WHERE tables_in_' . $this->db_name . ' like "' . $db_prefix . '%"');
        return $tables || $views;
    }

    /**
     * 执行SQL导入文件
     * @param Model $Model
     * @param string $file
     * @param string $content
     * @param string $db_prefix
     * @return bool
     */
    static function build(Model $Model, $file = '', $content = '', $db_prefix = '')
    {
        if ($db_prefix == '') {
            $db_prefix = C('DB_PREFIX');
        }
        if (!$Model instanceof Model) {
            return false;
        }
        if ($file) {
            if (file_exists($file) && is_readable($file)) {
                $content = file_get_contents($file);
            } else {
                return false;
            }
        } elseif ($content) {

        } else {
            return false;
        }
        if ($content) {
            $content = preg_replace('/\/\*.+\*\/\r\n/', '', $content);
            $content = trim(sql_prefix($content, $db_prefix),"\r\n");
            $Sqls = explode(";", $content);
            if (is_array($Sqls) && count($Sqls) > 0) {
//                $Model->startTrans();
                try {
                    foreach ($Sqls as $sql) {
                        $sql = trim($sql,"\r\n");
                        if ($sql)
                           $rs = $Model->execute($sql);
                    }
                } catch (\Exception $e) {
//                    $Model->rollback();
                    return false;
                }
//                $Model->commit();
                return true;
            }
        } else {
            return false;
        }
        return true;
    }

    function backup($type, $file = false, array $tables = [])
    {

    }

    /**
     * 获取表的字段信息
     * @param array $tables
     * @param bool $prefix
     * @return array|mixed
     */
    function getColumns($tables = [], $prefix = false, $cache = APP_DEBUG)
    {
        $one = false;
        if (is_string($tables)) {
            $tables = [$tables];
            $one = true;
        }
        if (!$tables) {
            $tables = $this->getTableList();
        } else {
            if (false === $prefix) {
                //不需要加前缀
                $prefix = '';
            } elseif (true === $prefix) {
//                从当前环境中添加前缀
                $prefix = C('DB_PREFIX');
            } elseif (is_string($prefix)) {
//                设置前缀为
//                $prefix=$prefix;
            } else {
                $prefix = '';
            }
        }
        $TableColumns = [];
        //是否强制刷新
        if (!$cache) {
            foreach ($tables as $table) {
                if ($CacheColumns = cache('ColumnsCache' . $prefix . $table)) {
                    $TableColumns[$table] = $CacheColumns;
                } else {
                    $Columns = $this->Model->query("SHOW columns From {$prefix}{$table}");
                    if ($Columns) {
                        $TableColumns[$table] = $Columns;
                    }
                    cache('ColumnsCache' . $prefix . $table, $Columns);
                }
            }
        } else {
            foreach ($tables as $table) {
                $Columns = $this->Model->query("SHOW columns From {$prefix}{$table}");
                if ($Columns) {
                    $TableColumns[$table] = $Columns;
                }
                cache('ColumnsCache' . $prefix . $table, $Columns);
            }
        }
        return $one ? $TableColumns[$tables[0]] : $TableColumns;
    }
}