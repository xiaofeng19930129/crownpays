#Monnify客户预留账户✨
 
此存储库包含有关如何在[Monify]上为您的客户创建REserved帐户的示例代码(https://monnify.com/)在PHP中。
>注：
>*请注意，这仅用于演示目的，而不是生产代码*
 
##要求

生成客户预留账户的步骤。

-您需要进行身份验证才能获得不记名令牌。-请参阅指南[示例代码/身份验证/README.md][l1]
-您可以从Monnify仪表板中获取您的客户ID和密码。如果您还没有，请单击[此处](https://monnify.com/)注册Monnify


##储备账户申请（为所有可用的合作伙伴银行分别获取一个账户）

如果您只想为您的客户保留首选合作伙伴银行的账户，则需要为“getAllAvailableBanks”传递“False”，并以数组形式提供首选银行的银行代码。

```php
<?php


$access_token = "<access_token>"; //在此处添加您的访问令牌

$postData = json_encode(array(     
      "accountReference" => "mN84t584ffgg75t84758478754", // unique reference
           "accountName" => "Damilare Ogunnaike",
          "currencyCode" => "NGN",
          "contractCode" => "8389328412",
         "customerEmail" => "test@tester.com",
                   "bvn" => "2233445566778899",
          "customerName" => "Damilare Ogunnaike",
  "getAllAvailableBanks" => true
));

$handler = curl_init();
$headers[] = 'Authorization: bearer '.$access_token;
$headers[] = 'Content-Type: application/json';


curl_setopt($handler, CURLOPT_URL, "https://sandbox.monnify.com/api/v2/bank-transfer/reserved-accounts");
curl_setopt($handler, CURLOPT_POSTFIELDS, $postData);
curl_setopt($handler, CURLOPT_HTTPHEADER,$headers);
curl_setopt($handler, CURLOPT_POST, true);

$response = curl_exec($handler); 

if($response !==false)
{
  var_dump($response); // 您可以解析此响应以获取保留帐户详细信息
}
else {
  print "Could not get a response";
}


 ```
 ##储备账户申请（仅限首选合作伙伴银行的账户）

如果您只想为您的客户保留首选合作伙伴银行的账户，则需要为“getAllAvailableBanks”传递“False”，并以数组形式提供首选银行的银行代码。


```php
<?php


$access_token = "<access_token>"; // 在此处添加您的访问令牌

$postData = array(     
      "accountReference" => "mN84t584ffgg75t84758478754", // unique reference
           "accountName" => "Damilare Ogunnaike",
          "currencyCode" => "NGN",
          "contractCode" => "8389328412",
         "customerEmail" => "test@tester.com",
                   "bvn" => "2233445566778899",
          "customerName" => "Damilare Ogunnaike",
  "getAllAvailableBanks" => false,
        "preferredBanks" => ["035"]
);

$handler = curl_init();
$headers[] = 'Authorization: bearer '.$access_token;
$headers[] = 'Content-Type: application/json';


curl_setopt($handler, CURLOPT_URL, "https://sandbox.monnify.com/api/v2/bank-transfer/reserved-accounts");
curl_setopt($handler, CURLOPT_POSTFIELDS, http_build_query($postData));
curl_setopt($handler, CURLOPT_HTTPHEADER,$headers);
curl_setopt($handler, CURLOPT_POST, true);

$response = curl_exec($handler); 

if($response !==false)
{
  var_dump($response); // 您可以解析此响应以获取保留帐户详细信息
}
else {
  print "Could not get a response";
}

```
 
 
##在保留账户上拆分付款
incomeSplitConfig允许您通过指定一个或多个子账户以及将每笔付款的特定百分比记入每个子账户，对保留账户使用拆分付款。IncomeSplitConfig是一个对象数组，因此您可以在每笔交易中拆分为多个子账户。
使用子账户申请储备账户


```php
<?php


$access_token = "<access_token>"; // add you access token here

$postData = array(     
      "accountReference" => "mN84t584ffgg75t84758478754", // unique reference
           "accountName" => "Damilare Ogunnaike",
          "currencyCode" => "NGN",
          "contractCode" => "8389328412",
         "customerEmail" => "test@tester.com",
                   "bvn" => "2233445566778899",
          "customerName" => "Damilare Ogunnaike",
  "getAllAvailableBanks" => true,
     "incomeSplitConfig" => [
         {
           "subAccountCode" => "MFY_SUB_319452883228",
            "feePercentage" => 10.5,
          "splitPercentage" => 20,
                "feeBearer" => true
         }
      ]
);

$handler = curl_init();
$headers[] = 'Authorization: bearer '.$access_token;
$headers[] = 'Content-Type: application/json';


curl_setopt($handler, CURLOPT_URL, "https://sandbox.monnify.com/api/v2/bank-transfer/reserved-accounts");
curl_setopt($handler, CURLOPT_POSTFIELDS, http_build_query($postData));
curl_setopt($handler, CURLOPT_HTTPHEADER,$headers);
curl_setopt($handler, CURLOPT_POST, true);

$response = curl_exec($handler); 

if($response !==false)
{
  var_dump($response); // you can parse this response to get the reserved account details
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
