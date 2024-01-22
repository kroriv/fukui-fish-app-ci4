<?php namespace App\Controllers;

class HomeController extends BaseController
{
  public function Index(): string
  {
    return ("<h1>Hello, World!</h1>");
  }
}
