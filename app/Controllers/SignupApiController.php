<?php namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use App\Helpers\UtilHelper;
use App\Models\PreflightModel;

class SignupApiController extends ResourceController
{
  // Trait
  use ResponseTrait;
  
  /** @var String Format */
  protected $format = "json";
  
  public function TestPreflight() 
  {
    echo $ip = $_SERVER['REMOTE_ADDR'];
  }
  
  public function LoadPreflight() 
  {
    // フォームデータ取得
    $postData = (object)$this->request->getPost();
    // Preflight取得
    $preflight = $postData->preflight;
    // 認証トークン取得
    $token = $preflight["token"];
    
    try
    {
      // PreflightModel生成
      $preflightModel = new PreflightModel();
      // Preflight取得
      $preflight = $preflightModel->findByToken($token);
      
      // Preflight該当なし
      if (!$preflight)
      {
        // [404]
        return $this->fail([
        ], 404);
      }
      
      // [200]
      return $this->respond([
        "status" => 200,
        "preflight" => $preflight
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
  
  public function CreatePreflight() 
  {
    // フォームデータ取得
    $postData = (object)$this->request->getPost();
    // Preflight取得
    $preflight = $postData->preflight;
    // メールアドレス取得
    $email = $preflight["email"];
    // 認証トークン生成
    $token = UtilHelper::GenerateToken(64);
    
    try 
    {
      // PreflightModel生成
      $preflightModel = new PreflightModel();
      // PreflightModel挿入
      $preflightModel->insert([
        "email" => $email,
        "token" => $token,
      ]);
      
      // Sleep
      sleep(3);
      
      // [200]
      return $this->respond([
        "status" => 200
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
}