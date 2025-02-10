# Authorize / Authenticate  ✨
 
此存储库包含有关如何使用[Monify]授权/验证以获取承载令牌的示例代码(https://monnify.com/)PHP中的API。
>注：
>*请注意，这仅用于演示目的，而不是生产代码*
 
##如何获取API密钥
您的API密钥可在您的Monnify仪表板上获得。您可以通过以下步骤找到它：
-登录您的[Monify仪表板](https://app.monnify.com/login).
-导航到设置。
-在设置选项卡上选择API密钥和Webhook。
-获取您唯一的API密钥和密钥。

## 使用CURL PHP获取令牌

```php
<?php

// 一个非常简单的PHP示例，使用CURL向Monnify发送HTTP POST以获取访问令牌


// 1. 从Monnify Dashboard获取API密钥和密钥
$API_Key = "MK_TEST_SAF7HR5F3F";
$Secret_Key = "4SY6TNL8CK3VPRSBTHTRG2N8XXEGC6NL";

$ch = curl_init();

// 将“ApiKey”+“：”+“SecretKey”连接起来，然后Base 64用单词“Basic”对字符串和前缀进行编码。请参阅下一行
$headers = array(
    'Content-Type:application/json',
    'Authorization: Basic '. base64_encode($API_Key . ":" . $Secret_Key) // <---
);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_URL,"https://sandbox.monnify.com/api/v1/auth/login");
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$output = curl_exec($ch);

curl_close($ch);

$json = json_decode($output, true);
// print_r($json);

$accessToken = $json['responseBody']['accessToken'];

// this is your access token
echo $accessToken;;

// Further processing ...
if ($server_output == "OK") { ... } else { ... }
```
 

## 使用Guzzle HTTP PHP客户端获取令牌
Guzzle帮助您使用PHP发出请求。要了解更多关于Guzzle的信息，请访问https://docs.guzzlephp.org/en/stable/overview.html

```php
<?php
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;

// 1. 从Monnify Dashboard获取API密钥和密钥
$userName = 'MK_TEST_SAF7HR5F3F'; // API key
$password = '4SY6TNL8CK3VPRSBTHTRG2N8XXEGC6NL'; // Secret Key

$httpClient = new Client();

$response = $httpClient->post(
    'https://sandbox.monnify.com/api/v1/auth/login',
    [
        RequestOptions::AUTH => [$userName, $password]
    ]
);

$json = (string) $response->getBody();
$json = json_decode($json); // Using this you can access any key like below
$accessToken = $json->responseBody->accessToken; //access key
```
 
 
 
## 使用Unirest for PHP获取令牌–PHP HTTP客户端
To 阅读更多关于Unirest for PHP的信息请访问 https://github.com/Kong/unirest-php

 ```php
 <?php
 
 // 1. 从Monnify Dashboard获取API密钥和密钥
$API_Key = "MK_TEST_SAF7HR5F3F";
$Secret_Key = "4SY6TNL8CK3VPRSBTHTRG2N8XXEGC6NL";

 Unirest::post(
     "https://sandbox.monnify.com/api/v1/auth/login", 
     array( "Accept" => "application/json",  "Content-Type" => "application/json" ), 
     NULL, 
     $API_Key, 
     $Secret_Key
 )


 ```
 
 
## 发展

想贡献吗？太棒了发送邮件至integration-support@monnify.com

## License
**Free Software!**
The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

[link-author]: https://jimiejosh.com
 
   [l1]: <https://github.com/jimiejosh/monnify-php-sample-codes/tree/master/sample-codes/authentication/README.md>
   [l2]: <https://github.com/jimiejosh/monnify-php-sample-codes/tree/master/sample-codes/webhooks/README.md>
   [l3]: <https://github.com/jimiejosh/monnify-php-sample-codes/tree/master/sample-codes/reservedaccount/README.md>
   [l4]: <https://github.com/jimiejosh/monnify-php-sample-codes/tree/master/sample-codes/bankverification/README.md>
   [l5]: <https://github.com/jimiejosh/monnify-php-sample-codes/tree/master/sample-codes/transfer/README.md>
   [l6]: <https://github.com/jimiejosh/monnify-php-sample-codes/tree/master/sample-codes/card/README.md>
