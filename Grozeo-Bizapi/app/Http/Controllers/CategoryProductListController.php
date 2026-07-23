<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\HomePage;
use App\Models\Categorys;
use App\Models\StockUniqueItem;
use App\Models\MstParentCategory;
use Illuminate\Http\Request;
use App\Http\Responses\SuccessWithData;


class CategoryProductListController extends Controller
{
    protected $homePage;
    protected $parentCategory;
    protected $sub;
    protected $subcategory;
    protected $uniqueItem;


    public function __construct(HomePage $homePage, MstParentCategory $parentCategory, Categorys $sub, Category $subcategory, StockUniqueItem $uniqueItem)
    {
        $this->homePage = $homePage;
        $this->subcategory = $subcategory;
        $this->sub = $sub;
        $this->uniqueItem = $uniqueItem;
        $this->parentCategory = $parentCategory;
    }

    public function productlist(Request $request)
    {
        $validatedData = $request->validate([
            'id' => 'required',

        ]);

        $type = HomePage::select('type')
            ->where('id',  $request['id'])
            ->value('type');

        $data = '';

        if ($type == "category") {

            $data = $this->parentCategory->select('parent_category_id', 'parent_category as parent_category_name', 'image_url', 'status')
                ->get();
            return new SuccessWithData($data);
        }

        if ($type == "SubCategory") {

            $data = $this->subcategory
                ->get();
        }

        return new SuccessWithData($data);
    }
}
