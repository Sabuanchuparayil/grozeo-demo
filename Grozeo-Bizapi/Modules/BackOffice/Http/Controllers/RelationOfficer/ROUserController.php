<?php

namespace BackOffice\Http\Controllers\RelationOfficer;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Responses\SuccessWithData;
use App\Http\Responses\SuccessResponse;
use App\Http\Responses\ErrorResponse;
use BackOffice\Models\RelationOfficer\ROUser;
use BackOffice\Models\RelationOfficer\ROFinascopContacts;
use BackOffice\Models\RelationOfficer\ROFinascopLeads;
use BackOffice\Models\RelationOfficer\ROFinascopProspects;

class ROUserController
{
    public function __construct()
    {
    }

    public function profile()
    {
        $roUser = auth_user();
        try
        {
            $user = ROUser::where('id', $roUser->id)
            ->with('areaEntries:id,areaName,areaLocation')
            ->first();
            return new SuccessWithData($user);
        }
        catch (\Exception $e)
        {
            return new ErrorResponse($e->getMessage()); 
        }
    }
}