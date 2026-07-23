<?php

namespace App\Models\RelationOfficer;

use Illuminate\Database\Eloquent\Model;
use App\Models\RelationOfficer\ROSurveyQuestions;
use App\Models\RelationOfficer\ROSurveyAnswers;
use App\Models\RelationOfficer\ROUser;

class ROSurveyResponses extends Model
{
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'crm_survey_responses';

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['created_at', 'updated_at'];
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    
    public function question()
    {
        return $this->belongsTo(ROSurveyQuestions::class, 'question_id', 'id');
    }
    public function answer()
    {
        return $this->belongsTo(ROSurveyAnswers::class, 'answer_id', 'id');
    }
    public function ro()
    {
        return $this->belongsTo(ROUser::class, 'ro_id', 'id');
    }
}
