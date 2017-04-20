<?php
/**
 * Created by PhpStorm.
 * User: castle
 * Date: 2016/03/09
 * Time: 21:53
 */

namespace Tsy\Plugs\PowerDesigner;


class Pdm
{
    const FK='FK';
    const TABLE='TABLE';
    const COLUMN='COLUMN';
    const DOMAIN='DOMAIN';
    private $pq;
    private $Domain;
    public $json=[];
    private $IDMap=[];
    function load($file){
        if(file_exists($file)) {
            $xml = file_get_contents($file);
            $xml = str_replace('Column.Mandatory','ColumnMandatory', $xml);
            $patterns = array();
            $patterns[0] = '/(<|<\/)([a-z]{1}):(\w+)(>|\/>)/';
            $patterns[1] = '/(<|<\/)([a-z]{1}):(\w+) (Id|Ref)="(\w+)"(>|\/>)/';
            $replaces = array();
            $replaces[0] = '$1${2}$3$4';
            $replaces[1] = '$1${2}$3 $4="$5"$6';
            $xml=preg_replace($patterns,$replaces,$xml);
           // $xml = str_replace(':', '', $xml);
            vendor('phpQuery.phpQuery');
            $this->pq=\phpQuery::newDocument($xml);
            $this->getDomains();
            $ProjectInfo = $this->getProject();
            $TableInfo = $this->getTables();
            $json['Project'] = $ProjectInfo;
            $json['Tables'] = $TableInfo;
            $json['Domains'] = $this->Domain;
            $json['ForeignKeys']=$this->getForeignKeys();
            foreach ($json['ForeignKeys'] as $ID=>$FK){
                $ParentTableCode=$this->IDMap[$FK['ParentTableID']]['Code'];
                $ChildTableCode=$this->IDMap[$FK['ChildTableID']]['Code'];
                $ParentColumnCode=$this->IDMap[$FK['ParentColumnID']]['Code'];
                $ChildColumnCode=$this->IDMap[$FK['ChildColumnID']]['Code'];
                $FKProperty=[
                    'ParentTable'=>$json['Tables'][$ParentTableCode],
                    'ParentTableCode'=>$ParentTableCode,
                    'ParentTableColumnCode'=>$ParentColumnCode,
                    'ChildTable'=>$json['Tables'][$ChildTableCode],
                    'ChildTableCode'=>$ChildTableCode,
                    'ChildTableColumnCode'=>$ChildColumnCode,
                    'Properties'=>$FK
                ];
                $json['Tables'][$ParentTableCode]['FKs']['Parent'][]=array_merge($FKProperty,['Type'=>'Parent']);
                $json['Tables'][$ChildTableCode]['FKs']['Child'][]=array_merge($FKProperty,['Type'=>'Child']);
            }
            $this->json=$json;
            return true;
        }
        return false;
    }
    function errorCheck(){
        //检查Index无Columns的错误
        foreach (pq('oIndex') as $oIndex){
            
        }
    }
    /**
     * 获取项目信息
     * @return array|bool
     */
    public function getProject(){
        $ProjectInfo=[
            'Name'=>pq('oModel aName:first')->html(),
            'Comment'=>pq('oModel aComment:first')->html(),
        ];
        return $ProjectInfo['Name']?$ProjectInfo:false;
    }

    /**
     * 获取表信息
     * @return array
     */
    public function getTables(){
        $Tables=[];
        foreach(pq('cTables oTable') as $oTable){
            $Table=[
                'ID'=>pq($oTable)->attr('Id'),
                'Name'=>pq($oTable)->find('aName:first')->html(),
                'Code'=>pq($oTable)->find('aCode:first')->html(),
                'Comment'=>pq($oTable)->find('aComment:first')->html(),
                'FKs'=>[
                    'Parent'=>[],//此表为父表时
                    'Child'=>[]//此表为子表时
                ]
            ];
            $Columns=[];
            $PK = pq($oTable)->find('cPrimaryKey oKey')->attr('Ref');
            $PK = pq($oTable)->find("[Id={$PK}]")->find('oColumn')->attr('Ref');
            $PKColumn='';
            foreach(pq($oTable)->find('oColumn') as $oColumn){
                $ColumnID=pq($oColumn)->attr('Id');
                if(null===$ColumnID){
                    $html = pq($oColumn)->html();
                    continue;
                }
                //获取扩展属性
                $ExtendProperties = explode(',',pq($oColumn)->find('aExtendedAttributesText')->html());
                //判断是否是Unsigned属性
                $Unsigned = in_array('Unsigned',$ExtendProperties);
                $Code = pq($oColumn)->find('aCode')->html();
                $Column=[
                    'Name'=>pq($oColumn)->find('aName')->html(),
                    'Code'=>$Code,
                    'Comment'=>pq($oColumn)->find('aComment')->html(),
                    'DataType'=>pq($oColumn)->find('aDataType')->html(),
                    'I'=>pq($oColumn)->find('aIdentity')->html()==1,
                    'M'=>pq($oColumn)->find('aColumnMandatory')->html(),
                    'U'=>$Unsigned,
                    'ID'=>$ColumnID,
                    'P'=>$PK?$PK==$ColumnID:0,
                    'DomainID'=>pq($oColumn)->find('cDomain oPhysicalDomain')->attr('Ref'),
                    'DefaultValue'=>pq($oColumn)->find('aDefaultValue')->html(),
                ];
                $Column['DefaultValue']=$Column['DefaultValue']==false?"":$Column['DefaultValue'];
                $Columns[$Code]=$Column;
                if($PK&&$PK==$ColumnID){
                    $PKColumn=$Code;
                }
                $this->IDMap[$ColumnID]=array_merge($Column,['Type'=>self::COLUMN]);
            }
            $Table['Columns']=$Columns;
            $Table['PK']=$PKColumn;
            $Tables[$Table['Code']]=$Table;
            $this->IDMap[$Table['ID']]=array_merge($Table,['Type'=>self::TABLE]);
        }
        return $Tables;
    }

    /**
     * 获取
     */
    public function getDomains(){
        foreach(pq('cDomains oPhysicalDomain') as $oPhysicalDomain){
            $Domain = [
                'ID'=>pq($oPhysicalDomain)->attr('Id'),
                'Name'=>pq($oPhysicalDomain)->find('aName:first')->html(),
                'Code'=>pq($oPhysicalDomain)->find('aCode:first')->html(),
                'Comment'=>pq($oPhysicalDomain)->find('aComment:first')->html(),
                'DataType'=>pq($oPhysicalDomain)->find('aDataType:first')->html(),
            ];
            $this->Domain[$Domain['ID']]=$Domain;
            $this->IDMap[$Domain['ID']]=array_merge($Domain,['Type'=>self::DOMAIN]);
        }
    }
    public function getForeignKeys(){
        $References=[];
        foreach(pq('cReferences oReference') as $oReference){
            $ID=pq($oReference)->attr('Id');
            $Reference = [
                'ID'=>$ID,
                'Name'=>pq($oReference)->find('aName:first')->html(),
                'Code'=>pq($oReference)->find('aCode:first')->html(),
                'Comment'=>pq($oReference)->find('aComment:first')->html(),
                'ParentTableID'=>pq($oReference)->find('cParentTable oTable')->attr('Ref'),
                'ChildTableID'=>pq($oReference)->find('cChildTable oTable')->attr('Ref'),
                'ParentColumnID'=>pq($oReference)->find('cJoins oReferenceJoin cObject1 oColumn')->attr('Ref'),
                'ChildColumnID'=>pq($oReference)->find('cJoins oReferenceJoin cObject2 oColumn')->attr('Ref'),
            ];
            $References[]=$Reference;
            $this->IDMap[$ID]=array_merge($Reference,['Type'=>self::FK]);
//            $this->Reference[$Domain['ID']]=$Domain;
        }
        return $References;
//        $a=1;
    }
    /**检测PDM文件
     * @param $file被检测文件的路径及文件名
     * @param $errPath存放错误日志的文件路径及文件名
     * @return array 返回包含错误对应的类型，表ID,字段ID的数组
     */
    function checkPDM($file,$errPath){
        fclose(fopen($errPath,'w'));
        $errLog = fopen($errPath,'a+');
        $errArray = [];
        $Pdm = new \Document($file);
        $data = \Document::$docs['PDM'];
        $tables = $data['Tables'];
        foreach($tables as $k => &$v){
            preg_match('/prefix_[a-zA-Z_]*/',$v['Code'],$tableName);   //表名前缀必须为prefix_
            if(!isset($tableName[0])){
                $errArray['TablePrefix'][]=[
                    'TableID' => $v['ID'],
                    'TableCode' => $v['Code']
                ];
                fwrite($errLog,$k."表未使用表名前缀prefix_\r\n");
            }
            $pk = false;
            foreach($v['Columns'] as $key => $value){
                preg_match('/[a-z]*/',$value['DataType'],$type);
                $col = $value['Code'];
                $num = ord(substr($col,0,1));   //字段名首字母大写
                if($num>96&&$num<123){
                    $errArray['CodeFirst'][] = [
                        'ColumnID' => $value['ID'],
                        'Code' => $col
                    ];
                    fwrite($errLog,$k."表中的".$col."字段首字母未大写\r\n");
                }
                if($value['P'] == true){    //检测是否是主键
                    $pk = true;
                }
                if($value['I'] == true){
                    if($value['U'] == false){   //自增字段必须为unsigned
                        $errArray['IU'][] = [
                            'TableID' => $v['ID'],
                            'ColumnID' => $value['ID']
                        ];
                        fwrite($errLog,$k."表中的".$col."字段为自增类型却未设置为unsigned\r\n");
                    }
                    if($type[0] != 'int' && $type[0] != 'bigint'){   //自增字段必须为整型或大整型
                        $errArray['IType'][] = [
                            'TableID' => $v['ID'],
                            'ColumnID' => $value['ID'],
                            'DataType' => $type[0]
                        ];
                        fwrite($errLog,$k."表中的".$col."字段为自增类型数据类型却不是整型或大整型\r\n");
                    }
                }
                preg_match('/[a-z]*ID/',$col,$code);
                if(isset($code[0])){              //以ID结尾的字段必须是整型或大整型
                    if($type[0] != 'int' && $type[0] != 'bigint'){
                        $errArray['ID'][] = [
                            'TableID' => $v['ID'],
                            'ColumnID' => $value['ID'],
                            'DataType' => $type[0]
                        ];
                        fwrite($errLog,$k."表中的".$col."字段以ID结尾却设置为整型或者大整型\r\n");
                    }
                }
                if($type[0] != 'int'&& $type[0] !='double' && $value['U'] == true){   //检测字段数据类型是否错误
                    $errArray['Unsigned'][] = [
                        'TableID' => $v['ID'],
                        'ColumnID' => $value['ID']
                    ];
                    fwrite($errLog,$k."表中的".$col."字段为".$type[0]."却设置为unsigned\r\n");
                }
            }
            if($pk == false){      //检测表是否存在主键
                $errArray['PK'][] = [
                    'TableID' => $v['ID'],
                    'ColumnID' =>current($v['Columns'])['ID']
                ];
                fwrite($errLog,$k."表缺少主键\r\n");
            }
            if(isset($v['FKs']['Parent'])){
                $parents = $v['FKs']['Parent'];
                foreach($parents as $m => $n){
                    $PID = $n['ParentTableColumnCode'];
                    $CID = $n['ChildTableColumnCode'];
                    $pType = $n['ParentTable']['Columns'][$PID]['DataType'];
                    $cType = $n['ChildTable']['Columns'][$CID]['DataType'];
                    if($pType !== $cType){
                        $PName = $n['ParentTableCode'];
                        $CName = $n['ChildTableCode'];
                        $PCode = $n['ParentTableColumnCode'];
                        $CCode = $n['ChildTableColumnCode'];
                        $errArray['FKs'][] = [
                            'ParentTableID' => $n['ParentTable']['ID'],
                            'ParentColumnID' => $n['ParentTable']['Columns'][$PID]['ID'],
                            'ParentColumnDataType' => $pType,
                            'ChildTableID' => $n['ChildTable']['ID'],
                            'ChildColumnID' => $n['ChildTable']['Columns'][$CID]['ID'],
                            'ChildColumnDataType' => $cType
                        ];
                        fwrite($errLog,$PName."表字段".$PCode."与".$CName."表字段".$CCode."外键的数据类型不匹配\r\n");
                    }
                }
            }
            if(isset($v['FKs']['Child'])){
                $child = $v['FKs']['Child'];
                foreach($child as $m => $n) {
                    $PID = $n['ParentTableColumnCode'];
                    $CID = $n['ChildTableColumnCode'];
                    $pType = $n['ParentTable']['Columns'][$PID]['DataType'];
                    $cType = $n['ChildTable']['Columns'][$CID]['DataType'];
                    if ($pType !== $cType) {
                        $PName = $n['ParentTableCode'];
                        $CName = $n['ChildTableCode'];
                        $PCode = $n['ParentTableColumnCode'];
                        $CCode = $n['ChildTableColumnCode'];
                        $errArray['FKs'][] = [
                            'ParentTableID' => $n['ParentTable']['ID'],
                            'ParentColumnID' => $n['ParentTable']['Columns'][$PID]['ID'],
                            'ParentColumnDataType' => $pType,
                            'ChildTableID' => $n['ChildTable']['ID'],
                            'ChildColumnID' => $n['ChildTable']['Columns'][$CID]['ID'],
                            'ChildColumnDataType' => $cType
                        ];
                        fwrite($errLog,$PName."表字段".$PCode."与".$CName."表字段".$CCode."外键的数据类型不匹配\r\n");
                    }
                }
            }
        }
        fwrite($errLog,"检测完毕\r\n");
        fclose($errLog);
        return $errArray;
    }

    /**修复PDM文件中存在的错误
     * @param $errArray错误数组，由方法checkPDM()得到
     * @param $outFile修复完成后输出的文件路径及文件名
     * @return bool|string
     */
    function repairPDM($errArray,$outFile){
        if(!isset($errArray)){
            return '待修复的内容为空';
        }
        $Unsigned = $errArray['Unsigned'];
        $PK = $errArray['PK'];
        $FKs = $errArray['FKs'];
        $TablePrefix = $errArray['TablePrefix'];
        $IU = $errArray['IU'];
        $IType = $errArray['IType'];
        $ID = $errArray['ID'];
        $CodeFirst = $errArray['CodeFirst'];
        if(isset($Unsigned)){                       //修复非整型未unsigned
            foreach($Unsigned as $k => $v){
                $id = $v['ColumnID'];
                pq("[Id=$id]")->find('aExtendedAttributesText')->html(str_replace(',Unsigned','', pq("[Id=$id]")->find('aExtendedAttributesText')->html()));
            }
        }
        if(isset($PK)){             //修复表缺少主键
            foreach($PK as $k => $v){
                $id = $v['ColumnID'];
                $TableId = $v['TableID'];
                $string = " <cKey.Columns>
   <oColumn Ref=\"$id\"/>
 </cKey.Columns>";
                $PK = pq("[Id=$TableId]")->find('cPrimaryKey oKey')->attr('Ref');
                $data=pq("[Id=$PK]")->html();
                $data.=$string;
                pq("[Id=$PK]")->html(str_replace(pq("[Id=$PK]")->html(),$data,pq("[Id=$PK]")->html()));

            }
        }
        if(isset($FKs)){                  //修复表外键字段数据类型不统一
            foreach($FKs as $k => $v){
                $id = $v['ChildColumnID'];
                $DataType = $v['ParentColumnDataType'];
                preg_match('/\([0-9]*\)/',$DataType,$len);
                $num=str_replace(')','',str_replace('(','',$len));
                pq("[Id=$id]")->find('aDataType')->html(str_replace(pq("[Id=$id]")->find('aDataType')->html(),$DataType,pq("[Id=$id]")->find('aDataType')->html()));
                pq("[Id=$id]")->find('aLength')->html(str_replace(pq("[Id=$id]")->find('aLength')->html(),$num[0],pq("[Id=$id]")->find('aLength')->html()));
            }
        }
        if(isset($TablePrefix)){        //修复表明前缀为非prefix_
            foreach($TablePrefix as $k => $v){
                $id = $v['TableID'];
                $Code = 'prefix_'.$v['TableCode'];
                pq("[Id=$id]")->find('aCode:first')->html(str_replace(pq("[Id=$id]")->find('aCode:first')->html(),$Code,pq("[Id=$id]")->find('aCode:first')->html()));
            }
        }
        if(isset($IU)){              //修复自增类型的字段为非unsigned
            foreach($IU as $k => $v){
                $id = $v['ColumnID'];
                $string = "<aExtendedAttributesText>{F4F16ECD-F2F1-4006-AF6F-638D5C65F35E},MYSQL50,56={4A2BD2F3-4A8A-4421-8A48-A8029BDA28E8},Unsigned,4=true

</aExtendedAttributesText>";
                $un = pq("[Id=$id]")->html();
                $data=$un.$string;
                pq("[Id=$id]")->html(str_replace(pq("[Id=$id]")->html(),$data,pq("[Id=$id]")->html()));
            }
        }
        if(isset($IType)){           //修复自增字段为非整型
            foreach($IType as $k => $v){
                $id = $v['ColumnID'];
                $DataType = $v['DataType'];
                preg_match('/\([0-9]*\)/',$DataType,$len);
                $num=str_replace(')','',str_replace('(','',$len));
                pq("[Id=$id]")->find('aDataType')->html(str_replace(pq("[Id=$id]")->find('aDataType')->html(),"int($num[0])",pq("[Id=$id]")->find('aDataType')->html()));
            }
        }
        if(isset($ID)){            //修复以ID结尾的字段为非整型
            foreach($ID as $k => $v){
                $id = $v['ColumnID'];
                $DataType = $v['DataType'];
                preg_match('/\([0-9]*\)/',$DataType,$len);
                $num=str_replace(')','',str_replace('(','',$len));
                pq("[Id=$id]")->find('aDataType')->html(str_replace(pq("[Id=$id]")->find('aDataType')->html(),"int($num[0])",pq("[Id=$id]")->find('aDataType')->html()));
            }
        }
        if(isset($CodeFirst)){              //修复字段名首字母未大写
            foreach($CodeFirst as $k => $v){
                $id = $v['ColumnID'];
                $Code = ucfirst($v['Code']);
                pq("[Id=$id]")->find('aCode')->html(str_replace(pq("[Id=$id]")->find('aCode')->html(),$Code,pq("[Id=$id]")->find('aCode')->html()));
            }
        }
        $html = pq('')->html();
        $patterns = array();
        $patterns[0] = '/(<|<\/)([a-z]{1})(\w+)(>|\/>)/';
        $patterns[1] = '/(<|<\/)([a-z]{1})(\w+) (Id|Ref)="(\w+)"(>|\/>)/';
        $replaces = array();
        $replaces[0] = '$1${2}:$3$4';
        $replaces[1] = '$1${2}:$3 $4="$5"$6';
        $html=preg_replace($patterns,$replaces,$html);
        $xml = str_replace('ColumnMandatory', 'Column.Mandatory', $html);
        file_put_contents($outFile,$xml);
        return true;
    }
    /**获取数据库中的数据
     * @return array 返回保存数据库的数据信息的数组
     */
    function getTablesFromSql(){
        $tables = [];
        $config = [
            'DB_NAME' => C('DB_NAME')
        ];
        $prefix = [];
        $tableName = M()->query("select `TABLE_NAME`,`TABLE_COMMENT` from information_schema.TABLES where `TABLE_SCHEMA` = '{$config["DB_NAME"]}' and `TABLE_TYPE` = 'BASE TABLE'");
        if($tableName){                       //修改表前缀，若有表前缀则改为prefix_,若没有表前缀，则添加prefix_
            foreach($tableName as $item){
                preg_match('/[a-zA-Z]*_/',$item['TABLE_NAME'],$prefix[]);
            }
            $same = array_unique($prefix);
            if(count($same)==1&&count($tableName)!=1){
                foreach($tableName as $k=>$item){
                    $tableName[$k]['TABLE_NAME']=str_replace($same[0][0],'prefix_',$item['TABLE_NAME']);
                }
            }else{
                foreach($tableName as $k=>$item){
                    $tableName[$k]['TABLE_NAME'].='prefix_';
                }
            }
        }
        if($tableName){
            foreach($tableName as $k=>$item){
                $name = preg_replace('/(prefix_)([a-zA-Z_]*)/','$2',$item['TABLE_NAME']);
                $tables[$name] = [
                    'ID' => '',
                    'Name' => '',
                    'Code' => $item['TABLE_NAME'],
                    'Comment' => $item['TABLE_COMMENT']?$item['TABLE_COMMENT']:false,
                    'FKs' => [
                        'Parent' => [],
                        'Child' => []
                    ],
                    'Columns' => [],
                    'PK' => ''
                ];
                $columns = M()->query("SHOW FULL COLUMNS FROM {$item['TABLE_NAME']} FROM {$config['DB_NAME']}");
                foreach($columns as $k){
                    if($k['Key'] == 'PRI'){
                        $tables[$name]['Columns'][$k['Field']] = [
                            'P' => true,
                            'I' => true,
                        ];
                        $tables[$name]['PK'] = $k['Field'];
                    }else{
                        $tables[$name]['Columns'][$k['Field']] = [
                            'P' => false,
                            'I' => false,
                        ];
                    }
                    if($k['Null'] == 'NO'){
                        $tables[$name]['Columns'][$k['Field']]['M'] = true;
                    }else{
                        $tables[$name]['Columns'][$k['Field']]['M'] = false;
                    }
                    preg_match('/unsigned/',$k['Type'],$u);
                    if($u){
                        $tables[$name]['Columns'][$k['Field']]['U'] = true;
                    }else{
                        $tables[$name]['Columns'][$k['Field']]['U'] = '';
                    }
                    preg_match('/[\S]*/',$k['Type'],$type);
                    $tables[$name]['Columns'][$k['Field']]['Name'] = '';
                    $tables[$name]['Columns'][$k['Field']]['Code'] = $k['Field'];
                    $tables[$name]['Columns'][$k['Field']]['Comment'] = $k['Comment']?$k['Comment']:false;
                    $tables[$name]['Columns'][$k['Field']]['DataType'] = $type[0];
                    $tables[$name]['Columns'][$k['Field']]['ID'] = '';
                    $tables[$name]['Columns'][$k['Field']]['DomainID'] = '';
                    $tables[$name]['Columns'][$k['Field']]['DefaultValue'] = $k['Default'];
                }
            }

            foreach($tableName as $item){
                $name = preg_replace('/(prefix_)([a-zA-Z_]*)/','$2',$item['TABLE_NAME']);;
                $foreign = M()->query("select `TABLE_NAME`,`COLUMN_NAME`,`REFERENCED_TABLE_NAME`,`REFERENCED_COLUMN_NAME`,`CONSTRAINT_NAME` from information_schema.KEY_COLUMN_USAGE where `TABLE_NAME` = '{$item['TABLE_NAME']}' and `CONSTRAINT_SCHEMA` = '{$config['DB_NAME']}'");
                $arr =[];
                $ParentData = [];
                foreach($foreign as $f){
                    if($f['REFERENCED_TABLE_NAME']!=null){
                        $arr[] = $f;
                        $m = preg_replace('/(prefix_)([a-zA-Z_]*)/','$2',$f['REFERENCED_TABLE_NAME']);
                        $ParentData[$m][] = $f;
                    }
                }
                if(isset($arr)){
                    foreach($arr as $a){
                        $ChildName = preg_replace('/(prefix_)([a-zA-Z_]*)/','$2',$a['TABLE_NAME']);
                        $ParentName = preg_replace('/(prefix_)([a-zA-Z_]*)/','$2',$a['REFERENCED_TABLE_NAME']);
                        $tables[$name]['FKs']['Child'][] = [
                            'ParentTable' => $tables[$ParentName],
                            'ParentTableCode' => $a['REFERENCED_TABLE_NAME'],
                            'ParentTableColumnCode' => $a['REFERENCED_COLUMN_NAME'],
                            'ChildTable' => $tables[$ChildName],
                            'ChildTableCode' => $a['TABLE_NAME'],
                            'ChildTableColumnCode' => $a['COLUMN_NAME'],
                            'Properties' => [
                                'ID' => '',
                                'NAME' => $a['CONSTRAINT_NAME'],
                                'Code' => $a['CONSTRAINT_NAME'],
                                'Comment' => '',
                                'ParentTableID' => '',
                                'ChildTableID' => '',
                                'ParentColumnID' => '',
                                'ChildColumnID' => '',
                            ],
                            'Type' => 'Child',
                        ];
                    }
                }
                if(isset($ParentData)){
                    foreach($ParentData as $k => $v){
                        foreach($v as $vs){
                            $ChildName = preg_replace('/(prefix_)([a-zA-Z_]*)/','$2',$vs['TABLE_NAME']);
                            $ParentName = preg_replace('/(prefix_)([a-zA-Z_]*)/','$2',$vs['REFERENCED_TABLE_NAME']);
                            $tables[$k]['FKs']['Parent'][] = [
                                'ParentTable' => $tables[$ParentName],
                                'ParentTableCode' => $vs['REFERENCED_TABLE_NAME'],
                                'ParentTableColumnCode' => $vs['REFERENCED_COLUMN_NAME'],
                                'ChildTable' => $tables[$ChildName],
                                'ChildTableCode' => $vs['TABLE_NAME'],
                                'ChildTableColumnCode' => $vs['COLUMN_NAME'],
                                'Properties' => [
                                    'ID' => '',
                                    'NAME' => $vs['CONSTRAINT_NAME'],
                                    'Code' => $vs['CONSTRAINT_NAME'],
                                    'Comment' => '',
                                    'ParentTableID' => '',
                                    'ChildTableID' => '',
                                    'ParentColumnID' => '',
                                    'ChildColumnID' => '',
                                ],
                                'Type' => 'Parent',
                            ];
                        }

                    }
                }
            }
        }
        return $tables;
    }

    /**获取pdm文件中的数据
     * @param $file文件的路径及文件名
     * @return mixed 返回保存pdm文件的数据信息的数组
     */
    function getTablesFromPDM($file){
        $Pdm = new \Document($file);
        $data = \Document::$docs['PDM'];
        $tables = $data['Tables'];
        return $tables;
    }

    /**比较数据库中与pdm文件中的数据
     * @param $file文件的路径及文件名
     * @return array 返回通过比较获得的数组
     */
    function checkSqlPdm($file){
        $file = file_get_contents($file);
        //获取pdm文件中的最大id
        $ids = [];
        preg_match_all('/Id="o[0-9]*"/',$file,$match);
        foreach($match as $k => $v){
            foreach($v as $m => $n){
                preg_match('/[0-9]+/',$n,$id);
                $ids[] = $id[0];
            }
        }
        $pos = array_search(max($ids),$ids);
        $max = $ids[$pos];
        $SqlTables = $this->getTablesFromSql();
        $PdmTables = $this->getTablesFromPDM($file);
        $ChangeArray = [];
        //比较表名
        $SqlTablesName = [];
        $PdmTablesName = [];
        foreach($SqlTables as $k => $v){
            $SqlTablesName[] = $k;
        }
        foreach($PdmTables as $k => $v){
            $PdmTablesName[] = $k;
        }
        $UniqueTableName = array_intersect($SqlTablesName,$PdmTablesName);
        $addTableName = array_diff($SqlTablesName,$UniqueTableName);
        $deleteTableName = array_diff($PdmTablesName,$UniqueTableName);
        if($addTableName){
            foreach($addTableName as $k => $v){
                $max++;
                $SqlTables[$v]['ID'] = "o".$max;   //为新增的表编号
                foreach($SqlTables[$v]['Columns'] as $key => $key_value){  //为新增的字段编号
                    $max++;
                    $SqlTables[$v]['Columns'][$key]['ID'] = "o".$max;
                }
                $ChangeArray['TableName']['addTableName'][] = $SqlTables[$v];
            }
        }
        if($deleteTableName){
            foreach($deleteTableName as $k => $v){
                $ChangeArray['TableName']['deleteTableName'][] = $PdmTables[$k];
            }
        }
        foreach($UniqueTableName as $k => $v){
            $SqlTables[$v]['ID'] = $PdmTables[$v]['ID'];
            //比较主键
            $SqlKey = $SqlTables[$v]['PK'];
            $PdmKey = $PdmTables[$v]['PK'];
            if($SqlKey != $PdmKey){
                $ChangeArray['PK'][] = [
                    'TableID' => $PdmTables[$v]['ID'],
                    'PK' => $SqlKey,
                    'PkID' => $SqlTables[$v]['Columns'][$SqlKey]['ID']
                ];
            }
            //比较字段名
            $SqlColumnName = [];
            $PdmColumnName = [];
            foreach($SqlTables[$v]['Columns'] as $kc => $kv)
            {
                $SqlColumnName[] = $kc;
            }
            foreach($PdmTables[$v]['Columns'] as $kc => $kv){
                $PdmColumnName[] = $kc;
            }
            $UniqueColumnName = array_intersect($SqlColumnName,$PdmColumnName);
            $addColumnName = array_diff($SqlColumnName,$UniqueColumnName);
            $deleteColumnName = array_diff($PdmColumnName,$UniqueColumnName);
            if($addColumnName){
                foreach($addColumnName as $i => $m){
                    $max++;
                    $SqlTables[$v]['Columns'][$m]['ID'] = "o".$max;
                    $ChangeArray['ColumnName']['addColumnName'][$v] [] = [
                        'Column' => $SqlTables[$v]['Columns'][$m],
                        'TableID' => $PdmTables[$v]['ID']
                    ];
                }
            }
            if($deleteColumnName){
                foreach($deleteColumnName as $i => $m){
                    $ChangeArray['ColumnName']['deleteColumnName'][$v][] = [
                        'Column' => $PdmTables[$v]['Columns'][$m],
                        'TableID' => $PdmTables[$v]['ID']
                    ];
                }
            }
            //比较字段属性
            foreach($UniqueColumnName as $vk => $vv){
                $SqlTables[$v]['Columns'][$vv]['ID'] = $PdmTables[$v]['Columns'][$vv]['ID'];
                $SqlColumnValue = [];
                $SqlColumnValue['Code'] = $SqlTables[$v]['Columns'][$vv]['Code'];
                $SqlColumnValue['Comment'] = $SqlTables[$v]['Columns'][$vv]['Comment'];
                $SqlColumnValue['DataType'] = $SqlTables[$v]['Columns'][$vv]['DataType'];
                $SqlColumnValue['I'] = $SqlTables[$v]['Columns'][$vv]['I'];
                $SqlColumnValue['M'] = $SqlTables[$v]['Columns'][$vv]['M'];
                $SqlColumnValue['U'] = $SqlTables[$v]['Columns'][$vv]['U'];
                $SqlColumnValue['P'] = $SqlTables[$v]['Columns'][$vv]['P'];
                $SqlColumnValue['DefaultValue'] = $SqlTables[$v]['Columns'][$vv]['DefaultValue'];
                $PdmColumnValue = [];
                $PdmColumnValue['Code'] = $PdmTables[$v]['Columns'][$vv]['Code'];
                $PdmColumnValue['Comment'] = $PdmTables[$v]['Columns'][$vv]['Comment'];
                $PdmColumnValue['DataType'] = $PdmTables[$v]['Columns'][$vv]['DataType'];
                $PdmColumnValue['I'] = $PdmTables[$v]['Columns'][$vv]['I'];
                $PdmColumnValue['M'] = $PdmTables[$v]['Columns'][$vv]['M'];
                $PdmColumnValue['U'] = $PdmTables[$v]['Columns'][$vv]['U'];
                $PdmColumnValue['P'] = $PdmTables[$v]['Columns'][$vv]['P'];
                $PdmColumnValue['DefaultValue'] = $PdmTables[$v]['Columns'][$vv]['DefaultValue'];
                $UniqueColumnValue = array_intersect_assoc($SqlColumnValue,$PdmColumnValue);
                $SaveColumnValue = array_diff_assoc($PdmColumnValue,$UniqueColumnValue);
                if($SaveColumnValue){
                    $ChangeArray['ColumnValue']['saveColumnValue'][] = [
                        'Column' => $SqlColumnValue,
                        'ColumnID' =>  $PdmTables[$v]['Columns'][$vv]['ID']
                    ];
                }
            }
            //比较外键
            $SqlFKs = $SqlTables[$v]['FKs'];
            $PdmFKs = $PdmTables[$v]['FKs'];
            //判断外键关系下的Parent外键关系是否发生改变
            if($SqlFKs['Parent']){
                //判断SqlFks和PdmFKs中的Parent中的关系是否相同
                if($PdmFKs['Parent']){
                    //初始化标志位
                    $count = count($PdmFKs['Parent']);
                    $flag = [];
                    for($i=0;$i<$count;$i++){
                        $flag[$i] = 0;
                    }
                    $i = 0;
                    foreach($SqlFKs['Parent'] as $SFk => $SFk_value){
                        $judge = false;
                        $SqlPropertiesCode = $SFk_value['Properties']['Code'];
                        $SqlPropertiesCode = str_replace("FK_","",$SqlPropertiesCode);
                        $SqlFKsValue = [];
                        $SqlFKsValue = [
                            'ParentTableCode' => $SFk_value['ParentTableCode'],
                            'ParentTableColumnCode' => $SFk_value['ParentTableColumnCode'],
                            'ChildTableCode' => $SFk_value['ChildTableCode'],
                            'ChildTableColumnCode' => $SFk_value['ChildTableColumnCode']
                        ];
                        foreach($PdmFKs['Parent'] as $PFk => $PFk_value){
                            $PdmPropertiesCode = $PFk_value['Properties']['Code'];
                            $PdmFKsValue = [];
                            $PdmFKsValue = [
                                'ParentTableCode' => $PFk_value['ParentTableCode'],
                                'ParentTableColumnCode' => $PFk_value['ParentTableColumnCode'],
                                'ChildTableCode' => $PFk_value['ChildTableCode'],
                                'ChildTableColumnCode' => $PFk_value['ChildTableColumnCode']
                            ];
                            //判断相同外键名的外键关系是否有修改，若有修改，则将$judge置为true,表明该外键关系存在，不是额外增加的
                            if($SqlPropertiesCode == $PdmPropertiesCode){
                                $flag[$i] = 1;
                                $i++;
                                $judge = true;
                                $diff = array_diff($SqlFKsValue,$PdmFKsValue);
                                if($diff) {
                                    if ($PdmTables[$v]['Columns'][$PFk_value['Properties']['ParentTableColumnCode']]) {
                                        $ParentColumnID = $PdmTables[$v]['Columns'][$PFk_value['Properties']['ParentTableColumnCode']]['ID'];
                                    } else {
                                        $ParentColumnID = $SqlTables[$v]['Columns'][$SFk_value['Properties']['ParentTableColumnCode']]['ID'];
                                    }
                                    $name = preg_replace('/(prefix_)([a-zA-Z_]*)/', '$2', $PFk_value['Properties']['ChildTableCode']);
                                    if ($PdmTables[$name]) {
                                        $ChildTableID = $PdmTables[$name]['ID'];
                                    } else {
                                        $ChildTableID = $SqlTables[$name]['ID'];
                                    }
                                    if ($PdmTables[$name]['Columns'][$PFk_value['Properties']['ChildTableColumnCode']]) {
                                        $ChildColumnID = $PdmTables[$name]['Columns'][$PFk_value['Properties']['ChildTableColumnCode']]['ID'];
                                    } else {
                                        $ChildColumnID = $SqlTables[$name]['Columns'][$SFk_value['Properties']['ChildTableColumnCode']]['ID'];
                                    }
                                    $Code =str_replace('FK_','',$SFk_value['Properties']['Code']);
                                    $ChangeArray['FKs']['saveProperties'][] = [
                                        'ID' => $PFk_value['Properties']['ID'],
                                        'Name' => $SFk_value['Properties']['Name'],
                                        'Code' => $Code,
                                        'Comment' => $PFk_value['Properties']['Comment'],
                                        'ParentTableID' => $PFk_value['Properties']['ParentTableID'],
                                        'ParentColumnID' => $ParentColumnID,
                                        'ChildTableID' => $ChildTableID,
                                        'ChildColumnID' => $ChildColumnID
                                    ];
                                }
                            }
                            //判断不同外键名的外键关系是否相同，若相同则表明外键名有修改，则将$judge置为true,表明该外键关系存在，不是额外增加的
                            else{
                                $diff = array_diff($SqlFKsValue,$PdmFKsValue);
                                if(!$diff){
                                    $judge = true;
                                    $flag[$i] = 1;
                                    $i++;
                                    $Code =str_replace('FK_','',$SFk_value['Properties']['Code']);
                                    $ChangeArray['FKs']['savePropertiesName'][] = [
                                        'PropertiesID' => $PFk_value['Properties']['ID'],
                                        'Name' => $SFk_value['Properties']['Name'],
                                        'Code' => $Code
                                    ];
                                }
                            }
                        }
                        //如果没有发生外键的修改和外键关系名的修改，那么则为添加的外键关系
                        if($judge == false){
                            $max++;
                            $ID = "o".$max;
                            $name1 = preg_replace('/(prefix_)([a-zA-Z_]*)/', '$2', $SFk_value['ParentTableCode']);
                            if($PdmTables[$name1]){
                                $ParentTableID = $PdmTables[$name1]['ID'];
                            }else{
                                $ParentTableID = $SqlTables[$name1]['ID'];
                            }
                            if($PdmTables[$name1]['Columns'][$SFk_value['ParentTableColumnCode']]){
                                $ParentColumnID = $PdmTables[$name1]['Columns'][$SFk_value['ParentTableColumnCode']]['ID'];
                            }else{
                                $ParentColumnID = $SqlTables[$name1]['Columns'][$SFk_value['ParentTableColumnCode']]['ID'];
                            }
                            $name2 = preg_replace('/(prefix_)([a-zA-Z_]*)/', '$2', $SFk_value['ChildTableCode']);
                            if($PdmTables[$name2]){
                                $ChildTableID = $PdmTables[$name2]['ID'];
                            }else{
                                $ChildTableID = $SqlTables[$name2]['ID'];
                            }
                            if($PdmTables[$name2]['Columns'][$SFk_value['ChildTableColumnCode']]){
                                $ChildColumnID = $PdmTables[$name2]['Columns'][$SFk_value['ChildTableColumnCode']]['ID'];
                            }else{
                                $ChildColumnID = $SqlTables[$name2]['Columns'][$SFk_value['ChildTableColumnCode']]['ID'];
                            }
                            $Code =str_replace('FK_','',$SFk_value['Properties']['Code']);
                            $ChangeArray['FKs']['addProperties'][] = [
                                'ID' => $ID,
                                'Name' => $SFk_value['Properties']['Name'],
                                'Code' => $Code,
                                'Comment' => $SFk_value['Properties']['Comment'],
                                'ParentTableID' => $ParentTableID,
                                'ParentColumnID' => $ParentColumnID,
                                'ChildTableID' => $ChildTableID,
                                'ChildColumnID' => $ChildColumnID
                            ];
                        }
                    }
                    //如果PdmFKs中存在SqlFKs不存在的外键关系，那么删除该外键关系
                    foreach($flag as $i => $j){
                        if($j == 0){
                            $ChangeArray['FKs']['deleteProperties'][] = [
                                'PropertiesID' => $PdmFKs[$i]['Properties']['ID']
                            ];
                        }
                    }
                }
                //若SqlFks中存在Parent中的关系但是Pdm中不存在Parent关系，那么添加Parent中的所有关系到Pdm中
                else{
                    foreach($SqlFKs['Parent'] as $SFk => $SFk_value){
                        $max++;
                        $ID = "o".$max;
                        $ParentTableID = $SqlTables[$v]['ID'];
                        $ParentColumnID = $SqlTables[$v]['Columns'][$SFk_value['ParentTableColumnCode']];
                        $name = preg_replace('/(prefix_)([a-zA-Z_]*)/', '$2', $SFk_value['ChildTableCode']);
                        $ChildTableID = $SqlTables[$name]['ID'];
                        $ChildColumnID = $SqlTables[$name]['Columns'][$SFk_value['ChildTableColumnCode']];
                        $Code =str_replace('FK_','',$SFk_value['Properties']['Code']);
                        $ChangeArray['FKs']['addProperties'][] = [
                            'ID' => $ID,
                            'Name' => $SFk_value['Properties']['Name'],
                            'Code' => $Code,
                            'Comment' => $SFk_value['Properties']['Comment'],
                            'ParentTableID' => $ParentTableID,
                            'ParentColumnID' => $ParentColumnID,
                            'ChildTableID' => $ChildTableID,
                            'ChildColumnID' => $ChildColumnID
                        ];
                    }
                }
            }
            //判断外键关系下的Child外键关系是否发生改变
            if($SqlFKs['Child']){
                //判断SqlFks和PdmFks中的Child中的关系是否相同
                if($PdmFKs['Child']){
                    //初始化标志位
                    $count = count($PdmFKs['Child']);
                    $flag = [];
                    for($i=0;$i<$count;$i++){
                        $flag[$i] = 0;
                    }
                    $i = 0;
                    foreach($SqlFKs['Child'] as $SFk => $SFk_value){
                        $judge = false;
                        $SqlPropertiesCode = $SFk_value['Properties']['Code'];
                        $SqlPropertiesCode = str_replace("FK_","",$SqlPropertiesCode);
                        $SqlFKsValue = [];
                        $SqlFKsValue = [
                            'ParentTableCode' => $SFk_value['ParentTableCode'],
                            'ParentTableColumnCode' => $SFk_value['ParentTableColumnCode'],
                            'ChildTableCode' => $SFk_value['ChildTableCode'],
                            'ChildTableColumnCode' => $SFk_value['ChildTableColumnCode']
                        ];
                        foreach($PdmFKs['Child'] as $PFk => $PFk_value){
                            $PdmPropertiesCode = $PFk_value['Properties']['Code'];
                            $PdmFKsValue = [];
                            $PdmFKsValue = [
                                'ParentTableCode' => $PFk_value['ParentTableCode'],
                                'ParentTableColumnCode' => $PFk_value['ParentTableColumnCode'],
                                'ChildTableCode' => $PFk_value['ChildTableCode'],
                                'ChildTableColumnCode' => $PFk_value['ChildTableColumnCode']
                            ];
                            //判断相同外键名的外键关系是否有修改，若有修改，则将$judge置为true,表明该外键关系存在，不是额外增加的
                            if($SqlPropertiesCode == $PdmPropertiesCode){
                                $flag[$i] = 1;
                                $i++;
                                $judge = true;
                                $diff = array_diff($SqlFKsValue,$PdmFKsValue);
                                if($diff) {
                                    if ($PdmTables[$v]['Columns'][$PFk_value['ParentTableColumnCode']]) {
                                        $ParentColumnID = $PdmTables[$v]['Columns'][$PFk_value['ParentTableColumnCode']]['ID'];
                                    } else {
                                        $ParentColumnID = $SqlTables[$v]['Columns'][$SFk_value['ParentTableColumnCode']]['ID'];
                                    }
                                    $name = preg_replace('/(prefix_)([a-zA-Z_]*)/', '$2', $PFk_value['ChildTableCode']);
                                    if ($PdmTables[$name]) {
                                        $ChildTableID = $PdmTables[$name]['ID'];
                                    } else {
                                        $ChildTableID = $SqlTables[$name]['ID'];
                                    }
                                    if ($PdmTables[$name]['Columns'][$PFk_value['ChildTableColumnCode']]) {
                                        $ChildColumnID = $PdmTables[$name]['Columns'][$PFk_value['ChildTableColumnCode']]['ID'];
                                    } else {
                                        $ChildColumnID = $SqlTables[$name]['Columns'][$SFk_value['ChildTableColumnCode']]['ID'];
                                    }
                                    $Code =str_replace('FK_','',$SFk_value['Properties']['Code']);
                                    $ChangeArray['FKs']['saveProperties'][] = [
                                        'ID' => $PFk_value['Properties']['ID'],
                                        'Name' => $SFk_value['Properties']['Name'],
                                        'Code' => $Code,
                                        'Comment' => $PFk_value['Properties']['Comment'],
                                        'ParentTableID' => $PFk_value['Properties']['ParentTableID'],
                                        'ParentColumnID' => $ParentColumnID,
                                        'ChildTableID' => $ChildTableID,
                                        'ChildColumnID' => $ChildColumnID
                                    ];
                                }
                            }
                            //判断不同外键名的外键关系是否相同，若相同则表明外键名有修改，则将$judge置为true,表明该外键关系存在，不是额外增加的
                            else{
                                $diff = array_diff($SqlFKsValue,$PdmFKsValue);
                                if(!$diff){
                                    $judge = true;
                                    $flag[$i] = 1;
                                    $i++;
                                    $Code =str_replace('FK_','',$SFk_value['Properties']['Code']);
                                    $ChangeArray['FKs']['savePropertiesName'][] = [
                                        'PropertiesID' => $PFk_value['Properties']['ID'],
                                        'Name' => $SFk_value['Properties']['Name'],
                                        'Code' => $Code
                                    ];
                                }
                            }
                        }
                        //如果没有发生外键的修改和外键关系名的修改，那么则为添加的外键关系
                        if($judge == false){
                            $max++;
                            $ID = "o".$max;
                            $name1 = preg_replace('/(prefix_)([a-zA-Z_]*)/', '$2', $SqlFKs[$SFk]['ParentTableCode']);
                            if($PdmTables[$name1]){
                                $ParentTableID = $PdmTables[$name1]['ID'];
                            }else{
                                $ParentTableID = $SqlTables[$name1]['ID'];
                            }
                            if($PdmTables[$name1]['Columns'][$SFk_value['ParentTableColumnCode']]){
                                $ParentColumnID = $PdmTables[$name1]['Columns'][$SFk_value['ParentTableColumnCode']]['ID'];
                            }else{
                                $ParentColumnID = $SqlTables[$name1]['Columns'][$SFk_value['ParentTableColumnCode']]['ID'];
                            }
                            $name2 = preg_replace('/(prefix_)([a-zA-Z_]*)/', '$2', $SFk_value['ChildTableCode']);
                            if($PdmTables[$name2]){
                                $ChildTableID = $PdmTables[$name2]['ID'];
                            }else{
                                $ChildTableID = $SqlTables[$name2]['ID'];
                            }
                            if($PdmTables[$name2]['Columns'][$SFk_value['ChildTableColumnCode']]){
                                $ChildColumnID = $PdmTables[$name2]['Columns'][$SFk_value['ChildTableColumnCode']]['ID'];
                            }else{
                                $ChildColumnID = $SqlTables[$name2]['Columns'][$SFk_value['ChildTableColumnCode']]['ID'];
                            }
                            $Code =str_replace('FK_','',$SFk_value['Properties']['Code']);
                            $ChangeArray['FKs']['addProperties'][] = [
                                'ID' => $ID,
                                'Name' => $SFk_value['Properties']['Name'],
                                'Code' => $Code,
                                'Comment' => $SFk_value['Properties']['Comment'],
                                'ParentTableID' => $ParentTableID,
                                'ParentColumnID' => $ParentColumnID,
                                'ChildTableID' => $ChildTableID,
                                'ChildColumnID' => $ChildColumnID
                            ];
                        }
                    }
                    //如果PdmFKs中存在SqlFKs不存在的外键关系，那么删除该外键关系
                    foreach($flag as $i => $j){
                        if($j == 0){
                            $ChangeArray['FKs']['deleteProperties'][] = [
                                'PropertiesID' => $PdmFKs[$i]['Properties']['ID']
                            ];
                        }
                    }
                }
                //若SqlFks中存在Parent中的关系但是Pdm中不存在Parent关系，那么添加Parent中的所有关系到Pdm中
                else{
                    foreach($SqlFKs['Child'] as $SFk => $SFk_value){
                        $max++;
                        $ID = "o".$max;
                        $ParentTableID = $SqlTables[$v]['ID'];
                        $ParentColumnID = $SqlTables[$v]['Columns'][$SFk_value['ParentTableColumnCode']];
                        $name = preg_replace('/(prefix_)([a-zA-Z_]*)/', '$2', $SFk_value['ChildTableCode']);
                        $ChildTableID = $SqlTables[$name]['ID'];
                        $ChildColumnID = $SqlTables[$name]['Columns'][$SFk_value['ChildTableColumnCode']];
                        $Code =str_replace('FK_','',$SFk_value['Properties']['Code']);
                        $ChangeArray['FKS']['addProperties'][] = [
                            'ID' => $ID,
                            'Name' => $SFk_value['Properties']['Name'],
                            'Code' => $Code,
                            'Comment' => $SFk_value['Properties']['Comment'],
                            'ParentTableID' => $ParentTableID,
                            'ParentColumnID' => $ParentColumnID,
                            'ChildTableID' => $ChildTableID,
                            'ChildColumnID' => $ChildColumnID
                        ];
                    }
                }
            }
            //判断SqlFks的Parent中不存在的外键关系PdmFKs的Parent中是否存在，若存在则删除该外键关系
            if($PdmFKs['Parent']&&!$SqlFKs['Parent']){
                //删除PdmFKs中Parent所有的外键关系
                foreach($PdmFKs['Parent'] as $FK_k => $FK_v){
                    $ChangeArray['FKs']['deleteProperties'][] = [
                        'PropertiesID' => $FK_v['Properties']['ID']
                    ];
                }
            }
            //判断SqlFks的Child中不存在的外键关系PdmFKs的Child中是否存在，若存在则删除该外键关系
            if($PdmFKs['Child']&&!$SqlFKs['Child']){
                //删除PdmFKs中Child所有的外键关系
                foreach($PdmFKs['Child'] as $FK_k => $FK_v){
                    $ChangeArray['FKs']['deleteProperties'][] = [
                        'PropertiesID' => $FK_v['Properties']['ID']
                    ];
                }
            }
        }
        //设置外键关系中所有需要添加的ID号
        if($ChangeArray['TableName']['addTableName']){
            foreach($ChangeArray['TableName']['addTableName'] as $k => $v){
                if($v['FKs']['Parent']){
                    foreach($v['FKs']['Parent'] as $key => &$key_value){
                        $name1 = preg_replace('/(prefix_)([a-zA-Z_]*)/', '$2',$key_value['ParentTable']['Code']);
                        $ChangeArray['TableName']['addTableName'][$k]['FKs']['Parent'][$key]['ParentTable']['ID'] = $SqlTables[$name1]['ID'];
                        $name2 = preg_replace('/(prefix_)([a-zA-Z_]*)/', '$2',$key_value['ChildTable']['Code']);
                        $ChangeArray['TableName']['addTableName'][$k]['FKs']['Parent'][$key]['ChildTable']['ID'] = $SqlTables[$name2]['ID'];
                        $max++;
                        $ChangeArray['TableName']['addTableName'][$k]['FKs']['Parent'][$key]['Properties']['ID'] = "o".$max;
                        $ChangeArray['TableName']['addTableName'][$k]['FKs']['Parent'][$key]['Properties']['ParentTableID'] = $SqlTables[$name1]['ID'];
                        $ChangeArray['TableName']['addTableName'][$k]['FKs']['Parent'][$key]['Properties']['ChildTableID'] = $SqlTables[$name2]['ID'];
                        $ChangeArray['TableName']['addTableName'][$k]['FKs']['Parent'][$key]['Properties']['ParentColumnID'] = $SqlTables[$name1]['Columns'][$key_value['ParentTableColumnCode']]['ID'];
                        $ChangeArray['TableName']['addTableName'][$k]['FKs']['Parent'][$key]['Properties']['ChildColumnID'] = $SqlTables[$name2]['Columns'][$key_value['ChildTableColumnCode']]['ID'];
                    }
                }
                if($v['FKs']['Child']){
                    foreach($v['FKs']['Child'] as $key => $k_value){
                        $name1 = preg_replace('/(prefix_)([a-zA-Z_]*)/', '$2',$k_value['ParentTable']['Code']);
                        $ChangeArray['TableName']['addTableName'][$k]['FKs']['Child'][$key]['ParentTable']['ID'] = $SqlTables[$name1]['ID'];
                        $name2 = preg_replace('/(prefix_)([a-zA-Z_]*)/', '$2',$k_value['ChildTable']['Code']);
                        $ChangeArray['TableName']['addTableName'][$k]['FKs']['Child'][$key]['ChildTable']['ID'] = $SqlTables[$name2]['ID'];
                        $max++;
                        $ChangeArray['TableName']['addTableName'][$k]['FKs']['Child'][$key]['Properties']['ID'] = "o".$max;
                        $ChangeArray['TableName']['addTableName'][$k]['FKs']['Child'][$key]['Properties']['ParentTableID'] = $SqlTables[$name1]['ID'];
                        $ChangeArray['TableName']['addTableName'][$k]['FKs']['Child'][$key]['Properties']['ChildTableID'] = $SqlTables[$name2]['ID'];
                        $ChangeArray['TableName']['addTableName'][$k]['FKs']['Child'][$key]['Properties']['ParentColumnID'] = $SqlTables[$name1]['Columns'][$k_value['ParentTableColumnCode']]['ID'];
                        $ChangeArray['TableName']['addTableName'][$k]['FKs']['Child'][$key]['Properties']['ChildColumnID'] = $SqlTables[$name2]['Columns'][$k_value['ChildTableColumnCode']]['ID'];
                    }
                }
            }
        }
        $ChangeArray['maxID'] = $max;
        return $ChangeArray;
    }

    /**根据数据库修改pdm设计稿文件中反日内容
     * @param $ChangeArray保存修改内容的数组通过调用checkSqlPdm方法获得
     * @return bool
     */
    function repairSqlPdm($ChangeArray){
        $max = $ChangeArray['maxID'];
        if(!isset($ChangeArray)){
            return false;
        }
        $tableName = $ChangeArray['TableName'];
        $PK = $ChangeArray['PK'];
        $ColumnName = $ChangeArray['ColumnName'];
        $ColumnValue = $ChangeArray['ColumnValue'];
        $FKs = $ChangeArray['FKs'];
        //进行表操作
        if($tableName){
            $addTableName = $tableName['addTableName'];
            $deleteTableName = $tableName['deleteTableName'];
            //添加表
            if($addTableName){
                foreach($addTableName as $k => $v){
                    if(!$v['Name']){
                        $v['Name'] = $v['Code'];
                    }
                    $time = time();
                    $ObjectID = $this->getObjectID();
                    $pdm = '';
                    $pdm = "<oTable Id=\"{$v['ID']}\">
<aObjectID>{$ObjectID}</aObjectID>
<aName>{$v['Name']}</aName>
<aCode>{$v['Code']}</aCode>
<aCreationDate>{$time}</aCreationDate>
<aCreator>那时年少</aCreator>
<aModificationDate>{$time}</aModificationDate>
<aModifier>那时年少</aModifier>
<aTotalSavingCurrency/>
<cColumns>
";
                    foreach($v['Columns'] as $ck => $cv){
                        if(!$cv['Name']){
                            $cv['Name'] = $cv['Code'];
                        }
                        $ObjectID = $this->getObjectID();
                        preg_match('/[0-9]+/',$cv['DataType'],$len);
                        $pdm.="<oColumn Id=\"{$cv['ID']}\">
<aObjectID>{$ObjectID}</aObjectID>
<aName>{$cv['Name']}</aName>
<aCode>{$cv['Code']}</aCode>
<aCreationDate>{$time}</aCreationDate>
<aCreator>那时年少</aCreator>
<aModificationDate>{$time}</aModificationDate>
<aModifier>那时年少</aModifier>
<aDataType>{$cv['DataType']}</aDataType>
";
                        if(is_numeric($len[0])){
                            preg_match('/[a-zA-Z]+/',$cv['DataType'],$type);
                            if($type[0] == 'double') {
                                preg_match_all('/[0-9]+/',$cv['DataType'],$len);
                                $pdm.= "<aLength>{$len[0][0]}</aLength>
<aPrecision>{$len[0][1]}</aPrecision>
";
                            }else{
                                $pdm.= "<aLength>{$len[0]}</aLength>
";
                            }
                        }
                        if($cv['I'] == true){
                            $pdm.="<aIdentity>1</aIdentity>
";
                        }
                        if($cv['M'] == true){
                            $pdm.="<aColumnMandatory>1</aColumnMandatory>
";
                        }
                        if($cv['U'] == true){
                            $pdm.="<aExtendedAttributesText>{F4F16ECD-F2F1-4006-AF6F-638D5C65F35E},MYSQL50,56={4A2BD2F3-4A8A-4421-8A48-A8029BDA28E8},Unsigned,4=true

</aExtendedAttributesText>
";
                        }
                        $pdm.="</oColumn>
";
                    }
                    $pdm.="</cColumns>
";
                    $max++;
                    $Id = "o".$max;
                    $ObjectID = $this->getObjectID();
                    $pdm.="<cKeys>
<oKey Id=\"{$Id}\">
<aObjectID>{$ObjectID}</aObjectID>
<aName>Key_1</aName>
<aCode>Key_1</aCode>
<aCreationDate>{$time}</aCreationDate>
<aCreator>那时年少</aCreator>
<aModificationDate>{$time}</aModificationDate>
<aModifier>那时年少</aModifier>
<cKeyColumns>
<oColumn Ref=\"{$v['Columns'][$v['PK']]['ID']}\"/>
</cKeyColumns>
</oKey>
</cKeys>
<cPrimaryKey>
<oKey Ref=\"{$Id}\"/>
</cPrimaryKey>
</oTable>
";
                    $t = pq("")->find('cTables')->html();
                    $t = str_replace(" xmlns:a=\"attribute\" xmlns:c=\"collection\"","",$t);
                    $data = $t.$pdm;
                    pq("")->find('cTables')->html(str_replace(pq("")->find('cTables')->html(),$data,pq("")->find('cTables')->html()));
                    $max++;
                    $display = "o".$max;
                    $TableSymbol = "
<oTableSymbol Id=\"{$display}\">
<aCreationDate>{$time}</aCreationDate>
<aModificationDate>{$time}8</aModificationDate>
<aIconMode>-1</aIconMode>
<aRect>((-20681,4765), (-11309,9488))</aRect>
<aLineColor>12615680</aLineColor>
<aFillColor>16570034</aFillColor>
<aShadowColor>12632256</aShadowColor>
<aFontList>STRN 0 新宋体,8,N
DISPNAME 0 新宋体,8,N
OWNRDISPNAME 0 新宋体,8,N
Columns 0 新宋体,8,N
TablePkColumns 0 新宋体,8,U
TableFkColumns 0 新宋体,8,N
Keys 0 新宋体,8,N
Indexes 0 新宋体,8,N
Triggers 0 新宋体,8,N
LABL 0 新宋体,8,N</aFontList>
<aBrushStyle>6</aBrushStyle>
<aGradientFillMode>65</aGradientFillMode>
<aGradientEndColor>16777215</aGradientEndColor>
<cObject>
<oTable Ref=\"{$v['ID']}\"/>
</cObject>
</oTableSymbol>
";
                    $a=pq("")->find('cSymbols')->html();
                    $TableSymbol.=$a;
                    pq("")->find('cSymbols')->html(str_replace(pq("")->find('cSymbols')->html(),$TableSymbol, pq("")->find('cSymbols')->html()));
                }
            }
            //删除表
            if($deleteTableName){
                foreach($deleteTableName as $k => $v){
                    $id = $v['ID'];
                    pq("[Id=$id]")->find('oTable')->html(str_replace(pq("[Id=$id]")->find('oTable')->html(),"",pq("[Id=$id]")->find('oTable')->html()));
                    $TID = pq("[Ref=$id]")->parent()->parent()->attr('Id');
                    pq("[Id=$TID]")->find('oTableSymbol')->html(str_replace(pq("[Id=$TID]")->find('oTableSymbol')->html(),"",pq("[Id=$TID]")->find('oTableSymbol')->html()));
                }
            }
        }
        //主键操作
        if($PK){
            foreach($PK as $k => $v){
                $TableID = $v['TableID'];
                $ID = $v['PkID'];
                $pkID = pq("[Id=$TableID]")->find('cPrimaryKey oKey')->attr('Ref');
                $data = "<o:Column Ref=\"{$ID}\"/>";
                pq("[Id=$pkID]")->find('cKey.Columns')->html(pq("[Id=$pkID]")->find('cKey.Columns')->html(),$data,pq("[Id=$pkID]")->find('cKey.Columns')->html());
            }
        }
        //字段名操作
        if($ColumnName){
            $addColumnName = $ColumnName['addColumnName'];
            $deleteColumnName = $ColumnName['deleteColumnName'];
            //添加字段
            if($addColumnName){
                foreach($addColumnName as $k => $v){
                    $pdm ="";
                    $time = time();
                    $ObjectID = $this->getObjectID();
                    preg_match('/[0-9]+/',$v['Column']['DataType'],$len);
                    $pdm.="<oColumn Id=\"{$v['Column']['ID']}\">
<aObjectID>{$ObjectID}</aObjectID>
<aName>{$v['Column']['Name']}</aName>
<aCode>{$v['Column']['Code']}</aCode>
<aCreationDate>{$time}</aCreationDate>
<aCreator>那时年少</aCreator>
<aModificationDate>{$time}</aModificationDate>
<aModifier>那时年少</aModifier>
<aDataType>{$v['Column']['DataType']}</aDataType>";
                    if(is_numeric($len[0])){
                        $pdm.= "<aLength>{$len[0]}</aLength>";
                    }
                    if($v['Column']['I'] == true){
                        $pdm.="<aIdentity>1</aIdentity>";
                    }
                    if($v['Column']['M'] == true){
                        $pdm.="<aColumnMandatory>1</aColumnMandatory>";
                    }
                    if($v['Column']['U'] == true){
                        $pdm.="<aExtendedAttributesText>{F4F16ECD-F2F1-4006-AF6F-638D5C65F35E},MYSQL50,56={4A2BD2F3-4A8A-4421-8A48-A8029BDA28E8},Unsigned,4=true

</aExtendedAttributesText>
";
                    }
                    $pdm.="</oColumn>";
                    $TableID = $v['TableID'];
                    $pdmString = pq("[Id=$TableID]")->html();
                    $pdmString.=$pdm;
                    pq("[Id=$TableID]")->html(str_replace(pq("[Id=$TableID]")->html(),$pdmString,pq("[Id=$TableID]")->html()));
                }
            }
            //删除字段
            if($deleteColumnName){
                foreach($deleteColumnName as $k => $v){
                    $ColumnID = $v['Column']['ID'];
                    pq("[Id=$ColumnID]")->html(str_replace( pq("[Id=$ColumnID]")->html(),"", pq("[Id=$ColumnID]")->html()));
                }
            }
        }
        //字段属性操作
        if($ColumnValue){
            $saveColumnValue = $ColumnValue['saveColumnValue'];
            //修改字段属性
            if($saveColumnValue){
                foreach($saveColumnValue as $k => $v){
                    $pdm ="";
                    $time = time();
                    $ObjectID = $this->getObjectID();
                    preg_match('/[0-9]+/',$v['Column']['DataType'],$len);
                    $pdm.="<aObjectID>{$ObjectID}</aObjectID>
<aName>{$v['Column']['Name']}</aName>
<aCode>{$v['Column']['Code']}</aCode>
<aCreationDate>{$time}</aCreationDate>
<aCreator>那时年少</aCreator>
<aModificationDate>{$time}</aModificationDate>
<aModifier>那时年少</aModifier>
<aDataType>{$v['Column']['DataType']}</aDataType>";
                    if(is_numeric($len[0])){
                        preg_match('/[a-zA-Z]+/',$v['DataType'],$type);
                        if($type[0] == 'double') {
                            preg_match_all('/[0-9]+/',$v['DataType'],$len);
                            $pdm.= "<aLength>{$len[0][0]}</aLength>
<aPrecision>{$len[0][1]}</aPrecision>
";
                        }else{
                            $pdm.= "<aLength>{$len[0]}</aLength>
";
                        }
                    }
                    if($v['Column']['I'] == true){
                        $pdm.="<aIdentity>1</aIdentity>";
                    }
                    if($v['Column']['M'] == true){
                        $pdm.="<aColumn.Mandatory>1</aColumn.Mandatory>";
                    }
                    if($v['Column']['U'] == true){
                        $pdm.="<aExtendedAttributesText>{F4F16ECD-F2F1-4006-AF6F-638D5C65F35E},MYSQL50,56={4A2BD2F3-4A8A-4421-8A48-A8029BDA28E8},Unsigned,4=true

</aExtendedAttributesText>";
                    }
                    $ColumnID = $v['ColumnID'];
                    pq("[Id=$ColumnID]")->html(str_replace(pq("[Id=$ColumnID]")->html(),$pdm,pq("[Id=$ColumnID]")->html()));
                }
            }
        }
        //外键关系操作
        if($FKs){
            $addProperties = $FKs['addProperties'];
            $deleteProperties = $FKs['deleteProperties'];
            $saveProperties = $FKs['saveProperties'];
            $savePropertiesName = $FKs['savePropertiesName'];
            //添加外键关系
            if($addProperties){
                foreach($addProperties as $k => $v){
                    if(!$v['Name']){
                        $v['Name'] = $v['Code'];
                    }
                    $pdm = "";
                    $max++;
                    $ID = "o".$max;
                    $TableId = $v['ParentTableID'];
                    $PK = pq("[Id=$TableId]")->find('cPrimaryKey oKey')->attr('Ref');
                    $ObjectID1 = $this->getObjectID();
                    $ObjectID2 = $this->getObjectID();
                    $time = time();
                    $pdm = "<oReference Id=\"{$v['ID']}\">
<aObjectID>{$ObjectID1}</aObjectID>
<aName>{$v['Name']}</aName>
<aCode>{$v['Code']}</aCode>
<aCreationDate>{$time}</aCreationDate>
<aCreator>那时年少</aCreator>
<aModificationDate>{$time}</aModificationDate>
<aModifier>那时年少</aModifier>
<aComment>{$v['Comment']}</aComment>
<aCardinality>0..*</aCardinality>
<aUpdateConstraint>1</aUpdateConstraint>
<aDeleteConstraint>1</aDeleteConstraint>
<cParentTable>
<oTable Ref=\"{$v['ParentTableID']}\"/>
</cParentTable>
<cChildTable>
<oTable Ref=\"{$v['ChildTableID']}\"/>
</cChildTable>
<ParentKey>
<oKey Ref=\"{$PK}\"/>
</ParentKey>
<cJoins>
<oReferenceJoin Id=\"{$ID}\">
<aObjectID>{$ObjectID2}</aObjectID>
<aCreationDate>{$time}</aCreationDate>
<aCreator>那时年少</aCreator>
<aModificationDate>{$time}</aModificationDate>
<aModifier>那时年少</aModifier>
<cObject1>
<oColumn Ref=\"{$v['ParentColumnID']}\"/>
</cObject1>
<cObject2>
<oColumn Ref=\"{$v['ChildColumnID']}\"/>
</cObject2>
</oReferenceJoin>
</cJoins>
</oReference>
";
                    $data = pq("")->find('cReferences')->html();
                    $data.=$pdm;
                    pq("")->find('cReferences')->html(str_replace(pq("")->find('cReferences')->html(),$data,pq("")->find('cReferences')->html()));
                    $max++;
                    $ReferID="o".$max;
                    $PID = pq("[Ref={$v['ParentTableID']}]")->parent()->parent()->attr('Id');
                    $CID = pq("[Ref={$v['ChildTableID']}]")->parent()->parent()->attr('Id');
                    $ParentRect = pq("[Id=$PID]")->find('aRect')->html();
                    $ChildRect = pq("[Id=$CID]")->find('aRect')->html();
                    $ReferenceSymbol = "
<oReferenceSymbol Id=\"{$ReferID}\">
<aCreationDate>1492344723</aCreationDate>
<aModificationDate>1492344723</aModificationDate>
<aRect>{$ChildRect}</aRect>
<aListOfPoints>{$ParentRect}</aListOfPoints>
<aCornerStyle>1</aCornerStyle>
<aArrowStyle>1</aArrowStyle>
<aLineColor>12615680</aLineColor>
<aShadowColor>12632256</aShadowColor>
<aFontList>CENTER 0 新宋体,8,N
SOURCE 0 新宋体,8,N
DESTINATION 0 新宋体,8,N</aFontList>
<cSourceSymbol>
<oTableSymbol Ref=\"{$CID}\"/>
</cSourceSymbol>
<cDestinationSymbol>
<oTableSymbol Ref=\"{$PID}\"/>
</cDestinationSymbol>
<cObject>
<oReference Ref=\"{$v['ID']}\"/>
</cObject>
</oReferenceSymbol>
";
                    $b=pq("")->find('cSymbols')->html();
                    $ReferenceSymbol.=$b;
                    pq("")->find('cSymbols')->html(str_replace(pq("")->find('cSymbols')->html(),$ReferenceSymbol, pq("")->find('cSymbols')->html()));
                }
            }
            //删除外键关系
            if($deleteProperties){
                foreach($deleteProperties as $k => $v){
                    $PID = $v['PropertiesID'];
                    pq("[Id=$PID]")->html(pq("[Id=$PID]")->html(),"",pq("[Id=$PID]")->html());
                    $RID = pq("[Id=$PID]")->parent()->parent()->attr('Id');
                    pq("[Id=$RID]")->find('oReferencesSymbol')->html(str_replace(pq("[Id=$RID]")->find('oReferencesSymbol')->html(),"",pq("[Id=$RID]")->find('oReferencesSymbol')->html()));
                }
            }
            //修改外键名
            if($savePropertiesName){
                $savePropertiesName = array_unique($savePropertiesName);
                foreach($savePropertiesName as $k => $v){
                    if(!$v['Name']){
                        $v['Name'] = $v['Code'];
                    }
                    $PID = $v['PropertiesID'];
                    pq("[Id=$PID]")->find('aName')->html(str_replace(pq("[Id=$PID]")->find('aName')->html(),$v['Name'],pq("[Id=$PID]")->find('aName')->html()));
                    pq("[Id=$PID]")->find('aCode')->html(str_replace(pq("[Id=$PID]")->find('aCode')->html(),$v['Code'],pq("[Id=$PID]")->find('aCode')->html()));
                }
            }
            //修改外键关系
            if($saveProperties){
                foreach($saveProperties as $k => $v){
                    $PID = $v['ID'];
                    $pdm = "";
                    $max++;
                    $ID = "o".$max;
                    $TableId = $v['ParentTableID'];
                    $PK = pq("[Id=$TableId]")->find('cPrimaryKey oKey')->attr('Ref');
                    $ObjectID1 = $this->getObjectID();
                    $ObjectID2 = $this->getObjectID();
                    $time = time();
                    $pdm = "<aObjectID>{$ObjectID1}</aObjectID>
<aName>{$v['Name']}</aName>
<aCode>{$v['Code']}</aCode>
<aCreationDate>{$time}</aCreationDate>
<aCreator>那时年少</aCreator>
<aModificationDate>{$time}</aModificationDate>
<aModifier>那时年少</aModifier>
<aComment>{$v['Comment']}</aComment>
<aCardinality>0..*</aCardinality>
<aUpdateConstraint>1</aUpdateConstraint>
<aDeleteConstraint>1</aDeleteConstraint>
<cParentTable>
<oTable Ref=\"{$v['ParentTableID']}\"/>
</cParentTable>
<cChildTable>
<oTable Ref=\"{$v['ChildTableID']}\"/>
</cChildTable>
<ParentKey>
<oKey Ref=\"{$PK}\"/>
</ParentKey>
<cJoins>
<oReferenceJoin Id=\"{$ID}\">
<aObjectID>{$ObjectID2}</aObjectID>
<aCreationDate>{$time}</aCreationDate>
<aCreator>那时年少</aCreator>
<aModificationDate>{$time}</aModificationDate>
<aModifier>那时年少</aModifier>
<cObject1>
<oColumn Ref=\"{$v['ParentColumnID']}\"/>
</cObject1>
<cObject2>
<oColumn Ref=\"{$v['ChildColumnID']}\"/>
</cObject2>
</oReferenceJoin>
</cJoins>
";
                    pq("[Id=$PID]")->html(str_replace(pq("[Id=$PID]")->html(),$pdm,pq("[Id=$PID]")->html()));
                    $PID = pq("[Ref={$v['ParentTableID']}]")->parent()->parent()->attr('Id');
                    $ReferID = pq("[Ref={$PID}]")->parent()->parent()->attr('Id');
                    $CID = pq("[Ref={$v['ChildTableID']}]")->parent()->parent()->attr('Id');
                    $ParentRect = pq("[Id=$PID]")->find('aRect')->html();
                    $ChildRect = pq("[Id=$CID]")->find('aRect')->html();
                    $ReferenceSymbol = "
<aCreationDate>1492344723</aCreationDate>
<aModificationDate>1492344723</aModificationDate>
<aRect>{$ChildRect}</aRect>
<aListOfPoints>{$ParentRect}</aListOfPoints>
<aCornerStyle>1</aCornerStyle>
<aArrowStyle>1</aArrowStyle>
<aLineColor>12615680</aLineColor>
<aShadowColor>12632256</aShadowColor>
<aFontList>CENTER 0 新宋体,8,N
SOURCE 0 新宋体,8,N
DESTINATION 0 新宋体,8,N</aFontList>
<cSourceSymbol>
<oTableSymbol Ref=\"{$CID}\"/>
</cSourceSymbol>
<cDestinationSymbol>
<oTableSymbol Ref=\"{$PID}\"/>
</cDestinationSymbol>
<cObject>
<oReference Ref=\"{$v['ID']}\"/>
</cObject>
";
                    pq("[Ref=$ReferID]")->html(str_replace(pq("[Ref=$ReferID]")->html(),$ReferenceSymbol,pq("[Ref=$ReferID]")->html()));
                }
            }
        }
        $html = pq('')->html();
        $patterns = array();
        $patterns[0] = '/(<|<\/)([a-z]{1})(\w+)(>|\/>)/';
        $patterns[1] = '/(<|<\/)([a-z]{1})(\w+) (Id|Ref)="(\w+)"(>|\/>)/';
        $replaces = array();
        $replaces[0] = '$1${2}:$3$4';
        $replaces[1] = '$1${2}:$3 $4="$5"$6';
        $html=preg_replace($patterns,$replaces,$html);
        $html = str_replace('ColumnMandatory', 'Column.Mandatory', $html);
        $xml = str_replace('KeyColumns', 'Key.Columns', $html);
        file_put_contents('1.pdm',$xml);
        return true;
    }

    /**生成ObjectID
     * @return string
     */
    function getObjectID(){
        $arr = array_merge(range(0,9),range('A','Z'));
        $array_len = count($arr);
        $str = [];
        for($i = 0;$i < 8;$i++){
            $rand = mt_rand(0,$array_len-1);
            $str[0].=$arr[$rand];
        }
        for($j = 1; $j < 4;$j++)
        {
            for($i = 0;$i < 4;$i++){
                $rand = mt_rand(0,$array_len-1);
                $str[$j].=$arr[$rand];
            }
        }
        for($i = 0;$i < 12;$i++){
            $rand = mt_rand(0,$array_len-1);
            $str[4].=$arr[$rand];
        }
        $ObjectID = $str[0]."-".$str[1]."-".$str[2]."-".$str[3]."-".$str[4];
        return $ObjectID;
    }
}