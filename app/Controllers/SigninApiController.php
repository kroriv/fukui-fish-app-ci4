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
    // フォームデータ取得
    $postData = (object)$this->request->getPost();
    // User取得
    $postUser = $postData->user;
    // 認証署名取得
    $signature = @$postUser["signature"];
    
    // 署名検証
    $validated = self::_ValidateUserSignature($signature);
    // 署名検証エラー
    if (intval(@$validated["status"]) !== 200)
    {
      return $this->fail([
        "status" => @$validated["status"],
        "message" => @$validated["message"]
      ], intval(@$validated["status"]));
    }
    // [200]
    return $this->respond([
      "status" => 200,
      "user" => @$validated["user"]
    ]);
  }
  
  private function _ValidateUserSignature(string $signature)
  {
    // バリデーション生成
    $validation = Services::validation();
    $validation->setRules([
      "signature" => "required",
    ]);
    $validation->setRule("signature", "認証署名", "required");
    $validation->run(["signature" => $signature]);
    // バリデーションエラー
    if (!$validation->run(["signature" => $signature]))
    {
      return [
        "status" => 401,
        "message" => "認証署名の形式が不正です。"
      ];
    }
    
    try
    {
      // 認証署名復元
      $decoded = JWT::decode($signature, new Key(getenv("jwt.secret.key"), getenv("jwt.signing.algorithm")));
      // 認証識別子取得
      $token = $decoded->data->user->token;
      // UsersModel生成
      $usersModel = new UsersModel();
      // User取得
      $user = $usersModel->findByToken($token);
      // User該当なし
      if (!$user || !$user->num)
      {
        // [404]
        return [
          "status" => 404,
          "message" => "該当する署名はありません。",
        ];
      }
      
      // [200]
      return [
        "status" => 200,
        "message" => "",
        "user" => $user,
      ];
    }
    // データベース例外
    catch(DatabaseException $e)
    {
      // [500]
      return [
        "status" => 500,
        "message" => "データベースでエラーが発生しました。"
      ];
    }
    // JSON形式例外
    catch (\JsonException $e)
    {
      // [411]
      return [
        "status" => 411,
        "message" => "JSONの形式が不正です。"
      ];
    }
    // 署名形式例外
    catch (SignatureInvalidException $e)
    {
      // [401]
      return [
        "status" => 401,
        "message" => "認証署名の形式が不正です。"
      ];
    }
    // 有効期限切例外
    catch (ExpiredException $e)
    {
      // [401]
      return [
        "status" => 401,
        "message" => "認証署名の有効期限が過ぎました。"
      ];
    }
    // その他例外
    catch (\Exception $e)
    {
      // [500]
      return [
        "status" => 500,
        "message" => "予期しない例外が発生しました。"
      ];
    }
  }
}