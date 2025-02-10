# Monnify验证银行账户✨
 
这允许您检查帐号是否是有效的NUBAN，并确认帐户名是否有效。


> 注：
>*请注意，这仅用于演示目的，而不是生产代码*
 

## 要求

生成客户预留账户的步骤。

-您需要进行身份验证才能获得不记名令牌。-请参阅指南[示例代码/身份验证/README.md][l1]
-您可以从Monnify仪表板中获取您的客户ID和密码。如果您还没有，请单击[此处](https://monnify.com/)注册Monnify


## 储备账户申请（为所有可用的合作伙伴银行分别获取一个账户）

如果您只想为您的客户保留首选合作伙伴银行的账户，则需要为“getAllAvailableBanks”传递“False”，并以数组形式提供首选银行的银行代码。

```php
<?php

$access_token = "<access_token>"; // add you access token here
$accountNumber = "0068687503"; // 
$bankCode = "232"; // 


$handler = curl_init();
$headers[] = 'Authorization: bearer '.$access_token;
$headers[] = 'Content-Type: application/json';


curl_setopt($handler, CURLOPT_URL, "https://sandbox.monnify.com/api/v1/disbursements/account/validate?accountNumber=$accountNumber&bankCode=$bankCode");
curl_setopt($handler, CURLOPT_HTTPHEADER,$headers);
curl_setopt($handler, CURLOPT_RETURNTRANSFER, true);


$response = curl_exec($handler); 

if($response !==false)
{
  var_dump($response); // 您可以解析此响应以获取响应正文
}
else {
  print "Could not get a response";
}


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
