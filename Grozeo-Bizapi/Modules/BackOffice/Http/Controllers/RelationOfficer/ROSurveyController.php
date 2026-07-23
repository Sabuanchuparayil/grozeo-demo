<?php

namespace BackOffice\Http\Controllers\RelationOfficer;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Responses\{
    SuccessWithData,
    SuccessResponse,
    ErrorResponse
};
use BackOffice\Models\RelationOfficer\{
    ROUser,
    ROFinascopContacts,
    ROFinascopLeads,
    ROFinascopProspects,
    ROSurveyQuestions,
    ROSurveyAnswers,
    ROSurveyResponses
};
use BackOffice\Http\Requests\RelationOfficer\ROSurveyRequest;
use BackOffice\Http\Repositories\RelationOfficer\ROSurveyRepository;

class ROSurveyController
{
    public function __construct(){}

    public function questionAnswers($type = 'lead')
    {
        $questionnaire = ROSurveyQuestions::select('id', 'crm_type', 'question')->where('crm_type', $type)->with('answers:id,question_id,answer')->get();
        return new SuccessWithData($questionnaire);
    }

    public function submitAnswers(ROSurveyRequest $request)
    {
        $outs = [];
        $roUser = auth_user();
        $checkExistingSurvey = ROSurveyResponses::where([
            ['crm_user_id', $request->crm_user_id],
            ['crm_user_type', strtolower($request->type)],
            ['ro_id', (@$roUser->id) ? $roUser->id : 0]
        ])->first();
        if($checkExistingSurvey)
        {
            return new ErrorResponse('The survey has been already completed by this user');
        }
        else
        {
            $all = 0;
            $wrong = 0;
            $correct = 0;
            foreach ($request->responses as $response)
            {
                $all++;
                $answerCheck = ROSurveyAnswers::where([
                    ['question_id', $response['question']],
                    ['id', $response['answer']]
                ])->first();
                if($answerCheck)
                {
                    $surveyResponse = ROSurveyResponses::create([
                        'crm_user_id'   => $request->crm_user_id,
                        'crm_user_type' => strtolower($request->type),
                        'question_id'   => $response['question'],
                        'answer_id'     => $response['answer'],
                        'ro_id'         => (@$roUser->id) ? $roUser->id : 0,
                    ]);
                    if($surveyResponse)
                    {
                        $correct++;
                    }
                }
                else
                {
                    $wrong++;
                    $outs[] = "Question {$response['question']} for answer {$response['answer']} is invalid.";
                }
            }
            if(($all == $wrong) && ($all > 0))
            {
                return new ErrorResponse('All Questions and answers and invalid');
            }
            else
            {
                array_unshift($outs, "{$correct} correct responses updated.");
            }
            return new SuccessWithData($outs);
        }
    }
    public function surveyDetails($userID, $type = 'lead')
    {
        $roUser = auth_user();
        $responses = (new ROSurveyRepository)->getSurveyDetails($userID, $type, $roUser->id);
        return new SuccessWithData($responses);
    }
}