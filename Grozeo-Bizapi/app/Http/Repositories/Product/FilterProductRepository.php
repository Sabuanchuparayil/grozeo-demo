<?php



namespace App\Http\Repositories\Product;

use App\Models\Category;
use App\Models\Categorys;
use App\Models\ProductBrand;
use App\Models\ParentCategory;
use App\Models\StockUniqueItem;
use App\Models\MedicineCategory;

class FilterProductRepository
{
    protected $productbrand;
    protected $category;
    protected $subcategory;
    protected $stockUniqueItem;
    private $parentcategory;
    private $medicineCategory;

    public function __construct(ProductBrand $productbrand,Categorys $category,Category $sub,StockUniqueItem $stockUniqueItem, ParentCategory $parentcategory, MedicineCategory $medicineCategory)
    {
        $this->productbrand = $productbrand;
        $this->category=$category;
        $this->subcategory=$sub;
        $this->stockUniqueItem=$stockUniqueItem;
        $this->parentcategory = $parentcategory;
        $this->medicineCategory = $medicineCategory;
    }

    public function getdetailWithCategory($category_id, $sort)
    {
        $categoryId = $this->category->select('category_id')->where('parent_category', $category_id)->get()->toArray();
        $subcategoryId = $this->subcategory->select('sub_category_id')->whereIn('main_category', array_column($categoryId, 'category_id'))->get()->toArray();
        $brandId = $this->stockUniqueItem->select('fsi_brand_id')->whereIn('fsi_category_id', array_column($subcategoryId, 'sub_category_id'))->get()->toArray();
        if (!empty($brandId))
            return $this->productbrand->where('brand_id', array_column($brandId, 'fsi_brand_id'))->orderBy('brand_name', $sort)->paginate(10);
        else
            return [];
    }

    public function productWithCategory($sort)
    {


        $data_category = $this->parentcategory->with(['subcategories' => function ($query) {
            $query->with('categories');
        }])->orderBy('parent_category', $sort)->paginate(10);



        $medicine_data=$this->medicineCategory->with('submedicinecategory')->orderBy('category_name', $sort)->paginate(10);

        $data=array('type'=>"Product",'type_id'=>1,'product_category'=>$data_category);
        $data1=array('type'=>"Medicine",'type_id'=>2,'medicine_category'=>$medicine_data);
        $output_data=array($data,$data1);

        return $data_category;
    }
}
