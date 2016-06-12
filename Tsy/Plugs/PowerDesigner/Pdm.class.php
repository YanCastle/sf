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
    private $pq;
    private $Domain;
    public $json=[];
    function load($file){
        if(file_exists($file)) {
            $xml = file_get_contents($file);
            $xml = str_replace(':', '', $xml);
            $xml = str_replace('Column.Mandatory', 'ColumnMandatory', $xml);
            vendor('phpQuery.phpQuery');
            $this->pq=\phpQuery::newDocument($xml);
            $this->getDomains();
            $ProjectInfo = $this->getProject();
            $TableInfo = $this->getTables();
            $json['Project'] = $ProjectInfo;
            $json['Tables'] = $TableInfo;
            $json['Domains'] = $this->Domain;
            $this->json=$json;
            return true;
        }
        return false;
    }

    /**
     * 更新到数据库中
     * @return bool
     */
    function update(){
            $d = json_encode($this->json,JSON_UNESCAPED_UNICODE);
            if($ProjectInfo&&$TableInfo){
                //判断是否存在，如果存在则删除
                $DomainModel = M('Domains');
                $TablesModel = M('Tables');
                $ColumnsModel = M('Columns');
                $ProjectModel = M('Projects');
                M()->startTrans();
                $ExistProjectID = M('projects')->where(['Name'=>$ProjectInfo['Name']])->getField('ProjectID');
                if($ExistProjectID){
                    //TODO 删除
                    $ColumnsModel->where(['ProjectID'=>$ExistProjectID])->delete();
                    $TablesModel->where(['ProjectID'=>$ExistProjectID])->delete();
                    $DomainModel->where(['ProjectID'=>$ExistProjectID])->delete();
//                    $ProjectModel->where(['ProjectID'=>$ExistProjectID])->delete();
                    $ProjectID=$ExistProjectID;
                    $ProjectModel->where(['ProjectID'=>$ExistProjectID])->save($ProjectInfo);
                }else{
                    $ProjectID = $ProjectModel->add($ProjectInfo);
                }
                if($ProjectID){
                    //Domain
                    foreach($this->Domain as $k=>$v){
                        $this->Domain[$k]['ProjectID']=$ProjectID;
                    }
                    $DomainIDMap=[];
                    if($this->Domain){
                        foreach($this->Domain as $k=>$v){
                            $DomainID = $DomainModel->add($v);
                            $DomainIDMap[$v['ID']]=$DomainID;
                        }
                    }else{
                        M()->rollback();
                        return false;
                    }
                    foreach($TableInfo as $Table){
                        $Table['ProjectID']=$ProjectID;
                        $TableID = $TablesModel->add($Table);
                        if($TableID){
//                            $DomainIDs = array_column($Table['Columns'],'ID');

//                            if($DomainIDs){
//                                $DomainIDMap=$DomainModel->where(['ID'=>['in',$DomainIDs]])->getField('ID,DomainID',true);
//                            }
                            foreach($Table['Columns'] as $k=>$v){
                                $Table['Columns'][$k]['TableID']=$TableID;
                                $Table['Columns'][$k]['ProjectID']=$ProjectID;
                                $Table['Columns'][$k]['DomainID']=isset($DomainIDMap[$v['DomainID']])?$DomainIDMap[$v['DomainID']]:0;
                            }
                            if($ColumnsModel->addAll($Table['Columns'])){

                            }else{
                                M()->rollback();
                                return false;
                            }
                        }else{
                            M()->rollback();
                            return false;
                        }
                    }
                    M()->commit();
                    return true;
                }else{
                    M()->rollback();
                    return false;
                }
            }else{
                M()->rollback();
                return false;
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
            ];
            $Columns=[];
            $PK = pq($oTable)->find('cPrimaryKey oKey')->attr('Ref');
            $PK = pq($oTable)->find("[Id={$PK}]")->find('oColumn')->attr('Ref');
            foreach(pq($oTable)->find('oColumn') as $oColumn){
                $ColumnID=pq($oColumn)->attr('Id');
                if(null===$ColumnID){
                    $html = pq($oColumn)->html();
                    continue;
                }
                $Column=[
                    'Name'=>pq($oColumn)->find('aName')->html(),
                    'Code'=>pq($oColumn)->find('aCode')->html(),
                    'Comment'=>pq($oColumn)->find('aComment')->html(),
                    'DataType'=>pq($oColumn)->find('aDataType')->html(),
                    'I'=>pq($oColumn)->find('aIdentity')->html()==1,
                    'M'=>pq($oColumn)->find('aColumnMandatory')->html(),
                    'ID'=>$ColumnID,
                    'P'=>$PK?$PK==$ColumnID:0,
                    'DomainID'=>pq($oColumn)->find('cDomain oPhysicalDomain')->attr('Ref'),
                    'DefaultValue'=>pq($oColumn)->find('aDefaultValue')->html(),
                ];
                $Column['DefaultValue']=$Column['DefaultValue']==false?"":$Column['DefaultValue'];
                $Columns[]=$Column;
            }
            $Table['Columns']=$Columns;
            $Tables[$Table['Code']]=$Table;
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
            ];
            $this->Domain[$Domain['ID']]=$Domain;
        }
    }
}