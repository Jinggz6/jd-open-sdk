<?php

/**
 * 调用京东联盟接口方法
 * 
 * 所有京东联盟的接口调用
 * @author jinggz
 * @version v1.0
 * @since 2019/5/20
 */

class JdApiMethod
{

    /**
     * GatherGoodsInfo 组合查询商品信息
     * 
     * @access public
     * @param $skuid 京东推广商品id
     * @return $goods 商品信息
     */
    public function GatherGoodsInfo($skuid)
    {
        if (empty($skuid)) {
            return false;
        }
        $data = $this->ExpandGoods($skuid);
        if (empty($data)) {
            return false;
        }
        $data_arr = array();
        foreach ($data as $key => $val) {
            $data_arr = $this->KeyWordExpandGoods($val['skuId']);
            if (!empty($data_arr)) {
                $data[$key]['imageList'] = $data_arr['imageList'];
                $data[$key]['spuid'] = $data_arr['spuid'];
            } else {
                $data[$key]['imageList'] = '';
                $data[$key]['spuid'] = '';
            }
        }
        return $data;
    }

    /**
     * ExpandGoods 查询推广商品 
     * 
     * @access public
     * @param $skuid  京东推广商品id 京东限制最多100个skuId
     * @return $res 商品信息
     */
    public function ExpandGoods($skuid,$app_key,$app_secret)
    {
        $JdApi = new JdApi($app_key,$app_secret);
        $arr = array(
            'skuIds' => $skuid,
        );
        $arr = json_encode($arr);
        $config = array(
            'method' => 'jd.union.open.goods.promotiongoodsinfo.query',
            'param_json' => $arr,
        );
        $goods = $JdApi->RequestJdApi($config);
        if (empty($goods['jd_union_open_goods_promotiongoodsinfo_query_response'])) {
            return [];
        }
        $goods_info = json_decode($goods['jd_union_open_goods_promotiongoodsinfo_query_response']['result'], true);
        if (empty($goods_info['data'])) {
            return [];
        }
        $res = $goods_info['data'];
        return $res;
    }

    /**
     * KeyWordExpandGoods 根据关键词查询推广商品 1个sku_id
     * 
     * @access public
     * @param $skuIds  京东推广商品id
     * @return $res 商品信息
     */
    public function KeyWordExpandGoods($skuid,$app_key,$app_secret)
    {
        $JdApi = new JdApi($app_key,$app_secret);
        $arr = array(
            'goodsReqDTO' => array('skuIds' => [$skuid]),
        );
        $arr = json_encode($arr);
        $config = array(
            'method' => 'jd.union.open.goods.query',
            'param_json' => $arr,
        );
        $goods = $JdApi->RequestJdApi($config);
        if (empty($goods['jd_union_open_goods_query_response'])) {
            return [];
        }
        $goods_info = json_decode($goods['jd_union_open_goods_query_response']['result'], true);
        if (empty($goods_info['data'])) {
            return [];
        }
        $res['imageList'] = $goods_info['data'][0]['imageInfo']['imageList'];
        $res['spuid'] = $goods_info['data'][0]['spuid'];
        return $res;
    }
}
