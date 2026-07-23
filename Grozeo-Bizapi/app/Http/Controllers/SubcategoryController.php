<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\SubCategory;
use Illuminate\Http\Request;
use App\Http\Responses\SuccessWithData;

class SubcategoryController extends Controller
{
    protected $subcategory;
    protected $category;


    public function __construct(SubCategory $category,Category $subcategory)
    {
        $this->subcategory = $subcategory;
        $this->category = $category;
    }

    public function get($id)
    {

        $data=$this->category
                    ->with('categories')
                    ->where('parent_category', $id)
                    ->get();
        return new SuccessWithData(
            $data
        );
    }
}
