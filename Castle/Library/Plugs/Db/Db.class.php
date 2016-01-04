<?php
/**
 * 创建数据库、获取字段结构等数据库操作方法
 */
namespace Plugs\Db;
use Core\Controller;
use Core\Model;

class Db extends Controller{
    static $BACKUP_TYPE_FILE='file';//备份到文件
    static $BACKUP_TYPE_RETURN='content';//返回数据库内容
    public $Model;
    public $tablePrefix='';
    public $db_name;
    public $tables=[];
    function __construct($name='',$tablePrefix='',$connection='',$db_name=''){
        parent::__construct();
        $this->Model=new Model($name,$tablePrefix,$connection);
        $this->tablePrefix=$tablePrefix?$tablePrefix:C('DB_PREFIX');
        $this->db_name=$db_name?$db_name:C('DB_NAME');
    }

    /**
     * 获取表
     * @param string $db_prefix
     * @return mixed
     */
    function getTableList($db_prefix='',$no_prefix=false){
        if($this->tablePrefix&&!$db_prefix){$db_prefix=$this->tablePrefix;}
        $tables = $this->Model->query('SHOW TABLES WHERE tables_in_' . $this->db_name . ' like "' . $db_prefix . '%"');
        foreach($tables as $table){
            $this->tables[]=$no_prefix?str_replace($this->tablePrefix,'',array_values($table)[0]):array_values($table)[0];
        }
        return $this->tables;
    }

    /**
     * 获取视图
     * @param string $db_prefix
     * @return mixed
     */
    function getViewList($db_prefix='',$no_prefix=false){
        if($this->tablePrefix&&!$db_prefix){$db_prefix=$this->tablePrefix;}
        $views = $this->Model->query('SHOW VIEWS WHERE tables_in_' . $this->db_name . ' like "' . $db_prefix . '%"');
        foreach($views as $view){
            $this->views[]=$no_prefix?str_replace($this->tablePrefix,'',$view["tables_in_{$this->db_name}"]):$view["tables_in_{$this->db_name}"];
        }
        return $this->views;
    }

    /**
     * 检查表或者视图是否存在
     * @param $TableName
     * @param string $db_prefix
     * @return bool
     */
    function existTable($TableName,$db_prefix=''){
        if($this->tablePrefix){$db_prefix=$this->tablePrefix;}
        $tables = $this->Model->query('SHOW TABLES WHERE tables_in_' . $this->db_name . ' = "' . $db_prefix.$TableName . '"');
        $views = $this->Model->query('SHOW VIEWS WHERE tables_in_' . $this->db_name . ' like "' . $db_prefix . '%"');
        return $tables||$views;
    }
    static function build(Model $Model,$file='',$content='',$db_prefix=''){
        if($db_prefix==''){$db_prefix=C('DB_PREFIX');}
        if(!$Model instanceof Model){return false;}
        if($file){
            if(file_exists($file)&&is_readable($file)){
                $content = file_get_contents($file);
            }else{
                return false;
            }
        }elseif($content){

        }else{
            return false;
        }
        if($content){
            $content = preg_replace('/\/\*.+\*\/\r\n/','',$content);
            $content = str_replace('{$PREFIX}',$db_prefix,$content);
            $Sqls = explode(";\r\n",$content);
            if(is_array($Sqls)&&count($Sqls)>0){
                try{
                    foreach($Sqls as $sql){
                        if($sql)
                            $Model->execute($sql);
                    }
                }catch (\Exception $e){
                    return false;
                }
            }
        }else{
            return false;
        }
        return true;
    }
    function backup($type,$file=false,array $tables=[]){

    }
    function getColumns($tables=[]){
        if(is_string($tables)){
            $tables=[$tables];
        }
        if(!$tables){
            $tables=$this->getTableList();
        }
        $TableColumns=[];
        foreach($tables as $table){
            $Columns = $this->Model->query("SHOW columns From {$table}");
            if($Columns){
                $TableColumns[$table]=$Columns;
            }
        }
        return $TableColumns;
    }
}