<?php namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\Database\Exceptions\DatabaseException;
use Config\Services;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\SignatureInvalidException;
use Firebase\JWT\ExpiredException;
use App\Helpers\UtilHelper;
use App\Models\PreflightsModel;

class SignupApiController extends ResourceController
{
  // Trait
  use ResponseTrait;
  
  /** @var String Format */
  protected $format = "json";
  
  public function LoadPreflight() 
  {
    // フォームデータ取得
    $postData = (object)$this->request->getPost();
    // Preflight取得
    $preflight = $postData->preflight;
    // 認証識別子取得
    $token = @$preflight["token"];
    // 認証署名取得
    $signature = @$preflight["signature"];
    
    return $signature ? self::_LoadPreflightWithSignature($signature) : self::_LoadPreflightWithToken($token);
  }
  
  private function _ValidatePreflightSignature(string $signature)
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
      $token = $decoded->data->preflight->token;
      
      // PreflightsModel生成
      $preflightsModel = new PreflightsModel();
      // Preflight取得
      $preflight = $preflightsModel->findByToken($token);
      
      // Preflight該当なし
      if (!$preflight)
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
        "preflight" => $preflight
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
      // [401]
      return [
        "status" => 500,
        "message" => "予期しない例外が発生しました。"
      ];
    }
  }
  
  private function _LoadPreflightWithSignature(string $signature)
  {
    // 署名検証
    $validated = self::_ValidatePreflightSignature($signature);
    // 署名エラー
    if (intval(@$validated["status"]) !== 200)
    {
      return $this->fail([
        "status" => @$validated["status"],
        "message" => @$validated["message"]
      ], @$validated["status"]);
    }
    
    // [200]
    return $this->respond([
      "status" => 200,
      "preflight" => @$validated["preflight"]
    ]);
  }
  
  private function _LoadPreflightWithToken(string $token)
  {
    try
    {
      // PreflightsModel生成
      $preflightsModel = new PreflightsModel();
      // Preflight取得
      $preflight = $preflightsModel->findByToken($token);
      // Preflight該当なし
      if (!$preflight)
      {
        // [404]
        return $this->fail([
          "message" => "該当する署名はありません。"
        ], 404);
      }
      
      // [200]
      return $this->respond([
        "status" => 200,
        "preflight" => $preflight
      ]);
    }
    // データベース例外
    catch(DatabaseException $e)
    {
      // [500]
      return $this->fail([
        "message" => "データベースでエラーが発生しました。"
      ], 500);
    }
    // その他例外
    catch (\Exception $e)
    {
      // [500]
      return $this->fail([
        "message" => "予期しない例外が発生しました。"
      ], 500);
    }
  }
  
  public function CreatePreflight() 
  {
    // フォームデータ取得
    $postData = (object)$this->request->getPost();
    // Preflight取得
    $preflight = $postData->preflight;
    // メールアドレス取得
    $email = $preflight["email"];
    // 認証コード生成
    $authcode = password_hash(UtilHelper::GetRandomNumber(4), PASSWORD_DEFAULT);
    // 認証トークン生成
    $token = UtilHelper::GenerateToken(64);
    
    try 
    {
      // PreflightsModel生成
      $preflightsModel = new PreflightsModel();
      // PreflightsModel挿入
      $preflightsModel->insert([
        "email" => $email,
        "authcode" => $authcode,
        "token" => $token,
      ]);
      
      // 署名ぺイロード生成
      $payload = [
        "data" => [
          "preflight" => (object)["token" => $token]
        ],
        "iat" => time(),
        "exp" => time() + 60*60*1 // 1時間の寿命を与える
      ];
      
      // 署名生成
      $signature =JWT::encode($payload, getenv("jwt.secret.key"), getenv("jwt.signing.algorithm"));
      
      // Sleep
      sleep(3);
      
      // [200]
      return $this->respond([
        "status" => 200,
        "signature" => $signature
      ]);
    } 
    catch(\Exception $e) 
    {
      // [500]
      return $this->fail([
        "status" => 500
      ]);
    }
  }
  
  public function AuthPreflight()
  {
    // フォームデータ取得
    $postData = (object)$this->request->getPost();
    // Preflight取得
    $preflight = $postData->preflight;
    
    // 認証署名取得
    $signature = $preflight["signature"];
    // 認証コード取得
    $authcode = $preflight["authcode"];
    
    // 署名検証
    $validated = self::_ValidatePreflightSignature($signature);
    // 署名エラー
    if (intval(@$validated["status"]) !== 200)
    {
      return $this->fail([
        "status" => @$validated["status"],
        "message" => @$validated["message"]
      ], @$validated["status"]);
    }
    
    
    
  }
}