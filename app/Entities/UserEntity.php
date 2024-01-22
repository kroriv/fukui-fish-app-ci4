<?php namespace App\Entities;

use CodeIgniter\Entity\Entity;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class UserEntity extends Entity
{
  /** @var Array Attributes */
  protected $attributes = [
    "num"    => null,
    "title"  => "*",
    "username"  => null,
    "passphrase"  => null,
    "section"  => null,
    "viewname"  => null,
    "personal"  => null,
    "active"  => null,
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
  
  
  public function createSignature(int $lifespan = null): String
  {
    // 署名ぺイロード生成
    $payload = [
      "data" => [
        "user" => (object)["token" => $this->token]
      ],
      "iat" => time(),
      "exp" => time() + ($lifespan ? $lifespan : 60*60*24*7) // 指定がなければ1週間の寿命を与える
    ];
    // 署名生成
    return JWT::encode($payload, getenv("jwt.secret.key"), getenv("jwt.signing.algorithm"));
  }
  
  /**
   * JsonSerializable
   * @todo JSONシリアライズ関数
   */
  public function jsonSerialize(): Array
  {
    return
    [
      "username"  => $this->username,
      "section"  => $this->section,
      "viewname"  => $this->viewname,
      "personal"  => $this->personal,
      "active"  => $this->active,
      "token"  => $this->token,
    ];
  }
}