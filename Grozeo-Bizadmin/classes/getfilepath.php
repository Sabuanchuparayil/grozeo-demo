<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of getfilepath
 *
 * @author jijutm
 */
class getfilepath {

    //put your code here

    public static function preview($id, $type = "report") {
        (defined('ON_STATIONERY')) && $type = "report-on-stationery";
        return self::uploaded($id, $type, false, '-preview.pdf');
    }

    public static function preview_invoice($id, $type = "") {
        (defined('ON_STATIONERY')) && $type = "report-on-stationery";
        return self::inv_uploaded($id, $type, false, '.pdf');
    }

    public static function inv_uploaded($id, $type = "", $version = false, $extn = '.pdf') {
        $path = self::store(INVOICES_STORE, '/' . substr(str_pad($id, 3, '0', STR_PAD_LEFT), -2)) . "/{$id}" . $extn;

        self::mkDir(dirname($path));
        if (defined('AWS_ENABLED')) {
            return $path;
        }
    

        return $path;
    }

    public static function uploaded($id, $type = "job", $version = false, $extn = '.pdf') {
        $path = self::store(REPORTS_STORE, '/' . substr(str_pad($id, 3, '0', STR_PAD_LEFT), -2)) . "/{$id}-{$type}" . $extn;


        self::mkDir(dirname($path));
        if (defined('AWS_ENABLED')) {
            return $path;
        }
        if (!$version) {
            $pattern = substr($path, 0, -4) . '*' . $extn;
            $list = glob($pattern);
            if (!empty($list)) {
                sort($list);
                $path = array_pop($list);
            }
        } else {
            $paded = str_pad($version, 3, '0', STR_PAD_LEFT) . $extn;
            $path = substr($path, 0, -4) . $paded;
        }

        return $path;
    }

    public static function thumbs($report_id, $pages) {
        $thumbs = array();
        for ($i = 0; $i < $pages; $i++) {
            $suffix = sprintf("-preview-%d.png", $i);
            $tmp = self::mediastore(self::reportStorePath(self::uploaded($report_id, 'report', false, $suffix)));
            if (file_exists($tmp)) {
                thumb_map_url($tmp);
                $thumbs[] = $tmp;
            } else {
                return false;
            }
        }
        return $thumbs;
    }

    public static function reportstore($file) {
        return self::store(REPORTS_STORE, $file);
    }

    public static function mediastore($file) {
        return self::store(MEDIA_STORE, $file);
    }

    public static function damstore($file) {
        return self::store(DAM_STORE, $file);
    }

    public static function salesstore($type, $id) {
        $file = PROJECT_ROOT . sprintf('/assets/%s/%s-%d.pdf', $type, $type, $id);
        self::mkDir(dirname($file));
        return $file;
    }
    public static function store($type, $file) {
        $path = self::getprefix($type) . $file;
        self::mkDir(dirname($path));
        return $path;
    }

    public static function getprefix($type) {
        if (defined('AWS_ENABLED')) {
            $prefix = ($type === DAM_STORE) ?
                    AWS_DAM_STORE : (
                    $type === MEDIA_STORE ?
                            AWS_MEDIA_STORE : AWS_REPORTS_STORE
                    );
        } else {
            $prefix = PROJECT_ROOT . $type;
        }
        return $prefix;
    }

    public static function reportStorePath($absPath) {
        $stub = (defined('AWS_REPORTS_STORE')) ? AWS_REPORTS_STORE : PROJECT_ROOT . REPORTS_STORE;
        return str_replace($stub, "", $absPath);
    }

    public static function mediaStorePath($absPath) {
        $stub = (defined('AWS_MEDIA_STORE')) ? AWS_MEDIA_STORE : PROJECT_ROOT . MEDIA_STORE;
        return str_replace($stub, "", $absPath);
    }

    public static function damStorePath($absPath) {
        $stub = (defined('AWS_DAM_STORE')) ? AWS_DAM_STORE : PROJECT_ROOT . DAM_STORE;
        return str_replace($stub, "", $absPath);
    }

    public static function mediaUrl($path) {
        $storePath = self::mediaStorePath($path);
        return ((defined('AWS_MEDIA_URL')) ? AWS_MEDIA_URL : '.' . MEDIA_STORE) . $storePath;
    }

    public static function reportUrl($path, $download = false) {
        if (defined('AWS_ENABLED') && substr($path, 0, 5) == 's3://') {
            global $s3Client;
            $p = parse_url($path);
            $filename = basename($path);
            $args = ($download === false) ? array() :
                    array(
                'ResponseContentDisposition' => 'attachment; filename=' . $filename,
                'ResponseContentType' => 'application/pdf;charset=UTF-8'
            );
            $signedUrl = $s3Client->getObjectUrl($p['host'], $p['path']/* , '+90 minutes',$args */);
        } else {
            $signedUrl = str_replace(PROJECT_ROOT, PORTAL_PATH, $path);
        }
        return $signedUrl;
    }

    public static function finalDl($id, $type = "report", $secure = true) {
        $path = self::uploaded($id, $type, false, '-on-plain.pdf');
        if (defined('AWS_ENABLED') && $secure) {
            global $s3Client;
            $k = parse_url($path);
            return $s3Client->getObjectUrl($k['host'], $k['path'], '+10 minutes');
        } else {
            return $path;
        }
    }

    public static function mkDir($path) {
        if (!defined('AWS_ENABLED')) {
            if (!is_dir($path)) {
                mkdir($path, 0755, true);
            }
        }
    }

    public static function temppath() {
        $tmpPath = workspace::setup("/up_", true);
        return $tmpPath;
    }

}
