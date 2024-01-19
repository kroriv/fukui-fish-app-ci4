<?php namespace App\Entities;

use CodeIgniter\Entity\Entity;
use App\Helpers\UtilHelper;

class UserEntity extends Entity
{
  /** @var Array Attributes */
  protected $attributes = [
    "num"    => null,
    "title"  => "*",
    "username"  => null,
    "passphrase"  => null,
    "section"  => null,
    "personal"  => null,
    "viewname"  => null,
    "token"  => null,
    "active"  => null,
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
      "username"  => $this->username,
      "section"  => $this->section,
      "personal"  => $this->personal,
      "viewname"  => $this->viewname,
      "token"  => $this->token,
    ];
  }
}