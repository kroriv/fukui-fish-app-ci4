<?php namespace App\Models;

use CodeIgniter\Model;
use CodeIgniter\Database\Query;
use App\Entities\UserEntity;

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
    "viewname",
    "personal",
    "token",
    "active",
    "title",
  ];
  protected $skipValidation = false;
  protected $useSoftDeletes = false;
  protected $useTimestamps = true;
  protected $createdField  = "createdDate";
  protected $updatedField  = "updatedDate";
  
  public function findByUsername($username)
  {
    // 暗号鍵取得
    $key = getenv("database.default.encryption.key");
    // クエリ生成
    $query = $this->db->prepare(static function ($db) 
    {
      $sql = "SELECT *, AES_DECRYPT(`username`, UNHEX(SHA2(?,512))) AS `username`, AES_DECRYPT(`personal`, UNHEX(SHA2(?,512))) AS `personal` FROM cmsb_users WHERE username IS NOT NULL HAVING username = ?";
      return (new Query($db))->setQuery($sql);
    });
    // クエリ実行
    $result = $query->execute(
      $key,
      $key,
      $username
    );
    // レコード取得
    $row = $result->getRow();
    
    return $row && $row->num ? new UserEntity((array)$row) : new UserEntity();
  }
  
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
    // レコード取得
    $row = $result->getRow();
    
    return $row && $row->num ? new UserEntity((array)$row) : new UserEntity();
  }
  
  public function insert($data = [], $returnID = true)
  {
    // 暗号鍵取得
    $key = getenv("database.default.encryption.key");
    // クエリ生成
    $query = $this->db->prepare(static function ($db) 
    {
      $sql = "INSERT INTO cmsb_users (`username`, `passphrase`, `section`, `viewname`, `personal`, `active`, `token`, `title`) VALUES (AES_ENCRYPT(?, UNHEX(SHA2(?,512))), ?, ?, ?, AES_ENCRYPT(?, UNHEX(SHA2(?,512))), ?, ?, ?)";
      return (new Query($db))->setQuery($sql);
    });
    // クエリ実行
    $result = $query->execute(
      $data["username"],
      $key,
      $data["passphrase"],
      $data["section"],
      $data["viewname"],
      json_encode($data["personal"]),
      $key,
      intval($data["section"]) === 3 ? 0 : 1, // 
      $data["token"],
      "*"
    );
    return $result;
  }
}