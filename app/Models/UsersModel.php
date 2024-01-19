<?php namespace App\Models;

use CodeIgniter\Model;
use CodeIgniter\Database\Query;

class UsersModel extends Model
{
  protected $db;
  protected $table = "cmsb_users";
  protected $primaryKey = "num";
  protected $returnType = "App\Entities\UserEntity";
  protected $allowedFields = [
    "username",
    "passphrase",
    "section",
    "personal",
    "viewname",
    "token",
    "active",
    "title",
  ];
  protected $skipValidation = false;
  protected $useSoftDeletes = false;
  protected $useTimestamps = true;
  protected $createdField  = "createdDate";
  protected $updatedField  = "updatedDate";
  
  public function findByToken($token)
  {
    // 暗号鍵取得
    $key = getenv("database.default.encryption.key");
    // クエリ生成
    $query = $this->db->prepare(static function ($db) 
    {
      $sql = "SELECT AES_DECRYPT(`username`, UNHEX(SHA2(?,512))) AS username, token FROM cmsb_users WHERE token IS NOT NULL AND token = ?";
      return (new Query($db))->setQuery($sql);
    });
    // クエリ実行
    $result = $query->execute(
      $key,
      $token
    );
    return $result->getRow();
  }
  
  public function insert($data = [], $returnID = true)
  {
    // 暗号鍵取得
    $key = getenv("database.default.encryption.key");
    // クエリ生成
    $query = $this->db->prepare(static function ($db) 
    {
      $sql = "INSERT INTO cmsb_users (`username`, `token`, `title`) VALUES (AES_ENCRYPT(?, UNHEX(SHA2(?,512))), ?, ?)";
      return (new Query($db))->setQuery($sql);
    });
    // クエリ実行
    $result = $query->execute(
      $data["username"],
      $key,
      $data["token"],
      "*"
    );
    return $result;
  }
}