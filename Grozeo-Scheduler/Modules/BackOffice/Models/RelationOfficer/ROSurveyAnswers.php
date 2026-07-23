<?php

namespace BackOffice\Models\RelationOfficer;

use Illuminate\Database\Eloquent\Model;
use BackOffice\Models\RelationOfficer\ROSurveyQuestions;

class ROSurveyAnswers extends Model
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
    protected $table = 'crm_survey_options';

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
}
