<?php

namespace App\Http\Repositories\Category;

use App\Models\Category;
use App\Models\MstParentCategory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class CategoryRepository
{
    protected $category;

    protected $parentCategory;

    public function __construct(Category $category,MstParentCategory $parentCategory)
    {
        $this->category = $category;
        $this->parentCategory = $parentCategory;
    }

    public function get()
    {
        return $this->category
            ->where('parent_category', 0)
            ->with('subcategories.subcategories')
            ->get();
    }

    public function getCategory()
    {
        return $this->parentCategory->with(['subcategories' => function ($query) {
            $query->where('status', '1')
                ->with(['subcategory' => function ($qry) {
                    $qry->where('status', '1');
                }]);
        }])->select('parent_category_id','parent_category_id as category_id','parent_category as category_name','status', 'image_url as image_url')
            ->where('status', '1')
            ->get();
    }

    public function getCategoryListOLD($filter = "", $business_type=-1, $retailtype=-1, $sortorder="ORDER BY parent_category")
    {
        $customBusinessTypes="";
        if($business_type > 0){
            $customBusinessTypes="".$business_type;
        }
        else if($retailtype > 0)
        {
            $btypes = DB::table('retaline_business_category')->where('business_category_id', $retailtype)->first();
            if(isset($btypes) && isset($btypes->rbc_business_type))
            $customBusinessTypes= $btypes->rbc_business_type; //DB::select("SELECT rbc_business_type FROM retaline_business_category WHERE business_category_id=11 limit 1")->first();

        }

        $storegroupid = getHeaderStoreGroup();
        $category_businessgroup_filter = ($storegroupid > 0 || $customBusinessTypes != "" ? " and  parent_category IN (SELECT pc.parent_category_id FROM mypha_productparent_category pc INNER JOIN finascop_branch_group_business_type sbt ON pc.parent_category_businessType = sbt.business_type_id WHERE pc.STATUS=1 " . ($customBusinessTypes != "" ? " AND FIND_IN_SET(pc.parent_category_businessType, '" . $customBusinessTypes . "') > 0" : "") . ($storegroupid > 0 ? " AND sbt.store_group_id= " . $storegroupid : "") . ") " : "");

	    $sql = "SELECT *, (CASE WHEN cattype = 4 THEN 2 WHEN cattype = 2 THEN 2 ELSE 0 END) AS horder FROM(
        SELECT pc.parent_category_id AS id, pc.parent_category, pc.image_url, pc.STATUS, 1 AS cattype, -1 AS parent_category_id, -1 AS category_id, 0 AS isVirtualCategory, 0 AS displayOrder, 0 AS contentType, '' AS properties, pc.isHome, pc.isInCategory FROM mypha_productparent_category pc " . ($storegroupid > 0 ? " INNER JOIN finascop_branch_group_business_type sbt ON pc.parent_category_businessType = sbt.business_type_id ": "") . " WHERE " . ($storegroupid > 0 ? "sbt.store_group_id= " . $storegroupid . " AND " : "") . " STATUS=1 " .  ($customBusinessTypes != "" ? " AND FIND_IN_SET(pc.parent_category_businessType, '" . $customBusinessTypes . "') > 0" : "") . "  AND EXISTS ( SELECT * FROM finascop_stock_itemmaster fs INNER JOIN mypha_productsubcategory psc ON fs.product_category = psc.sub_category_id INNER JOIN mypha_productcategory pc2 ON psc.main_category = pc2.category_id INNER JOIN finascop_stock_branch_inventory bi ON bi.stit_id = fs.stit_ID INNER JOIN finascop_branch AS b ON b.br_ID=bi.branch_id  WHERE pc2.parent_category = pc.parent_category_id ". ($storegroupid >0 ? " AND  (b.br_storeGroup= " . $storegroupid . " OR bi.issponsered = 1)" : "") .")
        UNION ALL
        SELECT category_id, category_name, image_url, STATUS, 2, parent_category AS pid, -1 AS pcid, 0 AS isVirtualCategory, 0 AS displayOrder, 0 AS contentType, '' AS properties, isHome, isInCategory FROM mypha_productcategory pc WHERE `STATUS` = '1' AND EXISTS ( SELECT * FROM finascop_stock_itemmaster fs INNER JOIN mypha_productsubcategory psc ON fs.product_category = psc.sub_category_id INNER JOIN finascop_stock_branch_inventory bi ON bi.stit_id = fs.stit_ID INNER JOIN finascop_branch AS b ON b.br_ID=bi.branch_id  WHERE psc.main_category = pc.category_id ". ($storegroupid >0 ? " AND  (b.br_storeGroup= " . $storegroupid . " OR bi.issponsered = 1)" : "") .") " . $category_businessgroup_filter . "
        UNION ALL
        SELECT sc.sub_category_id, sc.sub_category, sc.sub_category_image, sc.STATUS, 3, c.parent_category AS pid, sc.main_category AS pcid, 0 AS isVirtualCategory, 0 AS displayOrder, 0 AS contentType, '' AS properties, sc.isHome, sc.isInCategory FROM mypha_productsubcategory sc INNER JOIN mypha_productcategory c ON sc.main_category=c.category_id WHERE sc.STATUS=1 " . $category_businessgroup_filter . " AND EXISTS ( SELECT * FROM finascop_stock_itemmaster fs INNER JOIN finascop_stock_branch_inventory bi ON bi.stit_id = fs.stit_ID INNER JOIN finascop_branch AS b ON b.br_ID=bi.branch_id  WHERE fs.product_category = sc.sub_category_id ". ($storegroupid >0 ? " AND  (b.br_storeGroup= " . $storegroupid . " OR bi.issponsered = 1)" : "") .")
        UNION ALL
        SELECT vc.vc_id, vc.vc_name, vc.image_url, vc.vc_status, 4, vc.vc_parentcategoryId AS pid, vc.vc_categoryId AS pcid, 1 AS isVirtualCategory, vc.displayOrder, vc.contentType, vc.properties, vc_isHome, vc_isInCategory FROM retaline_virtual_category vc WHERE vc.vc_status=1  AND store_group_id = " . $storegroupid . ($storegroupid > 0 || $customBusinessTypes != "" ? " and EXISTS (SELECT sc.sub_category_id FROM mypha_productsubcategory sc INNER JOIN mypha_productcategory c ON sc.main_category=c.category_id INNER JOIN mypha_productparent_category pc ON c.parent_category = pc.parent_category_id  INNER JOIN finascop_branch_group_business_type sbt ON pc.parent_category_businessType = sbt.business_type_id WHERE pc.STATUS=1 " . ($storegroupid > 0 ? " AND sbt.store_group_id= " . $storegroupid : "") . ($customBusinessTypes != "" ? " AND FIND_IN_SET(pc.parent_category_businessType, '" . $customBusinessTypes . "') > 0" : "") . " AND EXISTS (SELECT * FROM finascop_stock_itemmaster p INNER JOIN retaline_vc_items vci ON vci.stit_id=p.stit_ID WHERE p.product_category = sc.sub_category_id AND vci.vc_id = vc.vc_id))" : "") . "
        )categories " . $filter . " " . $sortorder;


        // SELECT vc.vc_id, vc.vc_name, vc.image_url, vc.vc_status, 4, vc.vc_parentcategoryId AS pid, vc.vc_categoryId AS pcid, 1 AS isVirtualCategory, vc_isHome, vc_isInCategory FROM retaline_virtual_category vc WHERE vc.vc_status=1 " . ($storegroupid > 0 || $business_type > 0 ? " and ( vc.vc_parentCategoryId= 0 OR vc.vc_parentCategoryId IN (SELECT pc.parent_category_id FROM mypha_productparent_category pc INNER JOIN finascop_branch_group_business_type sbt ON pc.parent_category_businessType = sbt.business_type_id WHERE 1=1 " . ($storegroupid > 0 ? " AND sbt.store_group_id= " . $storegroupid : "") . ($business_type > 0 ? " AND pc.parent_category_businessType = " . $business_type : "") . " ))" : "") . "

        $data = DB::select($sql); //cattype LIMIT 9");

            //$count =0;    
        return $data; //array($data, $count);
    }

    
    public function getCategoryList($filter = "", $business_type=-1, $retailtype=-1, $sortorder="ORDER BY parent_category")
    {
        try
        {
            $storegroupid = getHeaderStoreGroup();
            $cacheKey = "subCatListing_{$storegroupid}_{$retailtype}_{$business_type}";
            $cachedData = Cache::get($cacheKey);
            if($cachedData)
            {
                return $cachedData;
            }
            
            $sql = 'CALL subCategoryListing('.$storegroupid.','.$retailtype.','.$business_type.')';
            $categoryList = DB::select($sql);
            $parent = [];
            $parentsub = [];
            $subc = [];
            $virtual = [];
            foreach($categoryList as $cat)
            {
                if($cat->isVirtualCategory == 0)
                {
                    if(!array_search($cat->dept_id, array_column($parent, 'id')))
                    {
                        $parent[] = [
                            "id"                    => $cat->dept_id,
                            "parent_category"       => $cat->dept_name,
                            "image_url"             => $cat->dept_img,
                            "thumb_url"             => $cat->parent_thumb_url,
                            "STATUS"                => $cat->STATUS,
                            "cattype"               => 1,
                            "parent_category_id"    => -1,
                            "category_id"           => -1,
                            "isVirtualCategory"     => $cat->isVirtualCategory,
                            "displayOrder"		    => $cat->displayOrder,
                            "contentType"           => $cat->contentType, 
                            "properties"            => $cat->properties,
                            "isHome"                => $cat->dept_isHome,
                            "isInCategory"          => $cat->dept_isInCategory,
                            "horder"                => 0,
                            "isFeatured"            => $cat->isFeatured,
                            "isPreferred"           => $cat->isPreferred,
                            'attributes'            => json_decode($cat->attributes)   
                        ];
                    }
                    if(!array_search($cat->parent_id, array_column($parentsub, 'id')))
                    {
                        $parentsub[] = [
                            "id"                    => $cat->parent_id,
                            "parent_category"       => $cat->parent_name,
                            "image_url"             => $cat->parent_image,
                            "thumb_url"             => $cat->parent_thumb_url,
                            "STATUS"                => $cat->STATUS,
                            "cattype"               => 2,
                            "parent_category_id"    => $cat->dept_id,
                            "category_id"           => -1,
                            "isVirtualCategory"     => $cat->isVirtualCategory,
                            "displayOrder"		    => $cat->displayOrder,
                            "contentType"           => $cat->contentType, 
                            "properties"            => $cat->properties,
                            "isHome"                => $cat->parent_isHome,
                            "isInCategory"          => $cat->parent_isInCategory,
                            "horder"                => 2,
                            "isFeatured"            => $cat->isFeatured,
                            "isPreferred"           => $cat->isPreferred,
                            'attributes'            => json_decode($cat->attributes)   
                        ];
                    }
                    if(!array_search($cat->sub_category_id, array_column($subc, 'id')))
                    {
                        $subc[] = [
                            "id"                    => $cat->sub_category_id,
                            "parent_category"       => $cat->sub_category,
                            "image_url"             => $cat->sub_category_image,
                            "thumb_url"             => $cat->parent_thumb_url,
                            "STATUS"                => $cat->STATUS,
                            "cattype"               => 3,
                            "parent_category_id"    => $cat->dept_id,
                            "category_id"           => $cat->parent_id,
                            "isVirtualCategory"     => $cat->isVirtualCategory,
                            "displayOrder"		    => $cat->displayOrder,
                            "contentType"           => $cat->contentType, 
                            "properties"            => $cat->properties,
                            "isHome"                => $cat->isHome,
                            "isInCategory"          => $cat->isInCategory,
                            "horder"                => 0,
                            "isFeatured"            => $cat->isFeatured,
                            "isPreferred"           => $cat->isPreferred,
                            'attributes'            => json_decode($cat->attributes)   
                        ];
                    }
                }
                else
                {
                    $virtual[] = [
                        "id"                    => $cat->sub_category_id,
                        "parent_category"       => $cat->sub_category,
                        "image_url"             => $cat->sub_category_image,
                            "thumb_url"             => $cat->parent_thumb_url,
                        "STATUS"                => $cat->STATUS,
                        "cattype"               => 4,
                        "parent_category_id"    => $cat->dept_id,
                        "category_id"           => $cat->parent_id,
                        "isVirtualCategory"     => $cat->isVirtualCategory,
                        "displayOrder"		    => $cat->displayOrder,
                        "contentType"           => $cat->contentType, 
                        "properties"            => $cat->properties,
                        "isHome"                => $cat->isHome,
                        "isInCategory"          => $cat->isInCategory,
                        "horder"                => 2,
                        "isFeatured"            => $cat->isFeatured,
                        "isPreferred"           => $cat->isPreferred,
                        'attributes'            => json_decode($cat->attributes)   
                    ];
                }
            }
            $response = array_merge($virtual, array_unique($parent, SORT_REGULAR), array_unique($parentsub, SORT_REGULAR), $subc);

            Cache::put($cacheKey, $response, 60);
            return $response;
        }
        catch (\Exception $e)
        {
            // info("CategoryRepository getCategoryList ERROR");
            // info($e);
            return [];
        }
    }

    public function getVirtualCategoryItems($typeID, $vcID)
    {
        switch ($typeID)
        {
            case 2:
                $response = $this->getVirtualCatItems($vcID);
                break;
            case 3:
                $response = $this->getSubCatItems($vcID);
                break;
            case 4:
                $response = $this->getCatItems($vcID);
                break;
            case 5:
                $response = $this->getDepartmentItems($vcID);
                break;
            case 6:
                $response = $this->getRetailCatItems($vcID);
                break;
            case 7:
                $response = $this->getBusinessCatItems($vcID);
                break;
            /*case 8:
                $response = $this->getBannerItems($vcID);
                break;*/
            
            default:
                $response = DB::table('retaline_vc_items')->where('stpi_id', '<', 0);
                break;
        }
        return $response->paginate(10);
    }

    private function getVirtualCatItems($vcID)
    {
        return DB::table('retaline_virtual_category as rvc')
        ->select('rvc.vc_id', 'rvc.vc_name', 'rvc.image_url', 'rvc.thumb_url')
        ->join('retaline_vc_items as rvi', 'rvi.stit_id', 'rvc.vc_id')
        ->where([
            ['rvi.vc_id', $vcID],
            ['rvc.vc_status', 1]
        ]);
    }
    private function getSubCatItems($vcID)
    {
        return DB::table('mypha_productsubcategory as mpsc')
        ->select(['mpsc.sub_category_id', 'mpsc.sub_category', 'mpsc.sub_category_image', 'mpsc.hasRestaurantService', 'mpsc.hasAgeVerification'])
        ->join('retaline_vc_items as rvi', 'rvi.stit_id', 'mpsc.sub_category_id')
        ->where([
            ['rvi.vc_id', $vcID],
            ['mpsc.status', 1]
        ]);
    }
    private function getCatItems($vcID)
    {
        return DB::table('mypha_productcategory as mpc')
        ->select(['mpc.category_id', 'mpc.category_name', 'mpc.image_url', 'mpc.thumb_url', 'mpc.banner_image_url'])
        ->join('retaline_vc_items as rvi', 'rvi.stit_id', 'mpc.category_id')
        ->where([
            ['rvi.vc_id', $vcID],
            ['mpc.status', 1]
        ]);
    }
    private function getDepartmentItems($vcID)
    {
        return DB::table('mypha_productparent_category as mppc')
        ->select(['mppc.parent_category_id', 'mppc.parent_category', 'mppc.image_url', 'mppc.thumb_url'])
        ->join('retaline_vc_items as rvi', 'rvi.stit_id', 'mppc.parent_category_id')
        ->where([
            ['rvi.vc_id', $vcID],
            ['mppc.status', 1]
        ]);
    }
    private function getRetailCatItems($vcID)
    {
        return DB::table('finascop_business_type as fbt')
        ->select(['fbt.business_type_id', 'fbt.business_type_name'])
        ->join('retaline_vc_items as rvi', 'rvi.stit_id', 'fbt.business_type_id')
        ->where([
            ['rvi.vc_id', $vcID],
            ['fbt.status', 1]
        ]);
    }
    private function getBusinessCatItems($vcID)
    {
        return DB::table('retaline_business_category as rbc')
        ->select(['rbc.business_category_id', 'rbc.business_category_name', 'rbc.rbc_business_type', 'rbc.imageUrl'])
        ->join('retaline_vc_items as rvi', 'rvi.stit_id', 'rbc.business_category_id')
        ->where([
            ['rvi.vc_id', $vcID],
            ['rbc.status', 1]
        ]);
    }
    /*private function getBannerItems($vcID)
    {
        return DB::table('')->select('')
        ->join('retaline_vc_items as rvi', 'rvi.stit_id', '')
        ->where([
            ['rvi.vc_id', $vcID],
            []
        ]);
    }*/
}
