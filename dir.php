<?php
//
// give the directory sorted by date, oldest first.
//



function scan_dir($dir) {
    $ignored = array('.', '..', '.svn', '.htaccess','config.conf','map.txt','map-final.txt','t');

    $files = array();    
    $out = array();    
    foreach (scandir($dir) as $file) {
        if (in_array($file, $ignored)) continue;
        $files[$file] = filemtime($dir . '/' . $file);
    }

    asort($files);
    $files = array_keys($files);

/*    foreach ($files as $file){
    
        $out[]=['field1'=>$file];
    }
*/
    //return($out);
    return ($files) ? $files : false;
}


$dir="/var/www/html/uploads";

$data=scan_dir($dir);

//foreach($data as $file)
//   print 

echo(json_encode($data));


