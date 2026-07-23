<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Responses\ErrorResponse;
use App\Http\Responses\SuccessResponse;
use App\Http\Responses\SuccessWithData;
use Illuminate\Support\Facades\Storage;

class ProductImageController extends Controller {

    
    public function imageUploadPost(Request $request)
    {
        $this->validate($request, [
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);
        $imageName = time() . '.' . $request->image->getClientOriginalExtension();
        $image = $request->file('image');
        // dd($image);

        $t = Storage::disk('s3')->put($imageName, file_get_contents($image), 'public');
        $imageName = Storage::disk('s3')->url($imageName);

        // return back()->with('success', 'Image Uploaded successfully.')->with('path', $imageName);
        return new SuccessWithData($imageName);
    }

}
