<?php
function rmdir_recurse($path)
{
    $path= rtrim($path, '/').'/';
    $handle = opendir($path);
    for (;false !== ($file = readdir($handle));)
        if($file != "." and $file != ".." )
        {
            $fullpath= $path.$file;
            if( is_dir($fullpath) )
            {
                rmdir_recurse($fullpath);
                rmdir($fullpath);
            }
            else{
              $path_parts = dirname($fullpath);
              if($path_parts['basename']=='index.html')  continue;              
              unlink($fullpath);
              echo "<br>".$fullpath;
            }
        }
    closedir($handle);
}
echo 'Delete: <br><br>';

rmdir_recurse('./jscache/');
