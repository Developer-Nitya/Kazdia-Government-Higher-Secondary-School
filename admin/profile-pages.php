<?php
declare(strict_types=1);
// START: Profile admin section
session_start();
if(empty($_SESSION['school_admin_logged_in'])){header('Location:index.php');exit;}
$file=__DIR__.'/../storage/profile-pages.json';
$data=json_decode(file_get_contents($file),true) ?: [];
if($_SERVER['REQUEST_METHOD']==='POST'){
 $slug=$_POST['slug']??'';
 if(isset($data[$slug])){
  $data[$slug]['content']=$_POST['content']??'';
  file_put_contents($file,json_encode($data,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
 }
}
echo 'Profile pages updated';
// END: Profile admin section
