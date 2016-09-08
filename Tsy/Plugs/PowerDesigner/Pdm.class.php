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
            $PKColumn='';
            foreach(pq($oTable)->find('oColumn') as $oColumn){
                $ColumnID=pq($oColumn)->attr('Id');
                if(null===$ColumnID){
                    $html = pq($oColumn)->html();
                    continue;
                }
                $Code = pq($oColumn)->find('aCode')->html();
                $Column=[
                    'Name'=>pq($oColumn)->find('aName')->html(),
                    'Code'=>$Code,
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
                $Columns[$Code]=$Column;
                if($PK&&$PK==$ColumnID){
                    $PKColumn=$Code;
                }
            }
            $Table['Columns']=$Columns;
            $Table['PK']=$PKColumn;
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
                'DataType'=>pq($oPhysicalDomain)->find('aDataType:first')->html(),
            ];
            $this->Domain[$Domain['ID']]=$Domain;
        }
    }
}