<?php namespace App\Entities;

use CodeIgniter\Entity\Entity;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
use App\Helpers\UtilHelper;

class PreflightEntity extends Entity
{
  /** @var Array Attributes */
  protected $attributes = [
    "num"    => null,
    "title"  => "*",
    "email"  => null,
    "authcode"  => null,
    "token"  => null,
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
      "email"  => $this->email,
      "authcode"  => $this->authcode,
      "token"  => $this->token,
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
   * @param object $event
   * @param object $settings
   */
  public function sendAuthcodeMail(object $event, object $settings): void
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
      
      //日本語用設定
      //$mailer->CharSet = "iso-2022-jp";
      $mailer->CharSet = "UTF-8";
      //$mailer->Encoding = "7bit";
      
      // FormJSON
      $form_json = json_decode($this->form_json);
      
      // Replacement
      ob_start();
      $body = $settings->reply_body;
      $body = str_replace("%担当者名%", $form_json->officer, $body);
      $body = str_replace("%イベントカテゴリ%", $event->category, $body);
      $body = str_replace("%イベント名%", $event->title, $body);
      $body = str_replace("%チケットURL%", getenv("app.ticketUrl") . sprintf("/%s/ticket/", $this->section). $this->token, $body);
      $body = str_replace("%認証コード%", $this->uuid, $body);
      $body = str_replace("%送信内容%", $this->form_content, $body);
      ob_clean();
      
      $mailer->setFrom("reply@tooway.jp", "福井工業大学 予約受付システム"); // 送信者
      $mailer->addAddress($this->email);   // 宛先
      $mailer->Subject = "予約が完了しました。[福井工業大学]"; 
      $mailer->Body = UtilHelper::Br2Nl($body);
      
      if (getenv("CI_ENVIRONMENT") === "production")
      {
        // 申込者へ送信
        if (@$this->email)
        {
          $mailer->send();
        }
      }
    }
    catch (\Exception $e)
    {
      
    }
  }
}