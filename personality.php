<?php
//
// Personality Handler - list the directories under personality, retrun current personality, allow setting of symbolic link to set personality
//

$personality_dir="/var/www/html/personality";

$personality_sim_link="/var/www/html/uploads";


function list_personality($dir) {
    $ignored = array('.', '..', '.svn', '.htaccess','config.conf','none','map-final.txt','t');

    $files = array();    
    $out = array();    
    foreach (scandir($dir) as $file) {
        if (in_array($file, $ignored)) continue;
        $files[$file] = filemtime($dir . '/' . $file);
    }

    arsort($files);
    $files = array_keys($files);

/*    foreach ($files as $file){
    
        $out[]=['field1'=>$file];
    }
*/
    //return($out);
    return ($files) ? $files : false;
}

function get_personality($path)
{
    $current_personality=readlink($path);

    if(!$current_personality)
        $current_personality="None";
    else
        $current_personality=basename($current_personality);

    return($current_personality);
}

//
// Set a personality ($personality) to the simlink,   verify it is valid in the $personality_dir
//
// Return TRUE if set, Fales if not
//
function set_personality($personality_sim_link,$personality_dir,$personality)
{

    $valid_personalities=list_personality($personality_dir);

    $current_personality=get_personality($personality_sim_link);

    if (in_array( $personality, $valid_personalities))
    {
        if($current_personality == $personality)
            echo("no action, current setting\n");
        else
            echo("valid change\n");
    
    }
    else
        echo("not valid change\n");

}



//$data=scan_dir($dir);




echo("list: \n");
var_dump(list_personality($personality_dir));
echo("\n\n");

echo("current: \n");
echo(get_personality($personality_sim_link));

echo("\n\n");

set_personality($personality_sim_link,$personality_dir, "medusa");



