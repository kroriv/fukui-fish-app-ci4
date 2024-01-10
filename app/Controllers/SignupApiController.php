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
    $postData = $this->request->getPost();
    // 認証トークン取得
    $token = $postData["token"];
    
    // PreflightModel生成
    $preflightModel = new PreflightModel();
    //$preflight = $preflightModel->where("token", $token)->findAll();
    
    // Preflight取得
    $preflight = $preflightModel->findByToken($token);
    
    // レスポンス配列生成
    $response = [];
    $response["status"] = 200;
    $response["preflight"] = $preflight;
    //$response["postData"] = $postData;
    
    // [200]
    return $this->respond($response);
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
    
    // PreflightModel生成
    $preflightModel = new PreflightModel();
    
    // Sleep
    sleep(3);
    
    try {
      // PreflightModel挿入
      $preflightModel->insert([
        "email" => $email,
        "token" => $token,
      ]);
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
      ], 500);
    }
  }
}