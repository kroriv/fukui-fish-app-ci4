<?php namespace App\Controllers;

use App\Entities\PreflightEntity;

class HomeController extends BaseController
{
  public function Index(): string
  {
    return ("<h1>Hello, World!</h1>");
  }
}
