<?php

namespace BackOffice\Http\Repositories\RelationOfficer;

use BackOffice\Models\RelationOfficer\ROSurveyResponses;

class ROSurveyRepository
{
    public function getSurveyDetails($userID, $type, $roID)
	{
		return ROSurveyResponses::select('id','question_id','answer_id','ro_id','created_at')
        ->where([
            ['crm_user_id', $userID],
            ['crm_user_type', $type],
            ['ro_id', $roID]
        ])->with('question:id,question')->with('answer:id,answer')->get();
	}
}
