<?php

namespace Zzbzh\Szsr;

use GuzzleHttp\Client;

class Recharge
{
    private static $singleInstance;

    private static $merchantName;

    private static $merchantKey;

    private static $httpClient;

    /**
     * Recharge constructor.
     * @param $merchantName string 商户名称
     * @param $merchantKey string 商户密钥
     */
    private function __construct($merchantName, $merchantKey)
    {
        self::$merchantName = $merchantName;
        self::$merchantKey = $merchantKey;
        self::$httpClient = new Client([
            'base_uri' => 'http://api.sohan.hk:50080/',
            'timeout' => 5.0
        ]);
    }


    private function __clone()
    {

    }

    /**
     * 声明静态调用方法
     * 目的：保证该方法的调用全局唯一
     * @param $merchantName string 商户名称
     * @param $merchantKey string  商户密钥
     * @return Recharge
     */
    public static function getInstance($merchantName, $merchantKey)
    {
        if (!self::$singleInstance) {
            self::$singleInstance = new self($merchantName, $merchantKey);
        }

        return self::$singleInstance;
    }


    /**
     * 充值接口
     * @param $order_no string 订单号
     * @param string $mobile 充值账户
     * @param string $notify_url 通知地址
     * @param integer $cash 整型金额，以元为单位
     * @return array
     */
    public function orderCreate($order_no, $mobile = '', $notify_url = '', $cash = 10)
    {
        $params['action'] = 'CZ';
        $params['orderId'] = $order_no;
        $params['chargeAcct'] = $mobile;
        $params['chargeCash'] = (int)$cash;
        $params['chargeType'] = 0;
        $params['retUrl'] = urlencode($notify_url);

        return $this->query($params, 'API');
    }

    /**
     * 查询订单
     * @param $order_no string 订单号
     * @return array
     */
    public function orderQuery($order_no)
    {
        return $this->query(['customerorderno' => $order_no], 'queryorder.action');
    }


    /**
     * 查询商户信息
     * @return mixed
     */
    public function merchantQuery()
    {
        return $this->query(['action' => 'YE'], 'api');
    }

    /**
     *
     * 签名函数
     * @param array $params
     * @return array
     */
    private function params($params = [])
    {
        $data = [];
        $data['sign'] = md5(iconv('utf-8', 'gbk', json_encode($params,256)).self::$merchantKey);
        $data['agentAccount'] = self::$merchantName;
        $data['busiBody'] = $params;
        
        return $data;
    }

    /**
     * 发出查询请求
     * @param $params array 请求参数
     * @param $action string 请求地址
     * @return mixed
     */
    private function query($params, $action = '')
    {
        $query = $this->params($params);

        dump(iconv('utf-8', 'gbk', json_encode($query, 256)));
       
        $response = self::$httpClient->request('POST', $action, ['body' => iconv('utf-8', 'gbk', json_encode($query, 256))]);

        return json_decode(iconv('gbk', 'utf-8', $response->getBody()->getContents()), true);
    }
}