<?php

namespace App\Http\Repositories\Subcategory;

use App\Models\Category;
use App\Models\Subcategory;

class SubcategoryRepository
{
    protected $subcategory;

    protected $category;

    public function __construct(Subcategory $subcategory,Category $category)
    {
        $this->subcategory = $subcategory;
        $this->category = $category;
    }

    /**
     * Get all subcategories of a particular category
     *
     * @param string $id
     * @return \Illuminate\Support\Collection
     */
    public function get($id)
    {
        return $this->category
                    ->with('subcategories')
                    ->where('parent_category', $id)
                    ->get();
    }

}
