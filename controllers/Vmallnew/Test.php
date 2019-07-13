<?php
/**
 * Created by PhpStorm.
 * User: 杨飞龙(yangfeilong.bj@acewill.cn)
 * Date: 2019/7/5
 * Time: 14:38
 */
class Vmallnew_TestController extends Yaf_Controller_Abstract{

    private function shardTable($table, $shardKey = array()) {

        $appName = Bootstrap_Env::get('app_name');

        $result = array();
        $module = substr($table, 0, strpos($table, '_'));
        $modules = Config::get($appName . '.db.modules');
        $tables = Config::get($appName . '.db.' . $module . '.tables');
        $slaveTables = Config::get($appName . '.db.' . $module . '.slave_tables');
        if (!in_array($module, $modules)) {
            throw new DB_Exception('Can not find db config for table ' . $table);
        }

        $result = array();

        // 设置默认字符集
        $result['charset'] = $tables[$table]['charset'];
        if (!$result['charset']) {
            $result['charset'] = 'utf8';
        }

        // 不分库的情况
        if (!$shardKey || !count($shardKey) || !array_key_exists($table, $tables)) {

            // DSN为多个数据源的数组，如果表没设置dsn，默认取第0个
            $dsnConfig = Config::get($appName . '.db.' . $module);

            // 主库的DSN配置
            $tableMasterDSN = intval($tables[$table]['dsn']);
            $result['db'] = $dsnConfig[$tableMasterDSN];
            $result['db_slave_ro2'] = $dsnConfig[2];

            if ($slaveTables && array_key_exists($table, $slaveTables)) {

                // 从库的DSN配置
                $tableSlaveDSN = intval($slaveTables[$table]['dsn']);
                $result['db_slave'] = $dsnConfig[$tableSlaveDSN];
            } else {
                $result['db_slave'] = $result['db'];
            }

            $result['table'] = $table;

            return $result;
        }

        // 分库分表
        $shardConfig = Config::get($appName . '.db.' . $module . '.' . $table . '.shard');
        if ($shardConfig) {
            $shardData = DB_Shard::getDB($shardConfig, $shardKey);
            if ($shardData) {
                $result['db'] = $result['db_slave'] = $shardData['db'];
                if ($shardData['table']) {
                    $result['table'] = $shardData['table'];
                } else {
                    $result['table'] = $table;
                }
            } else {
                throw new DB_Exception('Shard table error, check table config.');
            }
        } else {
            throw new DB_Exception('Can not find db config for table ' . $table);
        }

        return $result;

    }

    public function indexAction(){
        Bootstrap_Env::set('app_name', 'MeituanUser');
        $table = 'fans_coupon2fans_logs';
        $shardKey = [
            #'bid' => '2203191437',
            'bid' => '1112654546',
        ];
        $res = $this->shardTable($table,$shardKey);
        echo json_encode($res);die;
    }
}