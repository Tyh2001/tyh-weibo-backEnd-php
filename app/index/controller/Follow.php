<?php

namespace app\index\controller;

use think\Db;
use think\Request;

header('Access-Control-Allow-Origin:*');

// 关注相关
class Follow
{
  // 关注用户
  public function onFollowUser(Request $request)
  {
    $data = $request->param();

    $followsUser = Db::table('user_relations')->select();

    foreach ($followsUser as $key => $item) {
      if (strval($item['user_id']) === strval($data['user_id']) && strval($item['follower_id']) === strval($data['follower_id'])) {
        $result = array('code' => 401, 'msg' => '不能重复关注');
        return json($result);
      }
    }

    $userData = [
      'user_id' => $data['user_id'], // 用户 id
      'follower_id' => $data['follower_id'], // 关注用户的 id
      'created_at' => time() * 1000, // 关注时间
      'follower_nickname' => Db::table('user')->where('id', $data['follower_id'])->select()[0]['nickname'], // 关注用户的昵称
      'follower_avatar' => Db::table('user')->where('id', $data['follower_id'])->select()[0]['avatar'], // 关注用户的头像
      'user_nickname' => Db::table('user')->where('id', $data['user_id'])->select()[0]['nickname'], // 关注用户的头像
      'user_avatar' => Db::table('user')->where('id', $data['user_id'])->select()[0]['avatar'], // 关注用户的头像
    ];
    Db::table('user_relations')->insert($userData);

    $result = array('code' => 201, 'msg' => '关注成功');
    return json($result);
  }

  // 取消关注
  public function deleteFollowUser(Request $request)
  {
    $data = $request->param();

    $followsUser = Db::table('user_relations')->select();

    foreach ($followsUser as $key => $item) {
      if (strval($item['user_id']) === strval($data['user_id']) && strval($item['follower_id']) === strval($data['follower_id'])) {
        Db::table('user_relations')->where('follower_id', $data['follower_id'])->delete();
        $result = array('code' => 201, 'msg' => '取消关注成功');
        return json($result);
      }
    }
    $result = array('code' => 401, 'msg' => '取消关注失败');
    return json($result);
  }

  // 获取我的关注列表
  public function getFollowUserList(Request $request)
  {
    $data = $request->param();

    // 获取到已关注的用户
    $followUserList = Db::table('user_relations')->where('user_id', $data['user_id'])->select();

    $result = array('code' => 201, 'msg' => 'ok', 'data' => $followUserList);

    return json($result);
  }

  // 获取我的粉丝列表
  public function getFansUserList(Request $request)
  {
    $data = $request->param();

    $fansUserList = Db::table('user_relations')->where('follower_id', $data['user_id'])->select();

    $result = array('code' => 201, 'msg' => 'ok', 'data' => $fansUserList);

    return json($result);
  }
}
