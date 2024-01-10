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
    $preflight = $postData->preflight;
    
    // メールアドレス取得
    $email = $preflight["email"];
    // 認証トークン生成
    $token = UtilHelper::GenerateToken(64);
    
    // PreflightModel生成
    $preflightModel = new PreflightModel();
    // PreflightModel挿入
    $preflightModel->insert([
      "email" => $email,
      "token" => $token,
    ]);
    
    // レスポンス配列生成
    $response = [];
    $response["status"] = 200;
    $response["email"] = $email;
    $response["preflight"] = $preflight;
    //$response["preflight"] = $preflight;
    //$response["email"] = $email;
    //$response["$file"] = $file;
    // [200]
    return $this->respond($response);
  }
}