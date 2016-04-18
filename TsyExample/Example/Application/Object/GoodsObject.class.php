<?php
/**
 * Created by PhpStorm.
 * User: Castle
 * Date: 2016/4/18
 * Time: 21:51
 */

namespace Application\Object;


use Tsy\Library\Object;

class GoodsObject extends Object
{
    protected $main='Basic';
    protected $pk='GoodsID';
    protected $property=[
        'Producer'=>[
            self::RELATION_TABLE_NAME=>'PRODUCER_DIC',
            self::RELATION_TABLE_COLUMN=>'ProducerDicID',
            self::RELATION_TABLE_PROPERTY=>self::PROPERTY_ONE
        ]
    ];
    protected $link=[
        'Units'=>[
            self::RELATION_TABLE_NAME=>'unit_link',
            self::RELATION_TABLE_COLUMN=>'GoodsID',
            self::RELATION_TABLE_LINK_HAS_PROPERTY=>true,
            self::RELATION_TABLE_LINK_TABLES=>[
                'UNIT_DIC'=>[
                    self::RELATION_TABLE_COLUMN=>'UnitID'
                ]
            ]
        ]
    ];
}