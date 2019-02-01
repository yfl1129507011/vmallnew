<?php
/**
 * Created by PhpStorm.
 * User: acewill
 * Date: 2018/12/26
 * Time: 15:41
 * 运费模板
 */
class Admin_DeliveryTplModel extends VmallNewModel{
    protected $cls = '/delivery';

    public function formatData(array $data){
        if ($data && !empty($data['results'])){
            $city = Config::get(APP_NAME.'.citydata.city');
            $data['results'] = array_map(function ($v) use($city){
                $dests = explode(';',$v['templateDests']);
                if($dests){
                    $valuation = $v['valuation'];
                    if($valuation == 1){
                        $v['tpl_info'][] = array('可配送区域','首件（个）','运费（元）','续件（个）','运费（元）');
                    }elseif ($valuation == 2){
                        $v['tpl_info'][] = array('可配送区域','首重（kg）','运费（元）','续重（Kg）','运费（元）');
                    }else{
                        $v['tpl_info'][] = array('可配送区域','计价方式');
                    }
                    if(in_array($valuation, array(1,2))){
                        $standards = explode(';',$v['templateStartStandards']);
                        $fees = explode(';',$v['templateStartFees']);
                        $adds = explode(';',$v['templateAddStandards']);
                        $addFees = explode(';',$v['templateAddFees']);
                    }
                    foreach ($dests as $_k=>$_v){
                        if(empty($_v)) continue;
                        $temp = array();
                        $res = explode(',',$_v);
                        $res = array_map(function ($vv) use($city){
                            return $city[$vv];
                        }, $res);
                        $temp[] = implode('-',$res);
                        if(in_array($valuation, array(1,2))) {
                            $temp[] = $standards[$_k];
                            $temp[] = $fees[$_k];
                            $temp[] = $adds[$_k];
                            $temp[] = $addFees[$_k];
                        }else{
                            $temp[] = ($valuation==3)?'商家包邮':'固定运费';
                        }
                        $v['tpl_info'][] = $temp;
                    }
                }
                return $v;
            }, $data['results']);
        }
        return $data;
    }

    /**
     * @param array $params
     * @param bool $isRes
     * @return bool
     * 获取模板信息
     */
    public function getTpls(array $params, $isRes=true){
        $url = $this->getV2Url('', $params);
        $res = $this->curl($url);
        if($this->checkApiResult($res, $url) !== false){
            return $isRes?$res['results']:$this->formatData($res);
        }
        return false;
    }
}