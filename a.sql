SELECT
	*
FROM
	goods_basic
INNER JOIN goods_producer_dic ON goods_basic.ProducerDicID = goods_producer_dic.ProducerDicID
WHERE
	`GoodsID` IN (1);