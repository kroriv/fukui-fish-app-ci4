<?php namespace App\Models;

use CodeIgniter\Model;
use CodeIgniter\Database\Query;

class PreflightModel extends Model
{
  protected $db;
  protected $table = "cmsb_preflight";
  protected $primaryKey = "num";
  protected $returnType = "App\Entities\PreflightEntity";
  protected $allowedFields = [
    "email",
    "token",
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
      $sql = "SELECT AES_DECRYPT(`email`, UNHEX(SHA2(?,512))) as email FROM cmsb_preflight WHERE token IS NOT NULL AND token = ?";
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
      $sql = "INSERT INTO cmsb_preflight (`email`, `token`, `title`) VALUES (AES_ENCRYPT(?, UNHEX(SHA2(?,512))), ?, ?)";
      return (new Query($db))->setQuery($sql);
    });
    // クエリ実行
    $result = $query->execute(
      $data["email"],
      $key,
      $data["token"],
      "*"
    );
    return $result;
  }
}