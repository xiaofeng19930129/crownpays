<?php

namespace app\api\controller;

use app\common\controller\Api;
use app\common\library\Ems;
use app\common\library\Sms;
use fast\Random;
use think\Config;
use think\Validate;

/**
 * 会员接口
 */
class User extends Api
{
    protected $noNeedLogin = ['login', 'mobilelogin', 'register', 'resetpwd', 'changeemail', 'changemobile', 'third'];
    protected $noNeedRight = '*';

    public function _initialize()
    {
        parent::_initialize();

        if (!Config::get('fastadmin.usercenter')) {
            $this->error(__('User center already closed'));
        }

    }

    /**
     * 会员中心
     */
    public function index()
    {
        $this->success('', ['welcome' => $this->auth->nickname]);
    }

    /**
     * 会员登录
     *
     * @ApiMethod (POST)
     * @param string $account  账号
     * @param string $password 密码
     */
    public function login()
    {
        $account = $this->request->post('account');
        $password = $this->request->post('password');
        if (!$account || !$password) {
            $this->error(__('Invalid parameters'));
        }
        $ret = $this->auth->login($account, $password);
        if ($ret) {
            $data = $this->auth->getUserinfo();
            $this->success(__('Logged in successful'), $data);
        } else {
            $this->error($this->auth->getError());
        }
    }

    public function userinfo(){
        $data = $this->auth->getUserinfo();
        $this->success(__('Success'), $data);
    }


    /**
     * 手机验证码登录
     *
     * @ApiMethod (POST)
     * @param string $mobile  手机号
     * @param string $captcha 验证码
     */
    public function mobilelogin()
    {
        $mobile = $this->request->post('mobile');
        $captcha = $this->request->post('captcha');
        if (!$mobile || !$captcha) {
            $this->error(__('Invalid parameters'));
        }
        if (!Validate::regex($mobile, "^1\d{10}$")) {
            $this->error(__('Mobile is incorrect'));
        }
        if (!Sms::check($mobile, $captcha, 'mobilelogin')) {
            $this->error(__('Captcha is incorrect'));
        }
        $user = \app\common\model\User::getByMobile($mobile);
        if ($user) {
            if ($user->status != 'normal') {
                $this->error(__('Account is locked'));
            }
            //如果已经有账号则直接登录
            $ret = $this->auth->direct($user->id);
        } else {
            $ret = $this->auth->register($mobile, Random::alnum(), '', $mobile, []);
        }
        if ($ret) {
            Sms::flush($mobile, 'mobilelogin');
            $data = ['userinfo' => $this->auth->getUserinfo()];
            $this->success(__('Logged in successful'), $data);
        } else {
            $this->error($this->auth->getError());
        }
    }

    /**
     * 添加子账号
     */
    public function subAccount(){
        $username = $this->request->post('username');
        $password = $this->request->post('password');
        if (!$username || !$password) {
            $this->error(__('error'),[],201);
        }
        $ret = $this->auth->register($username, $password, '', '', ['fid'=>$this->auth->id]);
        if ($ret) {
            $this->success(__('success'));
        } else {
            $this->error($this->auth->getError(),[],$this->auth->getErrorCode());
        }
    }

    /**
     * 子账号列表
     */
    public function subAccountList(){
        $userModel = new \app\common\model\User();
        $list = $userModel->getSubAccountList($this->auth->id);
        $this->success(__('success'),$list);
    }

    /**
     * 设置汇率差
     */
    public function subAccountExchangeDiff(){
        $id = $this->request->get('id');
        $exchange_diff = $this->request->get('exchange_diff');
        $userModel = new \app\common\model\User();
        $result = $userModel->setSubAccountExchangeDiff($this->auth->id,$id,$exchange_diff);
        if(!$result){
            $this->error(__('error'),null,$userModel->getErrorCode());
        }
        $this->success(__('success'));
    }

    /**
     * 安全升级------改写这个了
     */
    public function security(){
        $id = $this->request->get('id');
        $userModel = new \app\common\model\User();
        $result = $userModel->security($this->auth->id);
        if(!$result){
            $this->error(__('error'),null,$userModel->getErrorCode());
        }
        $this->success(__('success'));
    }

    /**
     * 注册会员
     *
     * @ApiMethod (POST)
     * @param string $username 用户名
     * @param string $password 密码
     * @param string $email    邮箱
     * @param string $mobile   手机号
     * @param string $code     验证码
     */
    public function register()
    {
        $username = $this->request->post('username');
        $password = $this->request->post('password');
        // $email = $this->request->post('email');
        // $mobile = $this->request->post('mobile');
        $code = $this->request->post('code');
        $sms_code = $this->request->post('sms_code');
        if (!$username || !$password) {
            $this->error(__('error'),[],201);
        }
        // if ($username && !Validate::is($username, "email")) {
        //     $this->error(__('Email is incorrect'));
        // }
        // if ($mobile && !Validate::regex($mobile, "^1\d{10}$")) {
        //     $this->error(__('Mobile is incorrect'));
        // }
        $ret = Ems::check($username, $sms_code, 'register');
        if (!$ret) {
            $this->error(__('Captcha is incorrect'));
        }

        $userModel = new \app\common\model\User();
        $userCode = $userModel->getCode();

        $pid = 0;
        if(!empty($code)){
            if(!$pid = $userModel->checkCode($code)){
                $this->error(__('error'),[],203);
            }
        }

        $ret = $this->auth->register($username, $password, $username, '', ['code' => $userCode,'pid'=>$pid]);
        if ($ret) {
            $data = $this->auth->getUserinfo();
            $this->success(__('success'), $data);
        } else {
            $this->error($this->auth->getError(),[],$this->auth->getErrorCode());
        }
    }

    /**
     * 退出登录
     * @ApiMethod (POST)
     */
    public function logout()
    {
        if (!$this->request->isPost()) {
            $this->error(__('Invalid parameters'));
        }
        $this->auth->logout();
        $this->success(__('Logout successful'));
    }

    /**
     * 修改会员个人信息
     *
     * @ApiMethod (POST)
     * @param string $avatar   头像地址
     * @param string $username 用户名
     * @param string $nickname 昵称
     * @param string $bio      个人简介
     */
    public function profile()
    {
        $user = $this->auth->getUser();
        $username = $this->request->post('username');
        $nickname = $this->request->post('nickname');
        $bio = $this->request->post('bio');
        $avatar = $this->request->post('avatar', '', 'trim,strip_tags,htmlspecialchars');
        if ($username) {
            $exists = \app\common\model\User::where('username', $username)->where('id', '<>', $this->auth->id)->find();
            if ($exists) {
                $this->error(__('Username already exists'));
            }
            $user->username = $username;
        }
        if ($nickname) {
            $exists = \app\common\model\User::where('nickname', $nickname)->where('id', '<>', $this->auth->id)->find();
            if ($exists) {
                $this->error(__('Nickname already exists'));
            }
            $user->nickname = $nickname;
        }
        $user->bio = $bio;
        $user->avatar = $avatar;
        $user->save();
        $this->success();
    }

    /**
     * 修改邮箱
     *
     * @ApiMethod (POST)
     * @param string $email   邮箱
     * @param string $captcha 验证码
     */
    public function changeemail()
    {
        $user = $this->auth->getUser();
        $email = $this->request->post('email');
        $captcha = $this->request->post('captcha');
        if (!$email || !$captcha) {
            $this->error(__('Invalid parameters'));
        }
        if (!Validate::is($email, "email")) {
            $this->error(__('Email is incorrect'));
        }
        if (\app\common\model\User::where('email', $email)->where('id', '<>', $user->id)->find()) {
            $this->error(__('Email already exists'));
        }
        $result = Ems::check($email, $captcha, 'changeemail');
        if (!$result) {
            $this->error(__('Captcha is incorrect'));
        }
        $verification = $user->verification;
        $verification->email = 1;
        $user->verification = $verification;
        $user->email = $email;
        $user->save();

        Ems::flush($email, 'changeemail');
        $this->success();
    }

    /**
     * 修改手机号
     *
     * @ApiMethod (POST)
     * @param string $mobile  手机号
     * @param string $captcha 验证码
     */
    public function changemobile()
    {
        $user = $this->auth->getUser();
        $mobile = $this->request->post('mobile');
        $captcha = $this->request->post('captcha');
        if (!$mobile || !$captcha) {
            $this->error(__('Invalid parameters'));
        }
        if (!Validate::regex($mobile, "^1\d{10}$")) {
            $this->error(__('Mobile is incorrect'));
        }
        if (\app\common\model\User::where('mobile', $mobile)->where('id', '<>', $user->id)->find()) {
            $this->error(__('Mobile already exists'));
        }
        $result = Sms::check($mobile, $captcha, 'changemobile');
        if (!$result) {
            $this->error(__('Captcha is incorrect'));
        }
        $verification = $user->verification;
        $verification->mobile = 1;
        $user->verification = $verification;
        $user->mobile = $mobile;
        $user->save();

        Sms::flush($mobile, 'changemobile');
        $this->success();
    }

    /**
     * 第三方登录
     *
     * @ApiMethod (POST)
     * @param string $platform 平台名称
     * @param string $code     Code码
     */
    public function third()
    {
        $url = url('user/index');
        $platform = $this->request->post("platform");
        $code = $this->request->post("code");
        $config = get_addon_config('third');
        if (!$config || !isset($config[$platform])) {
            $this->error(__('Invalid parameters'));
        }
        $app = new \addons\third\library\Application($config);
        //通过code换access_token和绑定会员
        $result = $app->{$platform}->getUserInfo(['code' => $code]);
        if ($result) {
            $loginret = \addons\third\library\Service::connect($platform, $result);
            if ($loginret) {
                $data = [
                    'userinfo'  => $this->auth->getUserinfo(),
                    'thirdinfo' => $result
                ];
                $this->success(__('Logged in successful'), $data);
            }
        }
        $this->error(__('Operation failed'), $url);
    }

    /**
     * 重置密码
     *
     * @ApiMethod (POST)
     * @param string $mobile      手机号
     * @param string $newpassword 新密码
     * @param string $captcha     验证码
     */
    public function resetpwd()
    {
        // $oldpassword = $this->request->post("oldpassword", '', null);
        // $newpassword = $this->request->post("newpassword", '', null);
        // $renewpassword = $this->request->post("renewpassword", '', null);
        // $token = $this->request->post('__token__');
        // $rule = [
        //     'oldpassword'   => 'require|regex:\S{6,30}',
        //     'newpassword'   => 'require|regex:\S{6,30}',
        //     'renewpassword' => 'require|regex:\S{6,30}|confirm:newpassword',
        //     '__token__'     => 'token',
        // ];

        // $msg = [
        //     'renewpassword.confirm' => __('Password and confirm password don\'t match')
        // ];
        // $data = [
        //     'oldpassword'   => $oldpassword,
        //     'newpassword'   => $newpassword,
        //     'renewpassword' => $renewpassword,
        //     '__token__'     => $token,
        // ];
        // $field = [
        //     'oldpassword'   => __('Old password'),
        //     'newpassword'   => __('New password'),
        //     'renewpassword' => __('Renew password')
        // ];
        // $validate = new Validate($rule, $msg, $field);
        // $result = $validate->check($data);
        // if (!$result) {
        //     $this->error(__($validate->getError()), null, ['token' => $this->request->token()]);
        // }

        // $ret = $this->auth->changepwd($newpassword, $oldpassword);
        // if ($ret) {
        //     $this->success(__('Reset password successful'), url('user/login'));
        // } else {
        //     $this->error($this->auth->getError(), null, ['token' => $this->request->token()]);
        // }
        // $type = $this->request->post("type", "mobile");
        // $mobile = $this->request->post("mobile");
        // $email = $this->request->post("email");
        $oldpassword = $this->request->post("oldpassword", '', null);
        $newpassword = $this->request->post("newpassword");
        // $captcha = $this->request->post("captcha");
        if (!$newpassword || !$oldpassword) {
            $this->error(__('error'),[],201);
        }
        // //验证Token
        if (!Validate::make()->check(['newpassword' => $newpassword], ['newpassword' => 'require|regex:\S{6,30}'])) {
            $this->error(__('Password must be 6 to 30 characters'),[],202);
        }
        // if ($type == 'mobile') {
        //     if (!Validate::regex($mobile, "^1\d{10}$")) {
        //         $this->error(__('Mobile is incorrect'));
        //     }
        //     $user = \app\common\model\User::getByMobile($mobile);
        //     if (!$user) {
        //         $this->error(__('User not found'));
        //     }
        //     $ret = Sms::check($mobile, $captcha, 'resetpwd');
        //     if (!$ret) {
        //         $this->error(__('Captcha is incorrect'));
        //     }
        //     Sms::flush($mobile, 'resetpwd');
        // } else {
        //     if (!Validate::is($email, "email")) {
        //         $this->error(__('Email is incorrect'));
        //     }
        //     $user = \app\common\model\User::getByEmail($email);
        //     if (!$user) {
        //         $this->error(__('User not found'));
        //     }
        //     $ret = Ems::check($email, $captcha, 'resetpwd');
        //     if (!$ret) {
        //         $this->error(__('Captcha is incorrect'));
        //     }
        //     Ems::flush($email, 'resetpwd');
        // }
        
        // //模拟一次登录
        $this->auth->direct($this->auth->id);
        $ret = $this->auth->changepwd($newpassword, $oldpassword);
        if ($ret) {
            $this->success(__('success'));
        } else {
            $this->error(__('error'),[],$this->auth->getErrorCode());
        }
    }
}
