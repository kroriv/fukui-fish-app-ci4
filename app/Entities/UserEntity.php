<?php namespace App\Entities;

use CodeIgniter\Entity\Entity;

class UserEntity extends Entity
{
  /** @var Array Attributes */
  protected $attributes = [
    "num"    => null,
    "title"  => "*",
    "email"  => null,
    "passphrase"  => null,
    "section"  => null,
    "personal"  => null,
    "viewname"  => null,
    "token"  => null,
    "signature"  => null,
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
      "email"  => $this->email,
      "section"  => $this->section,
      "personal"  => $this->personal,
      "viewname"  => $this->viewname,
      "token"  => $this->token,
      "signature"  => $this->signature,
    ];
  }
}