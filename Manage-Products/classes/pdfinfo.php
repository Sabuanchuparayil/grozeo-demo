<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of pdfinfo
 *
 * @author jijutm
 */
class pdfinfo {

    //put your code here
    /**
     * @method getPdfInfo
     * @access private
     * @param string $path
     * @return mixed Boolean FALSE if failure, else associative array 
     *               of meta information
     */
    public static function meta($path) {
        $g = array();
        $i = 0;
        if (defined('AWS_ENABLED')) {
            $tmpfile = workspace::tempfile(uniqid(""), 'pdf');
            copy($path, $tmpfile);
            exec("/usr/bin/pdfinfo -meta '$tmpfile'", $g, $i);
            unlink($tmpfile);
        } else {
            exec("/usr/bin/pdfinfo -meta '$path'", $g, $i);
        }
        
        $g = preg_replace('/:\s+/', ':', $g);
        preg_match_all("/(.+?):(.+?)\\n/", join("\n", $g), $m);
        $info = array_combine($m[1], $m[2]);
         
        return $info;
    }

    public static function getSizing(&$meta) {
        $ps = array(
            array('612', '792'), //Letter		 
            array('612', '1008'), //Legal		 
            array('540', '720'), //Executive	 
            array('595', '842'), //A4		 
        );
        $tolerance = 0.005; // 0.5%
        preg_match("@([0-9\.]+)\sx\s([0-9\.]+)\s@",$meta['Page size'], $p);
        $d = array(
            'w' => array($p[1] * (1 - $tolerance), $p[1] * (1 + $tolerance)),
            'h' => array($p[2] * (1 - $tolerance), $p[2] * (1 + $tolerance))
        );
        
        $rv = "";
        foreach($ps as $pg_size){
            if($d['w'][0] <= $pg_size[0] && 
                    $pg_size[0] <= $d['w'][1] &&
                    $d['h'][0] <= $pg_size[1] &&
                    $pg_size[1] <= $d['h'][1]
                    ){
                $rv = " -dDEVICEWIDTHPOINTS={$pg_size[0]} -dDEVICEHEIGHTPOINTS={$pg_size[1]} -dPDFFitPage ";
                $meta['Page size'] = "{$pg_size[0]} x {$pg_size[1]} pts";
            }
        }
        return $rv;
    }

}
