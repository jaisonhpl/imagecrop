<?php

error_reporting(0);
ini_set('display_errors', 0);

/**
The function loadImage crops the image provided in the resize URL.

For example below URL will crop the original image(http://stagingpowerline.s3-website-us-east-1.amazonaws.com/avatars/55f174d90d411.jpeg) to 50x50 pixels:

Resize URL: http://client.heypayless.com/imgcrop/crop.php?50x50&http://stagingpowerline.s3-website-us-east-1.amazonaws.com/avatars/55f174d90d411.jpeg

**/
function LoadImage($imgname,$ext)
{

   /* Attempt to open */
if($ext == "jpeg" || $ext == "jpg")
  $im = @imagecreatefromjpeg($imgname);
else if($ext == "png")
  $im = @imagecreatefrompng($imgname);

   /* See if it failed */
   if(!$im)
   {
       /* Create a blank image */
       $im  = imagecreatetruecolor(150, 30);
       $bgc = imagecolorallocate($im, 255, 255, 255);
       $tc  = imagecolorallocate($im, 0, 0, 0);

       imagefilledrectangle($im, 0, 0, 150, 30, $bgc);

       /* Output an error message */
       imagestring($im, 1, 5, 5, 'Error loading ' . $imgname, $tc);
   }

   return $im;
}

//finding current url eg. http://powerline.com/imgcrop/crop.php?100x100&http://www.fordesigner.com/imguploads/Image/cjbc/zcool/png20080526/1211771871.png
$url = "http://".$_SERVER[HTTP_HOST].$_SERVER['REQUEST_URI'];
$url = explode('?',$url);
$img_arr = explode('&',$url[1]); // splitting into size & image_url
$size    = $img_arr[0];
$img_path= $img_arr[1];
$newwidth   = explode('x', $size);
$newwidth = $newwidth[0]; // height

$newheight  = explode('x', $size);
$newheight = $newheight[1]; //width
$img_path_arr = explode('/',$img_path);
$img_src = $img_path_arr[sizeof($img_path_arr) - 1];
$ext = explode('.',$img_src);
$ext = $ext[1];


if(file_exists("thumbs/$size/$img_src"))
{	
  // if the file is resized previously
  if($ext == "jpeg" || $ext == "jpg"){
    header('Content-Type: image/jpeg');
    $img = LoadImage("thumbs/$size/$img_src",$ext);
    imagejpeg($img);
    imagedestroy($img);
  }
  else if($ext == "png"){
    header('Content-Type: image/png');
    $img = LoadImage("thumbs/$size/$img_src",$ext);
    imagejpeg($img);
    imagedestroy($img);
  }

}
else
{
  $img = "thumbs/raw/".$img_src;
  $dest_path = "thumbs/".$size."/";

  if(!file_exists($dest_path))
  mkdir($dest_path,0777,true);

  $dest_path = "thumbs/".$size."/".$img_src;

  file_put_contents($img, file_get_contents($img_path));

  list($width, $height) = getimagesize($img);

  if($ext == "jpeg" || $ext == "jpg")
    $image = imagecreatefromjpeg($img);
  else if($ext == "png")
    $image = imagecreatefrompng($img);

  $thumbImage = imagecreatetruecolor($newwidth, $newheight);

  imagecopyresized($thumbImage, $image, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);
  imagedestroy($image);

  //imagedestroy($thumbImage); do not destroy before display :)
  ob_end_clean();  // clean the output buffer ... if turned on.

  if($ext == "jpeg" || $ext == "jpg"){
    header('Content-Type: image/jpeg');
    imagejpeg($thumbImage);
    imagejpeg($thumbImage,$dest_path);
  }
  else if($ext == "png"){
    header('Content-Type: image/jpeg');
    imagepng($thumbImage);
    imagepng($thumbImage,$dest_path);
  
  }
  imagedestroy($thumbImage);

}