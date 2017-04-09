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
            $patterns = array();
            $patterns[0] = '/(<|<\/)([a-z]{1}):(\w+)(>|\/>)/';
            $patterns[1] = '/(<|<\/)([a-z]{1}):(\w+) (Id|Ref)="(\w+)"(>|\/>)/';
            $replaces = array();
            $replaces[0] = '$1${2}$3$4';
            $replaces[1] = '$1${2}$3 $4="$5"$6';
            $xml=preg_replace($patterns,$replaces,$xml);
           // $xml = str_replace(':', '', $xml);
            $xml = str_replace('Column.Mandatory', 'ColumnMandatory', $xml);
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
                    'M'=>!!pq($oColumn)->find('aColumnMandatory')->html(),
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
}