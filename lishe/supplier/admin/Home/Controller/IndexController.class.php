<?php
namespace Home\Controller;
class IndexController extends CommonController {
    public function index(){

        header("Location:Login/Index");
        exit;

   }
    public function login(){
        echo 'login';
    }
}