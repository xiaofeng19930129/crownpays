# 银行转账✨
 
要启动单次传输，您需要向以下端点发送请求：

>注：
>*请注意，这仅用于演示目的，而不是生产代码*
 
##要求

生成客户预留账户的步骤。

-您需要进行身份验证才能获得不记名令牌。-请参阅指南[示例代码/身份验证/README.md][l1]
-您可以从Monnify仪表板中获取您的客户ID和密码。如果您还没有，请单击[此处](https://monnify.com/)注册Monnify

##启动转账（单笔）

以下是发起单次转账的示例请求：



```php
<?php

$access_token = "<access_token>"; // 在此处添加您的访问令牌

$postData = array(    
    "amount" => 20,
    "reference" =>"ben9-jlo00hdhdjjdfjoji", // 唯一交易参考
    "narration" =>"Test01",
    "destinationBankCode" => "057",
    "destinationAccountNumber" => "2085096393",
    "currency" => "NGN",
    "sourceAccountNumber" => "8016472829",
    "destinationAccountName" => "Marvelous Benji" 
);

$handler = curl_init();
$headers[] = 'Authorization: bearer '.$access_token;
$headers[] = 'Content-Type: application/json';


curl_setopt($handler, CURLOPT_URL, "https://sandbox.monnify.com/api/v2/disbursements/single");
curl_setopt($handler, CURLOPT_POSTFIELDS, http_build_query($postData));
curl_setopt($handler, CURLOPT_HTTPHEADER,$headers);
curl_setopt($handler, CURLOPT_POST, true);
 
$response = curl_exec($handler); 

if($response !==false)
{
  var_dump($response); // 您可以解析此响应以获取响应正文
}
else {
  print "Could not get a response";
}


 ```
 
  

##启动转移（批量）

批量转账允许您发送一个请求，其中包含要处理的支出列表。以下是发起批量转账的示例请求

```php
<?php

$access_token = "<access_token>"; // add you access token here

$postData = array(     
     "title"  => "Game of Batches",
    "batchReference" =>"batchreference12934",
    "narration" =>"911 Transaction",
    "sourceAccountNumber" => "9624937372",
    "onValidationFailure"  => "CONTINUE",
    "notificationInterval" => 10,
    "transactionList"  => [
    	[
	    	"amount" => 1300,
	    	"reference" =>"Final-Reference-1a",
	    	"narration" =>"911 Transaction",
	    	"destinationBankCode" => "058",
			"destinationAccountName" => "Benjamin Wilson",
	    	"destinationAccountNumber" => "0111946768",
	    	"currency" => "NGN"
    	],
		[
    		"amount" => 570,
	    	"reference" =>"Final-Reference-2a",
	    	"narration" =>"911 Transaction",
	    	"destinationBankCode" => "058",
			"destinationAccountName" => "Benjamin Wilson",
	    	"destinationAccountNumber" => "0111946768",
	    	"currency" => "NGN"
    	],
		[
    		"amount" => 230,
	    	"reference" =>"Final-Reference-3a",
	    	"narration" =>"911 Transaction",
			"destinationAccountName" => "Benjamin Wilson",
	    	"destinationBankCode" => "058",
	    	"destinationAccountNumber" => "0111946768",
	    	"currency" => "NGN"
    	]

   	]
);

$handler = curl_init();
$headers[] = 'Authorization: bearer '.$access_token;
$headers[] = 'Content-Type: application/json';


curl_setopt($handler, CURLOPT_URL, "https://sandbox.monnify.com/api/v2/disbursements/batch");
curl_setopt($handler, CURLOPT_POSTFIELDS, http_build_query($postData));
curl_setopt($handler, CURLOPT_HTTPHEADER,$headers);
curl_setopt($handler, CURLOPT_POST, true);
 
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
