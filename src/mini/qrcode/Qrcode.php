<?php
/*
 * This file is part of the abei2017/yii2-wx
 *
 * (c) abei <abei@nai8.me>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace abei2017\wx\mini\qrcode;

use abei2017\wx\core\Driver;
use yii\httpclient\Client;
use abei2017\wx\core\AccessToken;
use abei2017\wx\core\Exception;

/**
 * Qrcode
 * 二维码/小程序码
 * @author abei<abei@nai8.me>
 * @link https://nai8.me/yii2wx
 * @package abei2017\wx\mini\qrcode
 */
class Qrcode extends Driver {

    //  获取不受限制的小程序码
    const API_UN_LIMIT_CREATE = 'https://api.weixin.qq.com/wxa/getwxacodeunlimit';

    //  生成永久小程序码，有数量限制（可定制）
    const API_CREATE = 'https://api.weixin.qq.com/wxa/getwxacode';

    //  生成永久小程序码，有数量限制（简单类型）
    const API_A_CREATE = 'https://api.weixin.qq.com/cgi-bin/wxaapp/createwxaqrcode';

    private $accessToken = null;

    public function init(){
        parent::init();
        $this->accessToken = (new AccessToken(['conf'=>$this->conf,'httpClient'=>$this->httpClient]))->getToken();
    }

    /**
     * 生成一个不限制的小程序码
     * @param $scene
     * @param $page string 路径，不能带阐述
     * @param array $extra
     * @throws Exception
     * @return \yii\httpclient\Request;
     */
    public function unLimit($scene,$page,$extra = []){
        $params = array_merge(['scene'=>$scene,'page'=>$page],$extra);
        $response = $this->post(self::API_UN_LIMIT_CREATE."?access_token=".$this->accessToken,$params)
            ->setFormat(Client::FORMAT_JSON)->send();

        if($response->isOk == false){
            throw new Exception(self::ERROR_NO_RESPONSE);
        }

        $contentType = $response->getHeaders()->get('content-type');
        if(strpos($contentType,'json') != false){
            $data = $response->getData();
            if(isset($data['errcode'])){
                throw new Exception($data['errmsg'],$data['errcode']);
            }
        }

        return $response->getContent();
    }

    /**
     * 生成永久小程序码
     * 数量有限
     * 该方法生成的小程序码具有更多的可指定性
     */
    public function forever($path,$extra = []){
        $params = array_merge(['path'=>$path],$extra);
        $response = $this->post(self::API_CREATE."?access_token=".$this->accessToken,$params)
            ->setFormat(Client::FORMAT_JSON)->send();

        if($response->isOk == false){
            throw new Exception(self::ERROR_NO_RESPONSE);
        }

        $contentType = $response->getHeaders()->get('content-type');
        if(strpos($contentType,'json') != false){
            $data = $response->getData();
            if(isset($data['errcode'])){
                throw new Exception($data['errmsg'],$data['errcode']);
            }
        }

        return $response->getContent();
    }

    /**
     * 生成永久小程序码
     * 数量有限
     *
     * @param $path string 扫码进入的小程序页面路径
     * @param int $width 二维码的宽度
     * @since 1.2
     */
    public function simpleForever($path, $width = 430){
        $params = ['path'=>$path,'width'=>$width];
        $response = $this->post(self::API_A_CREATE."?access_token=".$this->accessToken,$params)
            ->setFormat(Client::FORMAT_JSON)->send();

        if($response->isOk == false){
            throw new Exception(self::ERROR_NO_RESPONSE);
        }

        $contentType = $response->getHeaders()->get('content-type');
        if(strpos($contentType,'json') != false){
            $data = $response->getData();
            if(isset($data['errcode'])){
                throw new Exception($data['errmsg'],$data['errcode']);
            }
        }

        return $response->getContent();
    }
}