# Monnify Webhooks✨

 
此存储库包含有关如何实现[Monify]的示例代码(https://monnify.com/)PHP中的Webhooks。我们尽可能简化了下面的示例，以向您展示实现webhook所需的步骤。
>注：
>*请注意，这仅用于演示目的，而不是生产代码*
 
##PHP webhook实现示例


```php
<?php

// IP白名单-根据统一IP 35.242.133.146验证IP地址
$ip = ($_SERVER['REMOTE_ADDR'])?$_SERVER['REMOTE_ADDR']:$_SERVER['REMOTE_HOST'];
if( $ip != "35.242.133.146") die("Invalid IP");

//获取原始json请求字符串

//{"eventData":{"product":{"reference":"111222333","type":"OFFLINE_PAYMENT_AGENT"},"transactionReference":"MNFY|76|20211117154810|000001","paymentReference":"0.01462001097368737","paidOn":"17/11/2021 3:48:10 PM","paymentDescription":"Mockaroo Jesse","metaData":{},"destinationAccountInformation":{},"paymentSourceInformation":{},"amountPaid":78000,"totalPayable":78000,"offlineProductInformation":{"code":"41470","type":"DYNAMIC"},"cardDetails":{},"paymentMethod":"CASH","currency":"NGN","settlementAmount":77600,"paymentStatus":"PAID","customer":{"name":"Mockaroo Jesse","email":"111222333@ZZAMZ4WT4Y3E.monnify"}},"eventType":"SUCCESSFUL_TRANSACTION"}
$raw_request = file_get_contents('php://input');



// 在monnidy仪表板的开发人员菜单中找到您的密钥
$SECRET_KEY = '91MUDL9N6U3BQRXBQ2PJ9M0PW4J22M1Y';

// 接下来，我们需要计算并比较通过标头作为“monnify签名”发送的哈希值。为了检查它是否与我们使用密钥和请求有效载荷生成的哈希相同。如果不是，则拒绝请求
$signature = $_SERVER['HTTP_MONNIFY_SIGNATURE']; // monnify签名作为标头发送到您的webhook端点，我们获取值并存储在该变量中
$computedHash = hash_hmac('sha512', $raw_request, $SECRET_KEY); // 哈希生成
if( $computedHash != $signature) die("invalid Hash");


echo "OK";

// 解析请求到数组
$request_array = json_decode( $raw_request );


// 不要忘记检查重复通知：当收到新通知时，在给出值bfore之前，请务必检查该通知是否尚未处理。您可以通过在处理后使用自己的引用和其他引用跟踪所有通知并更新状态来实现这一点

//在此处处理您的业务逻辑。。。赋予用户价值。。这就是全部！



 ```
 
 
## Development

Want to contribute? Great! Send a a mail to integration-support@monnify.com

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
