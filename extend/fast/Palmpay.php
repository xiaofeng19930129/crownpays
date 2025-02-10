<?php

namespace fast;

/**
 * Palmpay支付类
 */
class Palmpay
{
    public $publicKey = 'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAwePm8ZzPNgAMHx1H2hQnGUTunkMlj+9aJWVxGAwRTGjUfCtevxOr2CqcLtG9AfJadm5+/F158kYW9ZUnrA7Np0yMiURhMTMR2TZ6MIVriptgnMUX7UAqVBtcGd4IoWfj4gJtT4oKa2vsBoIrEp1qu+3JRuIut5R0SXVts37HWLU037Ola4rm4UsjC3PQUq2EeOkh8vr6wtfq2Ky82Tl3yde/pe858rdiO4/zEqflT/3jxbTtKuC1WIn3EuG7RjuXv4pqDvOg2I7NrjE3CVinVq2ap4BMxL5yYW3h/h2+s3EzkkhlUQcDljxchHvwIGTvzq9FAC4ULApOCqUAwzvxSwIDAQAB';
    public $privateKey = 'MIIEvAIBADANBgkqhkiG9w0BAQEFAASCBKYwggSiAgEAAoIBAQDB4+bxnM82AAwfHUfaFCcZRO6eQyWP71olZXEYDBFMaNR8K16/E6vYKpwu0b0B8lp2bn78XXnyRhb1lSesDs2nTIyJRGExMxHZNnowhWuKm2CcxRftQCpUG1wZ3gihZ+PiAm1Pigpra+wGgisSnWq77clG4i63lHRJdW2zfsdYtTTfs6VriubhSyMLc9BSrYR46SHy+vrC1+rYrLzZOXfJ17+l7znyt2I7j/MSp+VP/ePFtO0q4LVYifcS4btGO5e/imoO86DYjs2uMTcJWKdWrZqngEzEvnJhbeH+Hb6zcTOSSGVRBwOWPFyEe/AgZO/Or0UALhQsCk4KpQDDO/FLAgMBAAECggEAHM5H22buqhkrs9uydxs+C9UKlodnUeZqQDASs1M8/AXUZnY9vKmm2EDMX3BYYlen9Px0RU4l9fFP1pXzBO8AwsruqN4jNR9fQXmEgdJF/fEY/hw8x8ob/87oygxgv994IyCKg6IAlku8ZI3Q6b0VbxeO1ccjewbFQryoMF4KLorLPZglB6paEWuQ8lYxUdTmFGVgrb/m1bqkRMowLISZ2IGllqvIwfwU1WS2+4QDJ3hhOpj4N96mg7Qdeer4UAwl3WZtoGXTw/Fq9cPjlASUzXQn8aJoty45+KvbwwD6qvCj73BQPOystfA8XcImuwMWpqcjseJZNuXO0ojzcM41QQKBgQDfY8PJp42JyCG/f4jwsiu9pvhnQD7ZoRuaPvCpDN0x6wr4NXZjJNSw311pnNosrGlJbj2xgECUl5eZcbwzL5c+kMcOnjKKFj7kMAZMB+Yb2tZe8Yq93fE2rxb0T0qxI3+TrSOHQhWghSO2/Vc7wwMsOvqK3atwI9zcrKH6bvmFcQKBgQDeMbiTU4t0Ybz8C3nBWuWDwg52yBqk1MhozfB2IXUMXbe+H5z61LMQiOgDtq89UukEn/CYPwOpJjDbo5+3BRWH0daTglT4bHZ3zwDMH5MFSV6INaSn+k2q2syaCHHDKDHgrWpJqrFCvwEQJwE/5OFF9Xdr8w9qjyK5EPave6wUewKBgHKPThO3Kn2zNaNoOwj8xjO/UfpZVyHyXh5vEqoPH8x+tiRt/mV/uNdv5Q7JUpXAYUo4D8lcdUH+r88QJay2RkDerEaR2Gc6W0xYWJJbJqW4R2FvS3BtRkt95S6rDynig4VvMB4oRMEKJjOsnjTWoGTpZh0a6tVafuRoX7T3flzxAoGAfVrz1Tp3HOvfYRtgT7PmdNVQr7bpQUFC58338c2iaZ2eAkd3mxPRCm0S6LVyXFigWhmZ9Wrnt9ByFBhWDT1zDjNWqdYH9jfLBoDk5EST/5GLIbGR+ar/knrqn6RP+lh8+1Ma+gCxDQURpnk6/T810PBRtbdlZSrn3h0x74MIbG0CgYANunSfXqTFEsYxDQmiUQ6N26d45dtjBKM2h7IGKNvuSZtVJ7NeKyoJ4knDDAfGxLrqKfe+kenVCTVPLrXOHg3U5DLPsW/Q5/BjqzZSfgkYNJSpNDtdiC/ZX7yI5ver0V+bY/EjnEiFFZnZyWOy3EjPYH6nEINgojvPu6LJ7N9I/g==';
    private $_privKey;

    /**
     * * private key
     */
    private $_pubKey;

    /**
     * * public key
     */
    private $_keyPath;

    /**
     * * the keys saving path
     */

    /**
     * * the construtor,the param $path is the keys saving path
     * @param string $publicKey  公钥
     * @param string $privateKey 私钥
     */
    public function __construct($publicKey = null, $privateKey = null)
    {
        $this->setKey($publicKey, $privateKey);
    }

    /**
     * 设置公钥和私钥
     * @param string $publicKey  公钥
     * @param string $privateKey 私钥
     */
    public function setKey($publicKey = null, $privateKey = null)
    {
        if (!is_null($publicKey)) {
            $this->publicKey = $publicKey;
        }
        if (!is_null($privateKey)) {
            $this->privateKey = $privateKey;
        }
    }

    /**
     * * setup the private key
     */
    private function setupPrivKey()
    {
        if (is_resource($this->_privKey)) {
            return true;
        }
        $pem = chunk_split($this->privateKey, 64, "\n");
        $pem = "-----BEGIN PRIVATE KEY-----\n" . $pem . "-----END PRIVATE KEY-----\n";
        $this->_privKey = openssl_pkey_get_private($pem);
        return true;
    }

    /**
     * * setup the public key
     */
    private function setupPubKey()
    {
        if (is_resource($this->_pubKey)) {
            return true;
        }
        $pem = chunk_split($this->publicKey, 64, "\n");
        $pem = "-----BEGIN PUBLIC KEY-----\n" . $pem . "-----END PUBLIC KEY-----\n";
        $this->_pubKey = openssl_pkey_get_public($pem);
        return true;
    }

    /**
     * * encrypt with the private key
     */
    public function privEncrypt($data)
    {
        if (!is_string($data)) {
            return null;
        }
        $this->setupPrivKey();
        $r = openssl_private_encrypt($data, $encrypted, $this->_privKey);
        if ($r) {
            return base64_encode($encrypted);
        }
        return null;
    }

    /**
     * * decrypt with the private key
     */
    public function privDecrypt($encrypted)
    {
        if (!is_string($encrypted)) {
            return null;
        }
        $this->setupPrivKey();
        $encrypted = base64_decode($encrypted);
        $r = openssl_private_decrypt($encrypted, $decrypted, $this->_privKey);
        if ($r) {
            return $decrypted;
        }
        return null;
    }

    /**
     * * encrypt with public key
     */
    public function pubEncrypt($data)
    {
        if (!is_string($data)) {
            return null;
        }
        $this->setupPubKey();
        $r = openssl_public_encrypt($data, $encrypted, $this->_pubKey);
        if ($r) {
            return base64_encode($encrypted);
        }
        return null;
    }

    /**
     * * decrypt with the public key
     */
    public function pubDecrypt($crypted)
    {
        if (!is_string($crypted)) {
            return null;
        }
        $this->setupPubKey();
        $crypted = base64_decode($crypted);
        $r = openssl_public_decrypt($crypted, $decrypted, $this->_pubKey);
        if ($r) {
            return $decrypted;
        }
        return null;
    }

    /**
     * 构造签名
     * @param string $dataString 被签名数据
     * @return string
     */
    public function sign($dataString)
    {
        $this->setupPrivKey();
        $signature = false;
        openssl_sign($dataString, $signature, $this->_privKey);
        return base64_encode($signature);
    }

    /**
     * 验证签名
     * @param string $dataString 被签名数据
     * @param string $signString 已经签名的字符串
     * @return number 1签名正确 0签名错误
     */
    public function verify($dataString, $signString)
    {
        $this->setupPubKey();
        $signature = base64_decode($signString);
        $flg = openssl_verify($dataString, $signature, $this->_pubKey);
        return $flg;
    }

    public function __destruct()
    {
        is_resource($this->_privKey) && @openssl_free_key($this->_privKey);
        is_resource($this->_pubKey) && @openssl_free_key($this->_pubKey);
    }
}
