<?php

/**
 * 调用京东联盟接口
 * 
 * 所用京东联盟的接口调用
 * @author jinggz
 * @version v1.0
 * @since 2019/1/22
 */

 /**
  * cps 二合一链接 有效时间问题
  * 文档链接：https://union.jd.com/msg/detail?queryId=235
  * ①联盟长链接 (https://union-click.jd.com/xxx) 继续保持长期有效。若推广资源需要长期有效的推广链接，建议领取联盟长链接。
  * ②联盟短链接 (https://u.jd.com/xxx) 有效期调整为60天。若在微信生态下推广，建议领取短链接更为简洁美观。
  * 链接被领取60天后将自动失效。当用户访问失效链接时，将会跳转至京东首页，且产生的订单将无法跟单。
  *（联盟官方工具生成的二维码海报与小程序卡片的有效期与联盟短链接一致）
  */

class JdApi
{
    //京东key
     private $app_key; 

    // 京东密钥
     private $app_secret; 
    
    // 京东auth授权 accesstoken
     private $access_token = ''; 
    
    //联盟id
     private $uninid = '';

     //当前时间
     private $timestamp;

     //json数据
     public $format = 'json';

     //版本
     public $v = '1.0';

     //签名方法
     private $sign_method = 'md5';

     //请求域名
     public $serverUrl = "http://router.jd.com/api";

     /**
      * 构造函数 
      * 
      * @access public
      * @param $app_key  京东key
      * @param $app_secret  京东密钥
      * @param $access_token  京东outh授权 accesstoken
      * @param $server_url 回调地址 
      * @param $uninid 联盟id
      */
     public function __construct($app_key,$app_secret)
     {
          $this->app_key = $app_key;
          $this->app_secret = $app_secret;
          $this->timestamp = date('Y-m-d H:i:s');
     }


     /**
      * RequestJdApi 请求京东接口 返回数据 
      * 
      * @access public
      * @param $config 生成签名的数组
      * @return $data 请求到的京东数据
      */
     public function RequestJdApi($config)
     {
          if (empty($config)) {
               return false;
          }
          //1.组装数组
          $arr = $this->GroutIngArr($config);
          if (empty($arr)) {
               return false;
          }
          //2.生成签名
          $jd_sign = $this->JdSign($arr);
          if (empty($jd_sign)) {
               return false;
          }
          //3.组装http请求
          $arr['sign'] = $jd_sign;
          $url = $this->serverUrl;
          //4.发起http请求 返回数据
          $data = $this->CurlPost($url, $arr);
          if (empty($data)) {
               return false;
          }
          if (is_null(json_decode($data))) {
               return false;
          }
          $json_data = json_decode($data,true);
          return $json_data;

     }


     /**
      *  GroutIngArr 组装数组
      *  
      * @access public
      * @param $config 数组
      * @return $arr 组装完的数组
      */
     public function GroutIngArr($config)
     {
          if (empty($config)) {
               return false;
          }
          //组装数组
          $config['app_key'] = $this->app_key;
          $config['timestamp'] = $this->timestamp;
          $config['format'] = $this->format;
          $config['v'] = $this->v;
          $config['sign_method'] = $this->sign_method;
          return $config;
     }


     /**
      * JdSign 生成京东签名
      *
      * @access protected
      * @param $config 数组签名参数
      * @example 1.按照ASCII码表排序 2.拼接所有参数 3.在字符串两端拼接app_secret 4.md5进行加密并转换大写 
      * @return $sign 生成的签名 
      */
     protected function JdSign($arr)
     {
          if (!empty($arr)) {
               // 1.按照ASCII码表排序
               $p = ksort($arr);
               if ($p) {
                    $str = '';
                    // 2.拼接所有参数
                    foreach ($arr as $k => $val) {
                         $str .= $k . $val;
                    }
                    $strs = trim($str);
                    // 3.在字符串两端拼接app_secret
                    // 4.md5进行加密并转换大写  得到签名
                    $sign = strtoupper(md5($this->app_secret . $strs . $this->app_secret));
                    return $sign;
               }
          }
          return false;
     }



     /**
      *  CurlPost post请求
      * 
      * @access public
      * @param $url 请求的链接
      * @param $post_data 请求的数组
      * @return $data 返回请求到的数据
      */
     public function CurlPost($url, $post_data)
     {
         //初始化
          $curl = curl_init();
          // $this_header = array(
          //      "content-type: application/x-www-form-urlencoded; 
          //      charset=UTF-8"
          // );
          // //设置utf-8 编码
          // curl_setopt($curl, CURLOPT_HTTPHEADER, $this_header);
             //设置抓取的url
          curl_setopt($curl, CURLOPT_URL, $url);
             //设置头文件的信息作为数据流输出
          curl_setopt($curl, CURLOPT_HEADER, 0);
             //设置获取的信息以文件流的形式返回，而不是直接输出。
          curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
             //设置post方式提交
          curl_setopt($curl, CURLOPT_POST, 1);
             //设置post数据  
          curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($post_data));
          // curl_setopt($curl, CURLOPT_POSTFIELDS, $post_data);
          //不验证证书下同
          // curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
          // curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false); 
        //请求之前等待时间
          curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);
        //允许执行的时间
          curl_setopt($curl, CURLOPT_TIMEOUT, 60);
            //执行命令
          $data = curl_exec($curl);
             //关闭URL请求
          curl_close($curl);
             //显示获得的数据
          return $data;
     }



}
