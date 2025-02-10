<?php

namespace app\api\controller;

use app\common\controller\Api;
use app\common\exception\UploadException;
use app\common\library\Upload;
use app\common\model\Area;
use app\common\model\Version;
use fast\Random;
use think\captcha\Captcha;
use think\Config;
use think\Hook;
// 使用 Composer 来包含 Google Cloud 依赖项
use Google\Cloud\RecaptchaEnterprise\V1\RecaptchaEnterpriseServiceClient;
use Google\Cloud\RecaptchaEnterprise\V1\Event;
use Google\Cloud\RecaptchaEnterprise\V1\Assessment;
use Google\Cloud\RecaptchaEnterprise\V1\TokenProperties\InvalidReason;

/**
 * 公共接口
 */
class Common extends Api
{
    protected $noNeedLogin = ['init', 'captcha','config','siteverify'];
    protected $noNeedRight = '*';

    public function _initialize()
    {

        if (isset($_SERVER['HTTP_ORIGIN'])) {
            header('Access-Control-Expose-Headers: __token__');//跨域让客户端获取到
        }
        //跨域检测
        check_cors_request();

        if (!isset($_COOKIE['PHPSESSID'])) {
            Config::set('session.id', $this->request->server("HTTP_SID"));
        }
        parent::_initialize();
    }

    /**
     * 加载初始化
     *
     * @param string $version 版本号
     * @param string $lng 经度
     * @param string $lat 纬度
     */
    public function init()
    {
        if ($version = $this->request->request('version')) {
            $lng = $this->request->request('lng');
            $lat = $this->request->request('lat');

            //配置信息
            $upload = Config::get('upload');
            //如果非服务端中转模式需要修改为中转
            if ($upload['storage'] != 'local' && isset($upload['uploadmode']) && $upload['uploadmode'] != 'server') {
                //临时修改上传模式为服务端中转
                set_addon_config($upload['storage'], ["uploadmode" => "server"], false);

                $upload = \app\common\model\Config::upload();
                // 上传信息配置后
                Hook::listen("upload_config_init", $upload);

                $upload = Config::set('upload', array_merge(Config::get('upload'), $upload));
            }

            $upload['cdnurl'] = $upload['cdnurl'] ? $upload['cdnurl'] : cdnurl('', true);
            $upload['uploadurl'] = preg_match("/^((?:[a-z]+:)?\/\/)(.*)/i", $upload['uploadurl']) ? $upload['uploadurl'] : url($upload['storage'] == 'local' ? '/api/common/upload' : $upload['uploadurl'], '', false, true);

            $content = [
                'citydata'    => Area::getCityFromLngLat($lng, $lat),
                'versiondata' => Version::check($version),
                'uploaddata'  => $upload,
                'coverdata'   => Config::get("cover"),
            ];
            $this->success('', $content);
        } else {
            $this->error(__('Invalid parameters'));
        }
    }

    /**
     * 上传文件
     * @ApiMethod (POST)
     * @param File $file 文件流
     */
    public function upload()
    {
        Config::set('default_return_type', 'json');
        //必须设定cdnurl为空,否则cdnurl函数计算错误
        Config::set('upload.cdnurl', '');
        $chunkid = $this->request->post("chunkid");
        if ($chunkid) {
            if (!Config::get('upload.chunking')) {
                $this->error(__('Chunk file disabled'));
            }
            $action = $this->request->post("action");
            $chunkindex = $this->request->post("chunkindex/d");
            $chunkcount = $this->request->post("chunkcount/d");
            $filename = $this->request->post("filename");
            $method = $this->request->method(true);
            if ($action == 'merge') {
                $attachment = null;
                //合并分片文件
                try {
                    $upload = new Upload();
                    $attachment = $upload->merge($chunkid, $chunkcount, $filename);
                } catch (UploadException $e) {
                    $this->error($e->getMessage());
                }
                $this->success(__('Uploaded successful'), ['url' => $attachment->url, 'fullurl' => cdnurl($attachment->url, true)]);
            } elseif ($method == 'clean') {
                //删除冗余的分片文件
                try {
                    $upload = new Upload();
                    $upload->clean($chunkid);
                } catch (UploadException $e) {
                    $this->error($e->getMessage());
                }
                $this->success();
            } else {
                //上传分片文件
                //默认普通上传文件
                $file = $this->request->file('file');
                try {
                    $upload = new Upload($file);
                    $upload->chunk($chunkid, $chunkindex, $chunkcount);
                } catch (UploadException $e) {
                    $this->error($e->getMessage());
                }
                $this->success();
            }
        } else {
            $attachment = null;
            //默认普通上传文件
            $file = $this->request->file('file');
            try {
                $upload = new Upload($file);
                $attachment = $upload->upload();
            } catch (UploadException $e) {
                $this->error($e->getMessage());
            } catch (\Exception $e) {
                $this->error($e->getMessage());
            }

            $this->success(__('Uploaded successful'), ['url' => $attachment->url, 'fullurl' => cdnurl($attachment->url, true)]);
        }

    }

    /**
     * 验证码
     * @param $id
     * @return \think\Response
     */
    public function captcha($id = "")
    {
        \think\Config::set([
            'captcha' => array_merge(config('captcha'), [
                'fontSize' => 44,
                'imageH'   => 150,
                'imageW'   => 350,
            ])
        ]);
        $captcha = new Captcha((array)Config::get('captcha'));
        return $captcha->entry($id);
    }

    public function siteverify(){
        // 你的Secret Key
        $secretKey = "6LffhL4qAAAAAGoCMlLnn11m0o9naPjj6HJffsjN";

        // 获取来自客户端的reCAPTCHA响应
        $response = $this->request->post('g_recaptcha_response');
        

        // $this->create_assessment($secretKey,$response,'my-project-9337-1737369712420','crownpays');

        // Google验证URL
        $verifyUrl = 'https://www.google.com/recaptcha/api/siteverify';

        $params = [
            'secret' => $secretKey,
            'response' => $response,
            'remoteip' => $_SERVER['REMOTE_ADDR']
        ];
        $responseData = \fast\Http::post($verifyUrl,$params);
        // 解析JSON响应
        $recaptchaResponse = json_decode($responseData, true);
        $this->success(__('success'),$responseData);
    }

    public function create_assessment(string $recaptchaKey,string $token,string $project,string $action)
    {
        // 创建 reCAPTCHA 客户端。
        // 待办：在退出方法前，对客户端生成代码进行缓存（推荐）或调用 client.close()。
        $client = new RecaptchaEnterpriseServiceClient();
        $projectName = $client->projectName($project);
      
        // 设置要跟踪的事件的属性。
        $event = (new Event)
          ->setSiteKey($recaptchaKey)
          ->setToken($token);
      
        // 构建评估请求。
        $assessment = (new Assessment)->setEvent($event);
      
        try {
          $response = $client->createAssessment(
            $projectName,
            $assessment
          );
      
          // 检查令牌是否有效。
          if ($response->getTokenProperties()->getValid() == false) {
            printf('The CreateAssessment() call failed because the token was invalid for the following reason: ');
            printf(InvalidReason::name($response->getTokenProperties()->getInvalidReason()));
            return;
          }
      
          // 检查是否执行了预期操作。
          if ($response->getTokenProperties()->getAction() == $action) {
            // 获取风险得分和原因。
            // 如需详细了解如何解读评估，请参阅：
            // https://cloud.google.com/recaptcha-enterprise/docs/interpret-assessment
            printf('The score for the protection action is:');
            printf($response->getRiskAnalysis()->getScore());
          } else {
            printf('The action attribute in your reCAPTCHA tag does not match the action you are expecting to score');
          }
        } catch (exception $e) {
          printf('CreateAssessment() call failed with the following error: ');
          printf($e);
        }
    }
    public function config(){
        $data = [
            'expense_one_fixed'=>config('site.expense_one_fixed'),
            'expense_one_permillage'=>config('site.expense_one_permillage'),
            'expense_two'=>config('site.expense_two'),
            'service_email'=>config('site.service_email'),
            'service_mobile'=>config('site.service_mobile'),
            'service_qrcde'=> request()->domain() . config('site.service_qrcde'),
            'security' => 200,
        ];
        $this->success('success',$data);
    }

}
