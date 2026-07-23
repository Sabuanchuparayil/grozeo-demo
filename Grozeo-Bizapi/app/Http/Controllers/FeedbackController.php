<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Responses\SuccessResponse;
use App\Http\Requests\Feedback\FeedbackRequest;
use App\Http\Repositories\Feedback\FeedbackRepository;

class FeedbackController extends Controller
{
    protected $feedback;

    public function __construct(FeedbackRepository $feedback)
    {
        $this->feedback = $feedback;
    }

    public function store(FeedbackRequest $request)
    {
        $this->feedback->create($request->validated());
        return new SuccessResponse('Feedback send successfully');
    }
}
