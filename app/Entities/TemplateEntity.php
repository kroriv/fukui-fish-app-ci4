<?php namespace App\Entities;

use CodeIgniter\Entity\Entity;

class TemplateEntity extends Entity
{
  /** @var Array Attributes */
  protected $attributes = [
    "num" => null,
    "title" => "*",
    "preflight_authcode_notice_title" => null,
    "preflight_authcode_notice_content" => null,
    "user_complete_notice_title" => null,
    "user_complete_notice_content" => null,
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
      "preflight_authcode_notice_title" => $this->preflight_authcode_notice_title,
      "preflight_authcode_notice_content" => $this->preflight_authcode_notice_content,
      "user_complete_notice_title" => $this->user_complete_notice_title,
      "user_complete_notice_content" => $this->user_complete_notice_content,
    ];
  }
}