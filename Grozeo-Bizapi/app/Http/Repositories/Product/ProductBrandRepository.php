<?php

namespace App\Http\Repositories\Product;

use App\Models\Category;
use App\Models\Categorys;
use App\Models\ProductBrand;
use App\Models\StockUniqueItem;


class ProductBrandRepository
{
    protected $productbrand;
    protected $category;
    protected $subcategory;
    protected $stockUniqueItem;
    public function __construct(ProductBrand $productbrand,Categorys $category,Category $sub,StockUniqueItem $stockUniqueItem)
    {
        $this->productbrand = $productbrand;
        $this->category=$category;
        $this->subcategory=$sub;
        $this->stockUniqueItem=$stockUniqueItem;
    }

    public function getdetails()
    {

       
      //  return $this->productbrand->where('top_brand',1)->get();
      $data =  $this->productbrand->where('top_brand',1)
      ->select(["brand_id","brand_name","img_url"])
      ->take(9)
      ->get();

      $count=count($this->productbrand->where('top_brand',1)
        ->get());
        return array($data,$count);
    
    }
    public function getdetailWithCategory($category_id)
    { 
        
       
        
        
//         $brand_id=$this->stockUniqueItem->select('fsi_brand_id')->where('fsi_category_id',$category_id)->get()->toArray();
// if($brand_id)
// {
//     return $this->productbrand->where('brand_id',array_column($brand_id,'fsi_brand_id'))->get();

// }

 //dd($this->productbrand->where('brand_id',array_column($brand_id,'fsi_brand_id'))->get()->toArray());

        $categoryId=$this->category->select('category_id')->where('parent_category',$category_id)->get()->toArray();

      
        $subcategoryId=$this->subcategory->select('sub_category_id')->whereIn('main_category',array_column($categoryId,'category_id'))->get()->toArray();
        $brandId=$this->stockUniqueItem->select('fsi_brand_id')->whereIn('fsi_category_id',array_column($subcategoryId,'sub_category_id'))->get()->toArray();
        // dd($this->productbrand->where('brand_id',array_column($brandId,'fsi_brand_id'))->get()->toArray());
        if(!empty($brandId))
           return $this->productbrand->where('brand_id',array_column($brandId,'fsi_brand_id'))->get();
        else
            return [];
    }


    public function getdetailWithSubCategory($category_id)
    {

        $subcategoryId=$this->subcategory->select('sub_category_id')->where('main_category',$category_id)->get()->toArray();
        $brandId=$this->stockUniqueItem->select('fsi_brand_id')->whereIn('fsi_category_id',array_column($subcategoryId,'sub_category_id'))->get()->toArray();
        if(!empty($brandId))
          return $this->productbrand->where('brand_id',array_column($brandId,'fsi_brand_id'))->get();
        else
            return [];
    }

}
