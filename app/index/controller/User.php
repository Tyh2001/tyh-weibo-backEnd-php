<?php

namespace app\index\controller;

use think\Db;
use think\Request;

header('Access-Control-Allow-Origin:*');

// 用户相关
class User
{
  // 注册账号
  public function Register(Request $request)
  {
    // 获取到前端传递来的参数
    $data = $request->param();

    // 如果账号和密码有一个为空则直接返回错误信息
    if (
      $data['username'] === '' ||
      $data['password'] === '' ||
      $data['mail'] === '' ||
      $data['captcha'] === '' ||
      $data['captchaCode'] === ''
    ) {
      $result = array('code' => 402, 'msg' => '用户名信息不完善');
      return json($result);
    }

    // 随机生成用户名后缀编码
    function generate_password($length = 8)
    {
      // 密码字符集，可任意添加你需要的字符 
      $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
      $password = '';
      for ($i = 0; $i < $length; $i++) {
        $password .= $chars[mt_rand(0, strlen($chars) - 1)];
      }
      return $password;
    }

    // 先判定用户名是否存在，因为用户名是唯一的
    $user_list = Db::table('user')->select(); // 获取到用户数据库列表
    // return json($user_list);
    foreach ($user_list as $key => $item) {
      // 如果用户名已经存在则返回错误信息
      if ($item['username'] === $data['username']) {
        $result = array('code' => 401, 'msg' => '用户名已存在');
        return json($result);
      }
    }

    // 随机数对应的结果 检测验证码是否正确
    $captchaCodeList = array('35', '29', '29', '22', '33', '27', '15', '30', '18', '24', '20', '19', '28', '14', '32');
    if ($captchaCodeList[$data['captchaCode'] - 1] !== $data['captcha']) {
      $result = array('code' => 401, 'msg' => '验证码错误');
      return json($result);
    }

    // 数据库中不存在用户名则继续向数据库中添加用户数据
    $data = [
      'username' => $data['username'], // 用户名对应的用户名
      'password' => md5($data['password']), // 密码对应的密码
      'nickname' => '新用户' . generate_password(10), // 昵称是 新用户xxxx 加上随机字符
      'mail' => $data['mail'], // 昵称是 新用户xxxx 加上随机字符
      'avatar' => 'notLogin.jpg', // 注册之后添加默认头像
      'regis_time' => time() * 1000 // 记录注册时间 以为 php 和 js 精度不一样，所以 *1000 为前端 js 方便处理
    ];
    Db::table('user')->insert($data);

    $result = array('code' => 201, 'msg' => '注册成功');
    return json($result);
  }

  // 登录
  public function Login(Request $request)
  {
    // 获取到前端传递来的参数
    $data = $request->param();

    // 当有一个字段传入为空值时，直接返回
    if ($data['username'] === '' || $data['password'] === '') {
      $result = array('code' => 401, 'msg' => '用户信息不完善');
      return json($result);
    }

    // 获取到表的所有数据
    $user = Db::table('user')->select();

    foreach ($user as $key => $item) {
      if ($data['username'] === $item['username'] && md5($data['password']) === $item['password']) {

        // 获取到当前用户的信息
        $userInfo = Db::name('user')->where('username', $item['username'])->select();

        // 登录成功返回 code、msg、id、nickname
        $result = array(
          'code' => 201,
          'msg' => '登录成功',
          'data' => array(
            'id' => $userInfo[0]['id'],
            'nickname' => $userInfo[0]['nickname'],
            'photo' => 'http://localhost/Virgo_Tyh_PHP/public/userPhoto/' . $userInfo[0]['avatar']
          )
        );
        return json($result);
      }
    }
    $result = array('code' => 402, 'msg' => '账号或密码错误');
    return json($result);
  }

  // 获取用户资料
  public function getUserInfo(Request $request)
  {
    $data = $request->param();

    if (!$data) {
      $result = array('code' => 401, 'msg' => '缺少参数');
      return;
    }

    $userInfo = Db::table('user')->where('id', $data['id'])->select();

    // 获取我关注的人
    $myFollows = Db::table('user_relations')->where('user_id', $data['id'])->select();

    // 获取我的粉丝
    $myFans = Db::table('user_relations')->where('follower_id', $data['id'])->select();

    $result = array(
      'code' => 201,
      'msg' => 'OK',
      'data' => array(
        'avatar' => $userInfo[0]['avatar'], // 头像
        'nickname' => $userInfo[0]['nickname'], // 昵称
        'autograph' => $userInfo[0]['autograph'], // 个性签名
        'gender' => $userInfo[0]['gender'], // 性别
        'feeling' => $userInfo[0]['feeling'], // 感情状况
        'work' => $userInfo[0]['work'], // 职业
        'city' => $userInfo[0]['city'], // 城市
        'birthday' => $userInfo[0]['birthday'], // 生日
        'mail' => $userInfo[0]['mail'], // 邮箱
        'regis_time' => $userInfo[0]['regis_time'], // 注册时间
        'follow_list' => sizeof($myFollows), // 关注数量
        'fans_list' => sizeof($myFans), // 粉丝数量
      )
    );

    return json($result);
  }

  // 更新用户资料
  public function changeUserInfo(Request $request)
  {
    $data = $request->param();
    // 获取传入的用户 id
    $userID = $request->get('id');

    if (!$data) {
      $result = array('code' => 401, 'msg' => '信息不完善');
      return json($result);
    }

    Db::table('user')->where('id', number_format($userID))->update([
      'nickname' => $data['nickname'],
      'autograph' => $data['autograph'],
      'gender' => $data['gender'],
      'feeling' => $data['feeling'],
      'work' => $data['work'],
      'birthday' => $data['birthday'],
      'mail' => $data['mail'],
    ]);

    $result = array('code' => 201, 'msg' => '更新用户信息成功');
    return json($result);
  }

  // 更新用户密码
  public function changeUserPass(Request $request)
  {
    $data = $request->param();
    // 获取传入的用户 id
    $userID = $request->get('id');

    if (
      $data['oldPass'] === '' ||
      $data['newPass'] === '' ||
      $data['id'] === ''
    ) {
      $result = array('code' => 402, 'msg' => '参数不完善');
      return json($result);
    }

    // 查找到这个 id 的用户信息
    $userInfo = Db::table('user')->where('id', number_format($userID))->find();

    if ($userInfo['password'] !== md5($data['oldPass'])) {
      $result = array('code' => 401, 'msg' => '原始密码错误');
      return json($result);
    }

    // 更新密码
    Db::table('user')->where('id', number_format($userID))->update([
      'password' => md5($data['newPass']),
    ]);

    $result = array('code' => 201, 'msg' => '更新密码成功');
    return json($result);
  }

  // 更新用户头像
  public function uploadPhoto(Request $request)
  {
    // 获取表单上传文件 例如上传了001.jpg
    $file = request()->file('photo');
    // 获取传入的用户 id
    $userID = $request->get('id');

    // 移动到框架应用根目录 /public/userPhoto/ 目录下
    if ($file) {
      $info = $file->move(ROOT_PATH . 'public' . DS . 'userPhoto');
      if ($info) {
        Db::table('user')->where('id', number_format($userID))->update([
          'avatar' => $info->getSaveName(),
        ]);

        $result = array(
          'code' => 201,
          'msg' => '更新头像成功',
          'data' => array(
            'url' => $info->getSaveName()
          )
        );
        return json($result);
      }
      $result = array('code' => 401, 'msg' => '更新失败');
      return json($result);
    }
  }
}
