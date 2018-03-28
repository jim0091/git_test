<?php
/**
 * 阿里云CDN预热缓存.
 *
 * @author Mango <155382788@qq.com>
 *
 */
class CdnPushObjectModel
{
	private $requestUrl = 'https://cdn.aliyuncs.com/?';
	private $AccessKeyId = 'LTAIDZA5RpSM9OEE';
	private $accessSecret = 'gMouwcHBQSYKiqEX2wO5rqod62DXBu';
    private $dateTimeFormat = 'Y-m-d\TH:i:s\Z';	
	private $queryParameters = array();
	private $method;
	private $format;

    //构造函数
    public function __construct()
    {
        $this->method = "GET";
        $this->format = "JSON";
    }

	public function setObjectPath($objectPath) {
		$this->queryParameters["ObjectPath"] = $objectPath;
	}

    public function composeUrl()
    {
        $apiParams = $this->queryParameters;
        foreach ($apiParams as $key => $value) {
            $apiParams[$key] = $value;
        }
	    $apiParams['AccessKeyId'] = $this->AccessKeyId;
	    $apiParams['Format'] = $this->format;
	    $apiParams['SignatureMethod'] = 'HMAC-SHA1';
	    $apiParams['SignatureVersion'] = '1.0';
	    $apiParams['SignatureNonce'] = uniqid();
	    date_default_timezone_set("GMT");
	    $apiParams['Timestamp'] = date($this->dateTimeFormat);
	    $apiParams['Version'] = '2014-11-11';
	    $apiParams['Action'] = 'PushObjectCache';
        $apiParams["Signature"] = $this->computeSignature($apiParams, $this->accessSecret);

        foreach ($apiParams as $apiParamKey => $apiParamValue) {
            $this->requestUrl .= "$apiParamKey=" . urlencode($apiParamValue) . "&";
        }
        return substr($this->requestUrl, 0, -1);
    }

    private function computeSignature($parameters, $accessKeySecret)
    {
        ksort($parameters);
        $canonicalizedQueryString = '';
        foreach ($parameters as $key => $value) {
            $canonicalizedQueryString .= '&' . $this->percentEncode($key). '=' . $this->percentEncode($value);
        }
        $stringToSign = $this->method.'&%2F&' . $this->percentencode(substr($canonicalizedQueryString, 1));
        $signature = $this->signString($stringToSign, $accessKeySecret."&");

        return $signature;
    }

    private function percentEncode($str)
    {
        $res = urlencode($str);
        $res = preg_replace('/\+/', '%20', $res);
        $res = preg_replace('/\*/', '%2A', $res);
        $res = preg_replace('/%7E/', '~', $res);
        return $res;
    }

    private function signString($source, $accessSecret)
    {
        return base64_encode(hash_hmac('sha1', $source, $accessSecret, true));
    }

    private function curl($url, $httpMethod = "GET")
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $httpMethod);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_FAILONERROR, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 80);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        //https request
        if (strlen($url) > 5 && strtolower(substr($url, 0, 5)) == "https") {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        }
        curl_exec($ch);
        $result = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return $result;
    }

    public function doAction()
    {
    	$url = $this->composeUrl();
    	$status = $this->curl($url, $this->method);
        if (200 <= $status && 300 > $status) {
            return true;
        }
        return false;
    }
}