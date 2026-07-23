<?php

namespace App\Http\Controllers;
use App\Models\Categorys;

use App\Models\ParentCategory;
use App\Models\MedicineCategory;
use Illuminate\Http\Request;
use App\Http\Responses\SuccessResponse;
use App\Http\Responses\ErrorResponse;
use App\Http\Responses\SuccessWithData;
use Illuminate\Support\Facades\DB;
use App\Http\Repositories\Category\CategoryRepository;
use Illuminate\Support\Facades\Cache;

class CategoryController extends Controller
{

    private $parentcategory;
    private $medicineCategory;
    protected $category;
    protected $catRepo;

    public function __construct(ParentCategory $parentcategory, MedicineCategory $medicineCategory, Categorys $sub, CategoryRepository $categoryRepo)
    {
        $this->parentcategory = $parentcategory;
        $this->medicineCategory = $medicineCategory;
        $this->category = $sub;
        $this->catRepo = $categoryRepo;
    }


    /**
     * Undocumented function
     *
     * @param [type] $datas
     * @return void
     */
    public function get()
    {
        $storegroupid = getHeaderStoreGroup();

        $data_category = $this->parentcategory->with(['subcategories' => function ($query) use ($storegroupid) {
            $query->with(['categories' => function ($squery) use ($storegroupid) {
                $squery->whereRaw('sub_category_id IN( SELECT DISTINCT product_category FROM finascop_stock_itemmaster fs INNER JOIN finascop_stock_branch_inventory bi ON bi.stit_id = fs.stit_ID INNER JOIN finascop_branch AS b ON b.br_ID=bi.branch_id ' . ($storegroupid <= 0 ? '' : ' WHERE b.br_storeGroup= ' . $storegroupid) .')');
                //$query->with('categories');
            }]);
        }])->where('status',1);//->get();
        if($storegroupid > 0){
            $data_category = $data_category->join('finascop_branch_group_business_type as bt', 'bt.business_type_id', 'mypha_productparent_category.parent_category_businessType')
            ->where('store_group_id', $storegroupid);
        }

        $data_category= $data_category->get();
        $medicine_data=$this->medicineCategory->with('submedicinecategory')->get();

        $data=array('type'=>"Product",'type_id'=>1,'product_category'=>$data_category);
        $data1=array('type'=>"Medicine",'type_id'=>2,'medicine_category'=>$medicine_data);
        $output_data=array($data,$data1);

        return new SuccessWithData(
            $data_category
        );
    }

    public function getCategoryMenuList()
    {
        $data = $this->catRepo->getCategoryList(" WHERE cattype != 4 or isInCategory=1 ");
        return new SuccessWithData(
            $data
        );
    }

    public function getCategoryMenuListNew()
    {
        $storegroupid = getHeaderStoreGroup();
        $cacheKey = "getAllCategories_{$storegroupid}";
        $cachedData = Cache::get($cacheKey);
        if($cachedData)
        {
            return new SuccessWithData($cachedData);
        }
        $data = DB::select("CALL getAllCategories(".$storegroupid.")");
        foreach ($data as $d)
        {
            if(@$d->attributes)
            {
                $d->attributes = json_decode($d->attributes);
            }
        }
        Cache::put($cacheKey, $data, 60);
	   return new SuccessWithData(
            $data
        );
    }

    public function GetVirtualSubcategories($vcid)
    {
        $data = DB::select("SELECT sc.sub_category_id, sc.sub_category, sc.sub_category_image, sc.STATUS, 3, c.parent_category AS pid, sc.main_category AS pcid, 0 AS isVirtualCategory 
FROM mypha_productsubcategory sc INNER JOIN mypha_productcategory c ON sc.main_category=c.category_id 
INNER JOIN finascop_stock_itemmaster p ON p.product_category=sc.sub_category_id
INNER JOIN retaline_vc_items vc ON vc.stit_id=p.stit_ID
WHERE sc.STATUS=1 AND vc.vc_id=" . $vcid . "  GROUP BY sc.sub_category_id, sc.sub_category");
	return new SuccessWithData(
            $data
        );

    }

    public function GetRelatedCategoryList($cid, $clevel, $business_type=-1, $retailtype=-1)
    {
        try
        {
            $storegroupid = getHeaderStoreGroup();
            $category_businessgroup_filter = ($storegroupid > 0? " and  parent_category IN (SELECT pc.parent_category_id FROM mypha_productparent_category pc INNER JOIN finascop_branch_group_business_type sbt ON pc.parent_category_businessType = sbt.business_type_id WHERE pc.STATUS=1 AND sbt.store_group_id= " . $storegroupid . ") " : "");

            $filtersql = '';
            if($clevel == 4)
            {
                $filtersql = ' where (cattype =4 OR (cattype=3 AND ( id IN 
    		        (SELECT p.product_category FROM finascop_stock_itemmaster p INNER JOIN retaline_vc_items vc ON vc.stit_id=p.stit_ID WHERE vc.vc_id = '.$cid.' ))))';
            }
            else
            {
                $cat = null;
                if($clevel == 2)
                    $cat = $this->category->where('category_id', $cid)->first();
                else if($clevel == 1)
                    $cat = $this->category->where('parent_category', $cid)->orderBy('category_name', 'asc')->first();
                else
                    $cat = $this->category->whereRaw('category_id = (select main_category from mypha_productsubcategory where sub_category_id = '. $cid .')')->first();

                $filtersql = ' where ((cattype=1 AND id='. $cat->parent_category . ') OR (cattype=2 AND parent_category_id = '. $cat->parent_category . ') OR (cattype in(3, 4) AND category_id ='. $cat->category_id . ') )';
            }


            $data = DB::select("SELECT * FROM(
                SELECT parent_category_id AS id, parent_category, image_url, STATUS, 1 AS cattype, -1 AS parent_category_id, -1 AS category_id, 0 AS isVirtualCategory, isHome, isInCategory FROM mypha_productparent_category pc " . ($storegroupid > 0 ? " INNER JOIN finascop_branch_group_business_type sbt ON pc.parent_category_businessType = sbt.business_type_id ": "") . " WHERE " . ($storegroupid > 0 ? "sbt.store_group_id= " . $storegroupid . " AND " : "") . " STATUS=1
                UNION ALL
                SELECT category_id, category_name, image_url, STATUS, 2, parent_category AS pid, -1 AS pcid, 0 AS isVirtualCategory, isHome, isInCategory FROM mypha_productcategory WHERE `STATUS` = '1' " . $category_businessgroup_filter . "
                UNION ALL
                SELECT sc.sub_category_id, sc.sub_category, sc.sub_category_image, sc.STATUS, 3, c.parent_category AS pid, sc.main_category AS pcid, 0 AS isVirtualCategory, sc.isHome, sc.isInCategory FROM mypha_productsubcategory sc INNER JOIN mypha_productcategory c ON sc.main_category=c.category_id WHERE sc.STATUS=1 " . $category_businessgroup_filter . "
                UNION ALL
                SELECT vc.vc_id, vc.vc_name, vc.image_url, vc.vc_status, 4, vc.vc_parentcategoryId AS pid, vc.vc_categoryId AS category_id, 1 AS isVirtualCategory, vc_isHome, vc_isInCategory  FROM retaline_virtual_category vc WHERE vc.vc_status=1 AND store_group_id in (0" . ($storegroupid > 0 ? ", " . $storegroupid : "") . ") " . ($storegroupid > 0 || $business_type > 0 ? " and EXISTS (SELECT sc.sub_category_id FROM mypha_productsubcategory sc INNER JOIN mypha_productcategory c ON sc.main_category=c.category_id INNER JOIN mypha_productparent_category pc ON c.parent_category = pc.parent_category_id  INNER JOIN finascop_branch_group_business_type sbt ON pc.parent_category_businessType = sbt.business_type_id WHERE pc.STATUS=1 " . ($storegroupid > 0 ? " AND sbt.store_group_id= " . $storegroupid : "") . ($business_type > 0 ? " AND pc.parent_category_businessType = " . $business_type : "") . " AND EXISTS (SELECT * FROM finascop_stock_itemmaster p INNER JOIN retaline_vc_items vci ON vci.stit_id=p.stit_ID WHERE p.product_category = sc.sub_category_id AND vci.vc_id = vc.vc_id))" : "") . ")categories " . $filtersql . " ORDER BY parent_category"); //cattype LIMIT 9");

                $count =0;    
            //return array($data, $count);
            return new SuccessWithData(
                $data
            );
        }
        catch (\Exception $e)
        {
            // info($e->getMessage());
            return new ErrorResponse("Not found"); 
        }
    }

    public function getFlatCategoryList(){
        return $this->catRepo->getCategoryList();
    }

    public function getVirtualCategoryItems($typeID, $vcID)
    {
        try
        {
            return new SuccessWithData($this->catRepo->getVirtualCategoryItems($typeID, $vcID));
        }
        catch(\Exception $e)
        {
            return new ErrorResponse("Operation failed");
        }
    }

}
