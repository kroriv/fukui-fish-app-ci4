<?php namespace App\Models;

use CodeIgniter\Model;
use CodeIgniter\Database\Query;
use App\Entities\PreflightEntity;

class PreflightsModel extends Model
{
  protected $db;
  protected $table = "cmsb_preflights";
  protected $primaryKey = "num";
  protected $returnType = "App\Entities\PreflightEntity";
  protected $allowedFields = [
    "email",
    "authcode",
    "token",
    "title",
  ];
  protected $skipValidation = false;
  protected $useSoftDeletes = false;
  protected $useTimestamps = true;
  protected $createdField  = "createdDate";
  protected $updatedField  = "updatedDate";
  
  public function findByToken(string $token): PreflightEntity
  {
    // 暗号鍵取得
    $key = getenv("database.default.encryption.key");
    // クエリ生成
    $query = $this->db->prepare(static function ($db) 
    {
      $sql = "SELECT *, AES_DECRYPT(`email`, UNHEX(SHA2(?,512))) AS `email` FROM cmsb_preflights WHERE token IS NOT NULL AND token = ?";
      return (new Query($db))->setQuery($sql);
    });
    // クエリ実行
    $result = $query->execute(
      $key,
      $token
    );
    // レコード取得
    $row = $result->getRow();
    
    return $row && $row->num ? new PreflightEntity((array)$row) : new PreflightEntity();
  }
  public function findByEmail(string $email): PreflightEntity
  {
    // 暗号鍵取得
    $key = getenv("database.default.encryption.key");
    // クエリ生成
    $query = $this->db->prepare(static function ($db) 
    {
      $sql = "SELECT *, AES_DECRYPT(`email`, UNHEX(SHA2(?,512))) AS `email` FROM cmsb_preflights WHERE email IS NOT NULL HAVING email = ?";
      return (new Query($db))->setQuery($sql);
    });
    // クエリ実行
    $result = $query->execute(
      $key,
      $email
    );
    // レコード取得
    $row = $result->getRow();
    
    return $row && $row->num ? new PreflightEntity((array)$row) : new PreflightEntity();
  }
  
  public function insert($data = [], $returnID = true)
  {
    // 暗号鍵取得
    $key = getenv("database.default.encryption.key");
    // クエリ生成
    $query = $this->db->prepare(static function ($db) 
    {
      $sql = "INSERT INTO cmsb_preflights (`email`, `authcode`, `token`, `title`) VALUES (AES_ENCRYPT(?, UNHEX(SHA2(?,512))), ?, ?, ?)";
      return (new Query($db))->setQuery($sql);
    });
    // クエリ実行
    $result = $query->execute(
      $data["email"],
      $key,
      $data["authcode"],
      $data["token"],
      "*"
    );
    return $result;
  }
}