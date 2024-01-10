<?php namespace App\Entities;

use CodeIgniter\Entity\Entity;

class PreflightEntity extends Entity
{
  /** @var Array Attributes */
  protected $attributes = [
    "num"    => null,
    "title"  => "*",
    "email"  => null,
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
      "token"  => $this->token,
    ];
  }
}