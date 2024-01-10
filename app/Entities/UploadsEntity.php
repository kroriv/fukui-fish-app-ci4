<?php namespace App\Entities;

use CodeIgniter\Entity\Entity;
use App\Entities\Traits\Shared;

/**
 * UploadsEntity
 */
class UploadsEntity extends Entity
{
  /** @var Array Attributes */
  protected $attributes = [
    "num"    => null,
    "order"  => null,
    "tableName"  => null,
    "fieldName"  => null,
    "recordNum"  => null,
    "filePath"  => null,
    "urlPath"  => null,
    "width"  => null,
    "height"  => null,
    "filesize"  => null,
    "preSaveTempId"  => null,
    "storage"  => null,
    "mediaNum"  => null,
    
    "thumbFilePath" => null,
    "thumbUrlPath" => null,
    "thumbWidth" => null,
    "thumbHeight" => null,
    
    "thumbFilePath2" => null,
    "thumbUrlPath2" => null,
    "thumbWidth2" => null,
    "thumbHeight2" => null,
    
    "thumbFilePath3" => null,
    "thumbUrlPath3" => null,
    "thumbWidth3" => null,
    "thumbHeight3" => null,
    
    "thumbFilePath4" => null,
    "thumbUrlPath4" => null,
    "thumbWidth4" => null,
    "thumbHeight4" => null,
    
    "info1" => null,
    "info2" => null,
    "info3" => null,
    "info4" => null,
    "info5" => null,
  ];
  
  /** @var Array Dates */
  protected $dates = [
    "createdTime", 
  ];
}