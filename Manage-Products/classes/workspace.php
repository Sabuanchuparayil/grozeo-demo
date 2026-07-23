<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of workspace
 *
 * @author jijutm
 */
class workspace {

    //put your code here
    public static function clean($path) {
        $prefix = self::get_temp_dir();
        if (substr($path, 0, strlen($prefix)) === $prefix) {
            $files = glob($path . '/*');
            array_walk($files, 'workspace::cleanup');
            rmdir($path);
        }
    }

    public static function cleanup($file) {
        unlink($file);
    }

    public static function setup($signature = '/wk_', $use_session = false) {
        /* create a workspace */
        $tag = ($use_session === false) ? getmypid() : session_id();
        $work_space = self::get_temp_dir() . $signature . $tag;
        $k = 0;
        $path = $work_space . '-' . $k;
        while (is_dir($work_space)) {
            $k++;
            $path = $work_space . '-' . $k;
        }
        mkdir($path, 0755);
        return $path;
    }
    
    public static function tempfile($prefix, $extn){
        $tag = getmypid();
        return sprintf("%s/%s-%s.%s", self::get_temp_dir() , $prefix,  $tag, $extn);
        
    }
    
    public static function get_temp_dir(){
        return '/tmp';
    }

}
