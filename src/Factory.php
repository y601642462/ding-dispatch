<?php


namespace Wandell\Dispatch;

use Doctrine\Common\Cache\FilesystemCache;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;

class Factory
{
    private $config;
    private $base_url = 'https://oapi.dingtalk.com/';
    private $access_token;
    private $timestamp;

    public function __construct($config)
    {
        $this->config = $config;
        $this->timestamp = $this->msectime();
    }

    protected function getConfig()
    {
        return $this->config;
    }

    //返回当前的毫秒时间戳
    private function msectime()
    {
        list($s1, $s2) = explode(' ', microtime());
        return (float)sprintf('%.0f', (floatval($s1) + floatval($s2)) * 1000);
    }

    /**
     * 获取随机字符串
     * @param int $length
     * @return string
     */
    protected function getSuiteTicket($length = 10)
    {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $str = '';
        for ($i = 0; $i < $length; $i++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $str;
    }

    /**
     * 获取签名
     * @param $suite_ticket
     * @return string
     */
    private function getSignature($suite_ticket)
    {
        $custom_secret = $this->config['customSecret'];
        $signature = $this->timestamp . "\n" . $suite_ticket;
        $sign = hash_hmac('sha256', $signature, $custom_secret, true);
        $signature = base64_encode($sign);

        return $signature;
    }

    /**
     * 緩存
     * @param $name
     * @param $value
     * @param $expires_in
     * @return bool
     */
    private function set_cache($name, $value, $expires_in)
    {
        $cache = new FilesystemCache('../runtime/cache');
        $res = $cache->save($name, $value, $expires_in);

        return $res;
    }

    /**
     * 獲取緩存
     * @param $name
     * @return false|mixed
     */
    private function get_cache($name)
    {
        $cache = new FilesystemCache('cache');
        $res = $cache->fetch($name);

        return $res;
    }

    /**
     * post参数转get参数
     * @param $data
     * @return string
     */
    protected function make_url_query($data)
    {
        $res = '?';
        foreach ($data as $key => $datum) {
            $res .= $key . '=' . urlencode($datum) . '&';
        }
        $res = substr($res, 0, -1);

        return $res;
    }

    /**
     * 获取accessToken
     * @return false|mixed
     * @throws \Exception
     */
    protected function getAccessToken()
    {
        $access_token = $this->get_cache("access_token".$this->config['customKey']);
        if ($access_token) {
            return $access_token;
        }
        $suite_ticket = $this->getSuiteTicket();
        $data = ['auth_corpid' => $this->config['auth_corpid']];
        $url_data = [
            'signature' => $this->getSignature($suite_ticket),
            'accessKey' => $this->config['customKey'],
            'timestamp' => $this->timestamp,
            'suiteTicket' => $suite_ticket,
        ];
        $res = $this->make_url_query($url_data);
        $flag = $this->request('service/get_corp_token' . $res, $data,"post");
        if ($flag['errcode'] != 0) {
            throw new \Exception($flag['errmsg'], $flag['errcode']);
        }
        $cache = new FilesystemCache('cache');
        $cache->save("access_token".$url_data['accessKey'], $flag['access_token'], 7200);

        return $flag['access_token'];
    }

    /**
     * 获取jsapi_ticket
     * @return mixed|string
     * @throws \Exception
     */
    protected function getTicket()
    {
        $token = $this->getAccessToken();
        $url_data = [
            'access_token'=>$token,
        ];
        $jsapi_ticket = $this->get_cache("jsapi_ticket".$url_data['access_token']);
        if ($jsapi_ticket) {
            return $jsapi_ticket;
        }
        $res = $this->make_url_query($url_data);
        $flag = $this->request('get_jsapi_ticket' . $res);
        if ($flag['errcode'] != 0) {
            throw new \Exception($flag['errmsg'], $flag['errcode']);
        }
        $cache = new FilesystemCache('cache');
        $cache->save("jsapi_ticket".$url_data['access_token'], $flag['ticket'], 7200);

        return $flag['ticket'];
    }

    /**
     * 获取后台token
     * @param $corpid
     * @param $soo_secret
     * @return false|string
     */
    protected function getSsoToken()
    {
        $data = [
            'corpid' => $this->config['auth_corpid'],
            'corpsecret' => $this->config['sso_secret']
        ];

        $flag = $this->request("sso/gettoken".$this->make_url_query($data));
        if ($flag['errcode'] != 0) {
            throw new \Exception($flag['errmsg'], $flag['errcode']);
        }
        return $flag['access_token'];
    }

    /**
     * http请求
     * @param $action
     * @param array $data
     * @param string $method
     * @return false|string
     */
    protected function request($action, $data = [], $method = 'get')
    {
        $client = new Client(['base_uri' => $this->base_url]);
        if ($method == 'post') {
            $res = $client->post($action, [RequestOptions::JSON => $data]);
        } else {
            $res = $client->get($action);
        }
        $body = $res->getBody();
        $flag = json_decode($body->getContents(), true);

        return $flag;
    }

    /**
     * JSAPI鉴权配置
     * @return array
     */
    protected function h5Config($url)
    {
        $nonce_str = $this->getSuiteTicket();
        $timeStamp = $this->timestamp;
        $plain = 'jsapi_ticket=' . $this->getTicket() . '&noncestr=' . $nonce_str . '&timestamp=' . $timeStamp . '&url=' . $url;
        $signature = sha1($plain);
        $data = [
            'agentId'=> $this->config['agent_id'],
            'corpId'=> $this->config['auth_corpid'],
            'timeStamp'=> $timeStamp,
            'nonceStr'=> $nonce_str,
            'signature'=>$signature,
        ];

        return $data;
    }

    /**
     * 获取当前时间戳
     * @return float
     */
    protected function getTimestamp()
    {
        return $this->timestamp;
    }

    /**
     * 扫码登录获取签名
     * @return string
     */
    protected function getSign()
    {
        $custom_secret = $this->config['appSecret'];
        $signature = $this->timestamp;
        $sign = hash_hmac('sha256', $signature, $custom_secret, true);
        $signature = base64_encode($sign);

        return $signature;
    }
}