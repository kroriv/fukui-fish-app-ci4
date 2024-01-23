<?php namespace App\Entities;

use CodeIgniter\Entity\Entity;
use Firebase\JWT\JWT;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
use App\Helpers\UtilHelper;
use App\Models\TemplatesModel;
use App\Entities\TemplateEntity;

class PreflightEntity extends Entity
{
  /** @var Array Attributes */
  protected $attributes = [
    "num" => null,
    "title" => "*",
    "email" => null,
    "authcode" => null,
    "token" => null,
    "dragSortOrder" => null,
    "createdByUserNum" => 1,
    "updatedByUserNum" => 1
  ];
  
  /** @var Array Dates */
  protected $dates = [
    "createdDate", 
    "updatedDate",
  ];
  
  /**
   * JsonSerializable
   * @todo JSONシリアライズ関数
   */
  public function jsonSerialize(): Array
  {
    return
    [
      "email" => $this->email,
      "authcode" => $this->authcode,
      "token" => $this->token,
    ];
  }
  
  public function createSignature(int $lifespan = null): String
  {
    // 署名ぺイロード生成
    $payload = [
      "data" => [
        "preflight" => (object)["token" => $this->token]
      ],
      "iat" => time(),
      "exp" => time() + ($lifespan ? intval($lifespan) : 60*60*24) // 指定がなければ24時間の寿命を与える
    ];
    // 署名生成
    return JWT::encode($payload, getenv("jwt.secret.key"), getenv("jwt.signing.algorithm"));
  }
  
  /**
   * メール送信関数
   */
  public function sendAuthcodeNotice(string $authcode): void
  {
    // TemplatesModel生成
    $templatesModel = new TemplatesModel();
    // Template取得
    $temlate = $templatesModel->where("num", 1)->first();
    
    // 言語、内部エンコーディングを指定
    mb_language("japanese");
    mb_internal_encoding("UTF-8");
    
    // PHPMailer
    $mailer = new PHPMailer(true);
    
    try 
    {
      require ROOTPATH . "vendor/autoload.php";
      require ROOTPATH . "vendor/phpmailer/phpmailer/language/phpmailer.lang-ja.php";
      
      // Replacement
      ob_start();
      $body = $temlate->preflight_authcode_notice_content;
      $body = str_replace("%認証コード%", $authcode, $body);
      ob_clean();
      
      $mailer->isSMTP();
      $mailer->SMTPAuth = true;
      $mailer->Host = getenv("smtp.default.hostname");
      $mailer->Username = getenv("smtp.default.username");
      $mailer->Password = getenv("smtp.default.password");
      $mailer->Port = intval(getenv("smtp.default.port"));
      $mailer->SMTPSecure = "tls";
      $mailer->CharSet = "utf-8";
      $mailer->Encoding = "base64";
      $mailer->setFrom(getenv("smtp.default.from"), "FUKUI BRAND FISH");
      $mailer->addAddress($this->email);
      $mailer->Subject = $temlate->preflight_authcode_notice_title; 
      $mailer->Body = UtilHelper::Br2Nl($body);
      
      // 本番環境・ステージング環境のみ送信
      if (getenv("CI_ENVIRONMENT") === "production")
      {
        $mailer->send();
      }
    }
    catch (Exception $e)
    {
      
    }
  }
  
  public function sendTestNotice(): void
  {
    // 言語、内部エンコーディングを指定
    mb_language("japanese");
    mb_internal_encoding("UTF-8");
    
    // PHPMailer
    $mailer = new PHPMailer(true);
    
    try 
    {
      require ROOTPATH . "vendor/autoload.php";
      require ROOTPATH . "vendor/phpmailer/phpmailer/language/phpmailer.lang-ja.php";
      
      $mailer->isSMTP();
      $mailer->SMTPAuth = true;
      $mailer->Host = getenv("smtp.default.hostname");
      $mailer->Username = getenv("smtp.default.username");
      $mailer->Password = getenv("smtp.default.password");
      $mailer->Port = 587;
      $mailer->SMTPSecure = "tls";
      $mailer->CharSet = "utf-8";
      $mailer->Encoding = "base64";
      $mailer->setFrom(getenv("smtp.default.from"), "FUKUI BRAND FISH");
      $mailer->addAddress($to, $to_name);
      $mailer->Subject = $subject;
      $mailer->Body = $body;
      
      // 本番環境・ステージング環境のみ送信
      if (getenv("CI_ENVIRONMENT") === "production")
      {
        $mailer->send();
      }
    }
    catch (Exception $e)
    {
      print_r($e->getMessage());
    }
  }
}