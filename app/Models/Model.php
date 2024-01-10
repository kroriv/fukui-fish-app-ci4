<?php namespace App\Models;

use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\Database\ConnectionInterface;
use CodeIgniter\Database\Exceptions\DatabaseException;

abstract class Model
{
  /**
   * @var string
   */
  protected string $table;
  
  /**
   * @var ConnectionInterface
   */
  protected $db;
  
  /**
   * @var BaseBuilder
   */
  protected BaseBuilder $builder;
  
  public function __construct(?ConnectionInterface $db = null)
  {
    if ($db === null) 
    {
      $this->db = db_connect();
    } 
    else 
    {
      $this->db = $db;
    }
    $this->builder = $this->db->table($this->table);
  }
  
  /**
   * @param array $data
   * @return bool
   */
  public function insert(array $data): bool
  {
    $result = $this->builder->insert($data);
    if ($result === false) 
    {
      throw new DatabaseException();
    } 
    else 
    {
      return $result;
    }
  }
  
  /**
   * @param array $where
   * @param array $data
   * @return bool
   */
  public function update(array $where, array $data): bool
  {
    $this->builder->where($where);
    $result = $this->builder->update($data);
    if ($result === false) 
    {
      throw new DatabaseException();
    } 
    else 
    {
      return true;
    }
  }
  
  /**
   * @param array $where
   * @return bool|string
   */
  public function delete(array $where): bool|string
  {
    $this->builder->where($where);
    $result = $this->builder->delete();
    if ($result === false) 
    {
      throw new DatabaseException();
    } 
    else 
    {
      return $result;
    }
  }
}