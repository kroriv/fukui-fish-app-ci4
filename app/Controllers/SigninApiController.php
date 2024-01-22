<?php namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\Database\Exceptions\DatabaseException;
use Config\Services;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\SignatureInvalidException;
use Firebase\JWT\ExpiredException;
use App\Models\UsersModel;
use App\Entities\UserEntity;

class SigninApiController extends ResourceController
{
  // Trait
  use ResponseTrait;
  
  /** @var String Format */
  protected $format = "json";
  
  public function AuthUser()
  {
    // フォームデータ取得
    $postData = (object)$this->request->getPost();
    // User取得
    $postUser = $postData->user;
    
    // ユーザー名取得
    $username = @$postUser["username"];
    // パスワード取得
    $passphrase = @$postUser["passphrase"];
    
    // Sleep
    sleep(3);
    
    try
    {
      // UsersModel生成
      $usersModel = new UsersModel();
      // User取得
      $user = $usersModel->findByUsername($username);
      // 該当なし/パスワード不一致
      if (!$user->num || !password_verify($passphrase, $user->passphrase))
      {
        // [403]
        return $this->fail([
          "status" => 403,
          "message" => "サインインに失敗しました。"
        ], 403);
      }
      
      // 署名生成
      $signature = $user->createSignature();
      
      // [200]
      return $this->respond([
        "status" => 200,
        "signature" => $signature,
      ]);
    }
    // データベース例外
    catch(DatabaseException $e)
    {
      // [500]
      return $this->fail([
        "status" => 500,
        "message" => "データベースでエラーが発生しました。"
      ], 500);
    }
  }
  
  public function GuardUser()
  {
    
  }
}