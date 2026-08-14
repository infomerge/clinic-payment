<?php

function dir_copy($dir_name, $new_dir)
{
  if (!is_dir($new_dir)) {
    mkdir($new_dir);
	echo $new_dir;
  }
 
  if (is_dir($dir_name)) {
    if ($dh = opendir($dir_name)) {
      while (($file = readdir($dh)) !== false) {
        if ($file == "." || $file == "..") {
          continue;
        }
        if (is_dir($dir_name . "/" . $file)) {
          dir_copy($dir_name . "/" . $file, $new_dir . "/" . $file);
        }
        else {
          copy($dir_name . "/" . $file, $new_dir . "/" . $file);
        }
      }
      closedir($dh);
    }
  }
  return true;
}