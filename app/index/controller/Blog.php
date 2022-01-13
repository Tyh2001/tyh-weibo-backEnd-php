<?php

namespace app\index\controller;

use think\Db;
use think\Request;

header('Access-Control-Allow-Origin:*');

// 博客相关
class Blog
{
  // 发布博客
  public function releaseBlog(Request $request)
  {
    $files = request()->file('blogImages'); // 获取用户上传的的图片文件
    $blogText = $request->get('blogText'); // 获取用户发布的文字内容
    $userId = $request->get('userId'); // 获取用户的 id

    $blogImg = array(); // 存放上传图片的数组

    // 如果传递的图片存在 则执行循环操作
    if ($files) {
      foreach ($files as $file) {
        // 移动到框架应用根目录/public/blogImg/ 目录下
        $info = $file->move(ROOT_PATH . 'public' . DS . 'blogImg');
        if ($info) {
          // 将图片路径添加到数组中
          array_push($blogImg, $info->getSaveName());
        } else {
          echo $file->getError(); // 上传失败获取错误信息
        }
      }
    }

    $data = [
      'user_id' => $userId,
      'image' => json_encode($blogImg),
      'text' => $blogText,
      'release_time' => time() * 1000
    ];
    Db::table('blogs')->insert($data);

    $result = array('code' => 201, 'msg' => '发布成功');
    return json($result);
  }

  // 获取所有的博客内容
  public function getAllBlogList()
  {
    $allBlogList = Db::table('blogs')->select();

    $blogList = array();
    foreach ($allBlogList as $key => $item) {
      $itemBlog = array(
        'blogId' => $item['id'],
        'user_id' => $item['user_id'],
        'nickname' => Db::table('user')->where('id', $item['user_id'])->select()[0]['nickname'], // 昵称
        'avatar' => Db::table('user')->where('id', $item['user_id'])->select()[0]['avatar'], // 头像
        'release_time' => $item['release_time'], // 发布时间
        'text' => $item['text'], // 发布文字内容
        'image' => json_decode($item['image']), // 发布的图片路径参数
      );

      array_push($blogList, $itemBlog);
    }

    $result = array(
      'code' => 201,
      'msg' => 'ok',
      'data' => array_reverse($blogList)
    );
    return json($result);
  }

  // 获取指定用户的博客的内容
  public function getUserBlogList(Request $request)
  {
    $userId = $request->get('userId'); // 获取用户的 id

    // 获取到用户发布的所有博客内容
    $userBlog =  Db::table('blogs')->where('user_id', $userId)->select();

    $userInfo = Db::table('user')->where('id', $userId)->select(); // 获取用户信息

    $blogList = array();
    foreach ($userBlog as $key => $item) {
      $itemBlog = array(
        'blogId' => $item['id'],
        'user_id' => $item['user_id'],
        'nickname' => $userInfo[0]['nickname'], // 昵称
        'avatar' => $userInfo[0]['avatar'], // 头像
        'release_time' => $item['release_time'], // 发布时间
        'text' => $item['text'], // 发布文字内容
        'image' => json_decode($item['image']), // 发布的图片路径参数
      );

      array_push($blogList, $itemBlog);
    }

    $result = array(
      'code' => 201,
      'msg' => 'ok',
      'data' => $blogList
    );
    return json($result);
  }

  // 删除自己的指定博客内容
  public function deleteMyBlog(Request $request)
  {
    $blogId = $request->get('blogId');

    Db::table('blogs')->where('id', $blogId)->delete();

    $result = array('code' => 201, 'msg' => '删除成功');
    return json($result);
  }

  // 获取关注用户的博客
  public function getFollowAllBlogList(Request $request)
  {
    $userId = $request->get('userId'); // 获取用户的 id

    // 用户所有关注人的 id
    $followUser = array();

    // 获取到用户关注的所有人的 id
    $userBlog =  Db::table('user_relations')->where('user_id', $userId)->select();

    foreach ($userBlog as $key => $item) {
      array_push($followUser, $item['follower_id']);
    }

    // 获取到所有的博客内容
    $allBlogList = Db::table('blogs')->select();

    /**
     * 我关注人的博客内容
     */
    $followBlogList = array();
    foreach ($followUser as $key1 => $item) {
      foreach ($allBlogList as $key2 => $item) {
        if ($item['user_id'] === $followUser[$key1]) {
          array_push($followBlogList, $item);
        }
      }
    }

    $blogList = array();
    foreach ($followBlogList as $key => $item) {
      $itemBlog = array(
        'blogId' => $item['id'],
        'user_id' => $item['user_id'],
        'nickname' => Db::table('user')->where('id', $item['user_id'])->select()[0]['nickname'], // 昵称
        'avatar' => Db::table('user')->where('id', $item['user_id'])->select()[0]['avatar'], // 头像
        'release_time' => $item['release_time'], // 发布时间
        'text' => $item['text'], // 发布文字内容
        'image' => json_decode($item['image']), // 发布的图片路径参数
      );

      array_push($blogList, $itemBlog);
    }

    $result = array(
      'code' => 201,
      'msg' => 'ok',
      'data' => array_reverse($blogList)
    );
    return json($result);
  }
}
