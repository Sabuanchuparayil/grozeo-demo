<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Http\Responses\SuccessResponse;
use App\Http\Responses\SuccessWithData;
use App\Http\Requests\About\FeedbackRequest;
use App\Http\Repositories\About\AboutRepository;

class AboutController extends Controller
{
    private $about;

    public function __construct(AboutRepository $about)
    {
        $this->about = $about;
    }

    public function store(FeedbackRequest $request)
    {
        $this->about->store($request->validated());
       return new SuccessResponse(
            "Saved successfully."
        );
    }

    public function get()
    {
        return new SuccessWithData(
            $this->about->get()
        );
    }

    public function getPages()
    {
        return new SuccessWithData(
            $this->about->getPages()
        );
    }
    
}
