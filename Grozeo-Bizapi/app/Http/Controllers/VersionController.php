<?php

namespace App\Http\Controllers;

use App\Models\SystemConfig;
use Illuminate\Http\Request;
use App\Http\Requests\AppVersion;
use App\Http\Responses\SuccessWithData;

class VersionController extends Controller
{
    private $version;

    protected const ANDROID = 1;

    public function __construct(SystemConfig $version)
    {
        $this->version = $version;
    }
    public static function getVersion($type)
    {
       /* $type = $id == static::ANDROID
            ? 'Android_Min_Version'
            : 'Ios_Min_Version';*/

        $version = SystemConfig::where('cfg_Name', $type)
            ->where('cfg_Enabled', 'Yes')
            ->first(['cfg_Name', 'cfg_Value']);
        return new SuccessWithData(
            $version
        );
    }
}
