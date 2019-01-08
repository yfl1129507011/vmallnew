<?php
/**
 * Created by PhpStorm.
 * User: acewill
 * Date: 2019/1/3
 * Time: 17:26
 */
class Admin_TradeModel extends VmallNewModel{
    protected $cls = '/trade';
    private $orderYes = array(  # 订单正常状态
        '0'=>array('待支付','payment'),
        '1'=>array('待发货','undelivery'),
        '3'=>array('待收货','receive'),
        '6'=>array('已完成','complete'),
        '7'=>array('已关闭','cancel'),
        '99'=>array('已取消','cancel'),
        '100'=>array('退款关闭','cancel'),
    );
    private $orderNo = array(
        '0'=>array('退货待审核', 'afterdetail'),
        '1'=>array('待买家退货', 'return'),
        '2'=>array('退货待收货', 'return'),
        '3'=>array('待退款', 'refundetail'),
        '4'=>array('已退款', 'refunds'),
    );

    public $logisticsCompanyInfo = array(
        'shentong' => '申通快递',
        'yuantong' => '圆通速递',
        'zhongtong' => '中通快递',
        'huitongkuaidi' => '百世快递',
        'yunda' => '韵达快递',
        'shunfeng' => '顺丰速运',
        'debangwuliu' => '德邦物流',
        'yzguonei' => '邮政国内',
        'ziti' => '自提',
    );

    private function formatTrade(array $data){
        if(empty($data) || empty($data['results']) || !is_array($data['results'])) return $data;
        $orderNo = $this->orderNo;
        $orderYes = $this->orderYes;
        $data['results'] = array_map(function ($v) use ($orderNo,$orderYes){
            // 添加订单状态说明字段
            if (isset($v['refundStatus']) || isset($v['status'])) {
                if ($v['refundStatus'] !== null && $v['refundStatus'] != 99) {
                    $v['statusArr'] = $orderNo[$v['refundStatus']];
                } else { # 正常订单
                    $v['statusArr'] = $orderYes[$v['status']];
                }
            }
            return $v;
        }, $data['results']);

        return $data;
    }

    /**
     * @param array $query
     * @return array|bool
     * 获取订单列表
     */
    public function getTrade(array $query){
        $url = $this->getUrl('',$query);
        $res = $this->curl($url);
        if($this->checkApiResult($res, $url)){
            return $this->formatTrade($res);
        }
        return false;
    }

    /**
     * @return array
     * 获取订单数量
     */
    public function getTradeSum(){
        $orderStatus = array(
            '1' => '待发货',
            '100' => '退款/售后待审核',
            '102' => '待确认收货',
            '103' => '待退款',
        );
        $data = array();
        foreach ($orderStatus as $k=>$v) {
            $url = $this->getUrl('/sum', array('status' => $k));
            $res = $this->curl($url);
            if ($this->checkApiResult($res, $url)) {
                $data[$k] = $res['results'];
            }else{
                $data[$k] = 0;
            }
        }
        return $data;
    }

    /**
     * @param $tid
     * @return array|bool
     * 获取退款/售后信息
     */
    public function getRefundInfo($tid){
        if (empty($tid)) return false;
        $url = $this->getUrl('/'.$tid.'/refund-info');
        $res = $this->curl($url);
        if($this->checkApiResult($res,$url)){
            $res['results']['picUrlArr'] = array();
            $picUrls = $res['results']['picUrls'];
            if(!empty($picUrls) && strpos($picUrls, ',')){
                $res['results']['picUrlArr'] = explode(',',$picUrls);
            }
            return $res;
        }
        return false;
    }

    /**
     * @param $tid
     * @return array|bool
     * 获取订单详情数据
     */
    public function getDetailInfo($tid){
        if (empty($tid)) return false;
        $orderRes = $this->getTrade(array('tid'=>$tid));
        if(!$orderRes) return false;
        $orderInfo = $orderRes['results'][0];

        if(!empty($orderInfo['refundId'])) {
            $refundRes = $this->getRefundInfo($tid);
            $refundInfo = $refundRes['results'];
        }

        $tplStatus = $orderInfo['statusArr'][1];
        $data = $baseData = array();
        $baseData['订单编号'] = $orderInfo['tid'];
        switch ($tplStatus) {
            case 'undelivery':  # 待发货
                $baseData['下单时间'] = date('Y-m-d H:i:s',$orderInfo['createTs']/1000);
                $baseData['付款时间'] = date('Y-m-d H:i:s',$orderInfo['payTs']/1000);
                if(!empty($refundInfo['createTs'])){
                    $baseData['申请售后时间'] = date('Y-m-d H:i:s',$refundInfo['createTs']/1000);
                }
                if (!empty($refundInfo['verifyTs'])){
                    $baseData['售后审核时间'] = date('Y-m-d H:i:s',$refundInfo['verifyTs']/1000);
                }
                $baseData['购买用户'] = $orderInfo['name'];
                $baseData['订单状态'] = $orderInfo['statusArr'][0];
                $baseData['配送方式'] = '快递配送';
                $baseData['配送费用'] = $orderInfo['postFee']?$orderInfo['postFee']:'免邮';
                $_baseData = array_chunk($baseData, 3, true);
                $data['base_info'] = $_baseData;
                $data['opt'] = array(
                    'key'=>'可执行操作',
                    'val'=>'去发货',
                    'data-target'=>'.delivery_dialog',
                );
                break;
            case 'receive': # 待收货  receive
                $baseData['发货单号'] = $orderInfo['invoiceNo'];
                $baseData['物流公司'] = $orderInfo['logisticsCompanyCn'];
                $baseData['下单时间'] = date('Y-m-d H:i:s',$orderInfo['createTs']/1000);
                $baseData['付款时间'] = date('Y-m-d H:i:s',$orderInfo['payTs']/1000);
                $baseData['发货时间'] = date('Y-m-d H:i:s',$orderInfo['consignTs']/1000);
                $baseData['购买用户'] = $orderInfo['name'];
                $baseData['订单状态'] = $orderInfo['statusArr'][0];
                $baseData['配送方式'] = '快递配送';
                $baseData['配送费用'] = $orderInfo['postFee']?$orderInfo['postFee']:'免邮';
                $_baseData = array_chunk($baseData, 3, true);
                $data['base_info'] = $_baseData;
                $data['opt'] = array(
                    'key'=>'可执行操作',
                    'val'=>'修改发货信息',
                    'data-target'=>'.delivery_dialog',
                );
                break;
            case 'afterdetail': # 退款/售后待审核
                if(!empty($orderInfo['invoiceNo'])){
                    $baseData['运单号'] = $orderInfo['invoiceNo'];
                }
                if(!empty($orderInfo['logisticsCompanyCn'])) {
                    $baseData['物流公司'] = $orderInfo['logisticsCompanyCn'];
                }
                $baseData['下单时间'] = date('Y-m-d H:i:s',$orderInfo['createTs']/1000);
                $baseData['付款时间'] = date('Y-m-d H:i:s',$orderInfo['payTs']/1000);
                $baseData['申请售后时间'] = empty($refundRes['createTs'])?'-':date('Y-m-d H:i:s',$refundRes['createTs']/1000);
                $baseData['购买用户'] = $orderInfo['name'];
                $baseData['订单状态'] = '退货退款待审核';
                if(!empty($orderInfo['payment'])){
                    $baseData['订单金额'] = $orderInfo['payment'];
                }
                $baseData['配送方式'] = '快递配送';
                $baseData['配送费用'] = $orderInfo['postFee']?$orderInfo['postFee']:'免邮';
                $_baseData = array_chunk($baseData, 3, true);
                $data['base_info'] = $_baseData;
                $data['opt'] = array(
                    'key'=>'可执行操作',
                    'val'=>'审核退货',
                    'data-target'=>'.afterdetail_dialog',
                );
                break;
            case 'return': # 退货
                if(!empty($orderInfo['invoiceNo'])){
                    $baseData['运单号'] = $orderInfo['invoiceNo'];
                }
                if(!empty($orderInfo['logisticsCompanyCn'])) {
                    $baseData['物流公司'] = $orderInfo['logisticsCompanyCn'];
                }
                $baseData['下单时间'] = date('Y-m-d H:i:s',$orderInfo['createTs']/1000);
                $baseData['付款时间'] = date('Y-m-d H:i:s',$orderInfo['payTs']/1000);
                $baseData['申请售后时间'] = empty($refundInfo['createTs'])?'-':date('Y-m-d H:i:s',$refundInfo['createTs']/1000);
                $baseData['同意退货时间'] = empty($refundInfo['agreeReturnGoodsTs'])?'-':date('Y-m-d H:i:s',$refundInfo['agreeReturnGoodsTs']/1000);
                if(!empty($refundInfo['agreeRefundTs'])) {
                    $baseData['同意退款时间'] = date('Y-m-d H:i:s',$refundInfo['agreeRefundTs']/1000);;
                }
                $baseData['购买用户'] = $orderInfo['name'];
                $baseData['订单状态'] = ($orderInfo['refundStatus']==1)?'待买家退货':'退货待收货';
                $baseData['配送方式'] = '快递配送';
                $baseData['配送费用'] = $orderInfo['postFee']?$orderInfo['postFee']:'免邮';
                $_baseData = array_chunk($baseData, 3, true);
                $data['base_info'] = $_baseData;
                $data['opt'] = array(
                    'key'=>'可执行操作',
                    'val'=>'确认收到退货',
                    'data-target'=>'.return_dialog',
                );
                break;
            case 'refundetail': # 退款
                $baseData['下单时间'] = date('Y-m-d H:i:s',$orderInfo['createTs']/1000);
                $baseData['付款时间'] = date('Y-m-d H:i:s',$orderInfo['payTs']/1000);
                $baseData['申请退款时间'] = empty($refundInfo['createTs'])?'-':date('Y-m-d H:i:s',$refundInfo['createTs']/1000);
                $baseData['购买用户'] = $orderInfo['name'];
                $baseData['订单状态'] = '待退款';
                $baseData['退款金额'] = $refundInfo['aggreeRefundFee'];
                $baseData['配送方式'] = '快递配送';
                $baseData['配送费用'] = $orderInfo['postFee']?$orderInfo['postFee']:'免邮';
                $_baseData = array_chunk($baseData, 3, true);
                $data['base_info'] = $_baseData;
                $data['opt'] = array(
                    'key'=>'可执行操作',
                    'val'=>'退款',
                    'data-target'=>'.return_dialog',
                );
                break;
            case 'refunds':  # 已退款
                $baseData['下单时间'] = date('Y-m-d H:i:s',$orderInfo['createTs']/1000);
                $baseData['付款时间'] = date('Y-m-d H:i:s',$orderInfo['payTs']/1000);
                $baseData['申请退款时间'] = empty($refundInfo['createTs'])?'-':date('Y-m-d H:i:s',$refundInfo['createTs']/1000);
                $baseData['退款时间'] = date('Y-m-d H:i:s',$refundInfo['agreeRefundTs']/1000);
                $baseData['购买用户'] = $orderInfo['name'];
                $baseData['订单状态'] = '已退款';
                $baseData['退款金额'] = $refundInfo['aggreeRefundFee'];
                $baseData['配送方式'] = '快递配送';
                $baseData['配送费用'] = $orderInfo['postFee']?$orderInfo['postFee']:'免邮';
                $_baseData = array_chunk($baseData, 3, true);
                $data['base_info'] = $_baseData;
                break;
            case 'payment': # 待付款
                $baseData['下单时间'] = date('Y-m-d H:i:s',$orderInfo['createTs']/1000);
                $baseData['购买用户'] = $orderInfo['name'];
                $baseData['订单状态'] = '待付款';
                $_baseData = array_chunk($baseData, 3, true);
                $data['base_info'] = $_baseData;
                break;
            case 'complete': # 订单完成
                $baseData['发货单号'] = $orderInfo['invoiceNo'];
                $baseData['物流公司'] = $orderInfo['logisticsCompanyCn'];
                $baseData['下单时间'] = date('Y-m-d H:i:s',$orderInfo['createTs']/1000);
                $baseData['付款时间'] = date('Y-m-d H:i:s',$orderInfo['payTs']/1000);
                $baseData['发货时间'] = date('Y-m-d H:i:s',$orderInfo['consignTs']/1000);
                $baseData['完成时间'] = date('Y-m-d H:i:s',$orderInfo['endTs']/1000);
                $baseData['购买用户'] = $orderInfo['name'];
                $baseData['订单状态'] = $orderInfo['statusArr'][0];
                $baseData['配送方式'] = '快递配送';
                $baseData['配送费用'] = $orderInfo['postFee']?$orderInfo['postFee']:'免邮';
                $_baseData = array_chunk($baseData, 3, true);
                $data['base_info'] = $_baseData;
                break;
            case 'cancel':
                if(!empty($orderInfo['invoiceNo'])) {
                    $baseData['发货单号'] = $orderInfo['invoiceNo'];
                }
                $baseData['下单时间'] = date('Y-m-d H:i:s',$orderInfo['createTs']/1000);
                if(!empty($results['endTs'])) {
                    $baseData['取消时间'] = date('Y-m-d H:i:s', $orderInfo['createTs'] / 1000);
                }
                $baseData['购买用户'] = $orderInfo['name'];
                $baseData['订单状态'] = $orderInfo['statusArr'][0];
                $baseData['配送方式'] = '快递配送';
                $baseData['配送费用'] = $orderInfo['postFee']?$orderInfo['postFee']:'免邮';
                $_baseData = array_chunk($baseData, 3, true);
                $data['base_info'] = $_baseData;
                break;
            default:
                break;
        }

        if (!empty($refundInfo['reson'])){
            $data['reson']['key'] = '申请退货退款留言';
            $data['reson']['val'] = $refundInfo['reson'];
        }
        if (!empty($refundInfo['picUrlArr'])){
            $data['picUrlArr'] = $refundInfo['picUrlArr'];
        }

        if(!empty($refundInfo['returnReceiverName'])){
            $data['return_info'] = array(  # 退货收货人信息
                '姓名'=>$refundInfo['returnReceiverName'],
                '手机'=>$refundInfo['returnPhone'],
                '地址'=>$refundInfo['returnAddress'],
            );
        }

        $data['to_info'] = array(  # 收货人信息
            '姓名'=>$orderInfo['name'],
            '手机'=>$orderInfo['mobile'],
            '地址'=>$orderInfo['state'].$orderInfo['city'].$orderInfo['district'].$orderInfo['address'],
        );
        $data['orders'] = $orderInfo['orders'];
        $data['trade_opt_info'] = array(
            'invoiceNo' => $orderInfo['invoiceNo'],
            'logisticsCompany' => $orderInfo['logisticsCompany'],
            'tid' => $orderInfo['tid'],
            'tplStatus' => $tplStatus,
        );
        if(!empty($orderInfo['payment'])){
            $data['trade_opt_info']['payment'] = $orderInfo['payment'];
        }
        if (!empty($refundInfo['aggreeRefundFee'])){
            $data['trade_opt_info']['aggreeRefundFee'] = $refundInfo['aggreeRefundFee'];
        }

        return $data;
    }

    /**
     * @param $tid
     * @param array $body
     * @return array|bool
     * 订单发货
     */
    public function tradeDelivery($tid, array $body){
        if(empty($tid) || empty($body)) return false;
        $url = $this->getUrl('/'.$tid.'/delivery');
        $res = $this->curl($url,'put', $body);
        return $this->checkApiResult($res,$url,$body);
    }

    /**
     * @param $tid
     * @param array $body
     * @return array|bool
     * 订单退款/售后审核
     */
    public function returnCheck($tid, array $body){
        if(empty($tid) || empty($body)) return false;
        $url = $this->getUrl('/'.$tid.'/return-check');
        $res = $this->curl($url,'put', $body);
        return $this->checkApiResult($res,$url,$body);
    }

    /**
     * @param array $data
     * @return array
     */
    public function tradeCheck(array $data){
        $returnData = $optData = array();
        $returnData['code'] = 200;
        if(!empty($data)){
            if (empty($data['tid'])){
                $returnData['code'] = 401;
                $returnData['msg'] = '参数错误';
                return $returnData;
            }
            $tid = trim($data['tid']);
            if(empty($data['aggree'])){
                $optData['aggree'] = false;
            }else{
                $optData['aggree'] = true;
                if(empty($data['return_type'])){
                    $returnData['code'] = 402;
                    $returnData['msg'] = '退货类型参数错误';
                    return $returnData;
                }
                if(!empty($data['returnReceiverName'])){
                    $optData['returnReceiverName'] = $this->formatLen($data['returnReceiverName'],9);
                }
                if(!empty($data['receiverPhone'])){
                    if(!preg_match('/^1[3-9]\d{9}$/',$data['receiverPhone'])){
                        $returnData['code'] = 403;
                        $returnData['msg'] = '电话号码参数错误';
                        return $returnData;
                    }
                    $optData['receiverPhone'] = $data['receiverPhone'];
                }
                if(!empty($data['returnAddress'])){
                    $optData['returnAddress'] = $data['returnAddress'];
                }
                if(!empty($data['refundAmount'])){
                    if($data['refundAmount'] > $data['refundAmount_check']){
                        $returnData['code'] = 404;
                        $returnData['msg'] = '退款金额参数错误';
                        return $returnData;
                    }
                    $optData['refundAmount'] = sprintf('%.2f',$data['returnAddress']);
                }
            }

            $res = $this->returnCheck($tid,$optData);
            if($res){
                $returnData['msg'] = '操作成功';
            }else{
                $returnData['code'] = 400;
                $returnData['msg'] = '操作失败';
            }
        }
        return $returnData;
    }

    /**
     * @param $tid
     * @param array $query
     * @return array|bool
     * 售后确认收到退货
     */
    public function returnGoodConfirm($tid, array $query){
        if (empty($tid) || empty($query)) return false;
        $url = $this->getUrl('/'.$tid.'/return-good-confirm',$query);
        $res = $this->curl($url,'put');
        return $this->checkApiResult($res,$url);
    }

    # 订单退款
    public function tradeRefund($tid, array $query){
        if (empty($tid) || empty($query)) return false;
        $url = $this->getUrl('/'.$tid.'/refund',$query);
        $res = $this->curl($url,'put');
        return $this->checkApiResult($res,$url);
    }

    /**
     * @param array $data
     * 订单数据导出
     */
    public function exportTrade(array $data){
        $exportData = array();
        $head = array('订单号','下单时间','收货人','手机','收货地址','商品名称','商品价格','订单状态');
        if ($data){
            foreach ($data as $k=>$v){
                $temp = array();
                $temp[] = (string)$v['tid'];
                $temp[] = date('Y-m-d H:i:s',strtotime($v['gmtCreated']));
                $temp[] = $v['name'];
                $temp[] = $v['mobile'];
                $temp[] = $v['state'] . $v['city'] . $v['district'] . $v['address'];
                $temp[] = $v['orders'][0]['itemName'];
                $temp[] = $v['payment']?$v['payment']:$v['totalFee'];
                $temp[] = $v['status_name'];
                $exportData[] = $temp;
            }
        }
        $this->csv_export($exportData,$head);
    }

    /**
     * 导出excel(csv)
     * @param array $data 导出数据
     * @param array $headlist 第一行,列名
     * @param string $fileName 输出Excel文件名
     */
    private function csv_export($data = array(), $headlist = array(), $fileName='导出数据信息') {
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="'.$fileName.'.csv"');
        header('Cache-Control: max-age=0');
        //打开PHP文件句柄,php://output 表示直接输出到浏览器
        $fp = fopen('php://output', 'a');
        //输出Excel列名信息
        foreach ($headlist as $key => $value) {
            //CSV的Excel支持GBK编码，一定要转换，否则乱码
            $headlist[$key] = iconv('utf-8', 'gbk', $value);
        }
        //将数据通过fputcsv写到文件句柄
        fputcsv($fp, $headlist);
        //计数器
        $num = 0;
        //每隔$limit行，刷新一下输出buffer，不要太大，也不要太小
        $limit = 100000;
        //逐行取出数据，不浪费内存
        $count = count($data);
        for ($i = 0; $i < $count; $i++) {
            $num++;
            //刷新一下输出buffer，防止由于数据过多造成问题
            if ($limit == $num) {
                ob_flush();
                flush();
                $num = 0;
            }
            $row = $data[$i];
            foreach ($row as $key => $value) {
                $row[$key] = iconv('utf-8', 'gbk', $value);
            }
            fputcsv($fp, $row);
        }
    }

}