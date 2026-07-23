<?php

namespace App\Domains\Cart;

use App\Models\Cart;
use App\Product;
use App\Models\StockItemMaster;
use App\Exceptions\ProductNotFoundException;
use App\Exceptions\ProductExistInCartException;
use App\Http\Repositories\Item\Price;
use App\Models\Branch;

class PrepareCartItem
{
    protected $cart;

    protected $product;

    protected $item;

    public function __construct()
    {
        $this->cart = new Cart;
        //$this->product = new Product;
        $this->item = new StockItemMaster;
    }

    /**
     * Prepare item to be added to cart.
     *
     * @param array $data
     * @return array
     */
    public static function prepare($data)
    {


        return (new static)->prepareData($data);
    }

    /**
     * Prepare item to be added to cart.
     *
     * @param array $data
     * @return array
     */
protected function prepareData($data)
{
    $customerId = 0;
    if(auth()->check())
    {
        $customerId = auth()->user()->cust_id;
    }
    if($customerId > 0 || @$data['guest_token'] != "")
    {
        $brTypeId = $data['branch_type_id']? $data['branch_type_id'] : 1;
        // $customerId =43;
        $this->checkIfProductAlreadyAdded($data['cart_product_id'], $customerId, $data['order_method'], $data['cart_branch_id'], $brTypeId);

        $product = $this->getProduct($data['cart_product_id']);

        return $this->getFinalData($data, $product, $customerId);
    }
    return [];
}

    /**
     * Check if the product added already exist in DB.
     *
     * @param string $productId
     * @param string $customerId
     * @throws \App\Exceptions\ProductExistInCartException
     * @return void
     */
    protected function checkIfProductAlreadyAdded($data, $customerId, $branchtypeid=1)
    {
        $storegroupid = getHeaderStoreGroup();
        $where = [
            ['cart_customer_id', $customerId],
            ['cart_product_id', $data['cart_product_id']],
            ['order_method', $data['order_method']],
            ['cart_branch_id', $data['cart_branch_id']],
            ['branch_type_id', $branchtypeid],
            ['storegroup_id', $storegroupid],
        ];
        if(!auth()->check())
        {
            $where[] = ["guest_token", @$data['guest_token']];
        }
        $productExists = $this->cart->where($where)->exists();

        if ($productExists) {
            throw new ProductExistInCartException("Product exist in cart");
        }
    }

    /**
     * Get the product by id.
     *
     * @param string $id
     * @throws \App\Exceptions\ProductNotFoundException
     * @return \Illuminate\Database\Eloquent\Model
     */
    protected function getProduct($id)
    {
        $product = $this->item->find($id);

        return $product ?? $this->throwException();
    }

    /**
     * Prepare actual data to be stored
     *
     * @param array $data
     * @param \Illuminate\Database\Eloquent\Model $product
     * @param string $customerId
     * @return array
     */
    protected function getFinalData($data, $product, $customerId)
    {
        $data = (array)$data;
        $brTypeId = @$data['branch_type_id'] ? $data['branch_type_id'] : 1;
        $price = Price::findPrice(array($product->stit_ID), $data['cart_branch_id'], $brTypeId);
        $stitId = $product->stit_ID;
        $cos_nos= $product->cos_nos;
        $cos_nos = ($cos_nos>0)?$cos_nos:1;
        $storegroupid = getHeaderStoreGroup();

        $mrp = array_key_exists($stitId, $price['fpod_leastSKUmrp']) ? $price['fpod_leastSKUmrp'][$stitId] *$cos_nos : 0;
        
        //  $order_method= \Session::get('order_method');
        /*
        $order_method= 1;
        if($order_method==1){
             $selling_price = array_key_exists($stitId, $price['fpod_customerRateCouDel']) ? $price['fpod_customerRateCouDel'][$stitId] *$cos_nos: 0;
        }else{
        */      
        $sellingpriceField = ($storegroupid > 0 && @$price['branch_storegroup'] == $storegroupid ? 'selling_price' : (@$price['issponsered'] != 1 ? 'selling_price' : ($branch_type_id == 3 ? 'fpod_customerRateHmDel' : 'fpod_customerRateCouDel')));

	    // $sellingpriceField = ($storegroupid > 0) ? 'selling_price' : ($brTypeId == 3 ? 'fpod_customerRateHmDel' : 'fpod_customerRateCouDel');
        $selling_price = array_key_exists($stitId, $price[$sellingpriceField]) ? $price[$sellingpriceField][$stitId] *$cos_nos : 0;

           //$selling_price = array_key_exists($stitId, $price['fpod_customerRatePikup']) ? $price['fpod_customerRatePikup'][$stitId] *$cos_nos : 0;
        /*
        }
        */
        $taxValue = @$price['taxValue'][$stitId] ? $price['taxValue'][$stitId] : NULL;

       $percentage=($mrp>0)?((($mrp - $selling_price)*100) /$mrp):0 ;
       
        //   'cart_price' => $product->pdt_mrp,
          //  'cart_retail_price' => $product->pdt_mrp,
          //  'cart_sales_price' => $product->pdt_sale_rate,
         $mrp=round($mrp,2);
        $selling_price=round($selling_price,2);
        $percentage=round($percentage,2);
        return array_merge($data, [
            'cart_customer_id'      => $customerId,
            'cart_price'            => $mrp,
            'cart_retail_price'     => $mrp,
            'cart_sales_price'      => $selling_price,
            'cart_subcategory_id'   => $product->product_subcategory_id,
            'cart_package_type_id'  => $product->package_type_id,
            // 'cart_is_taxable'       => $product->product_tax ? 1 : 0,
            // 'cart_cgst'             => round($product->stit_GST/2, 2),
            // 'cart_sgst'             => round($product->stit_GST/2, 2),
            // 'cart_igst'             => $product->stit_GST,
            'cart_is_taxable'       => is_null($taxValue) ? 0 : 1,
            'cart_cgst'             => round($taxValue / 2, 2),
            'cart_sgst'             => round($taxValue / 2, 2),
            'cart_igst'             => $taxValue,
            'cart_discount'         => 0,
            'order_method'          => $data['order_method'],
            'cart_sku_id'           => null,
            'cart_status'           => 'added'
        ]);
    }

    /**
     * Method to throw an exception.
     *
     * @throws \App\Exceptions\ProductNotFoundException
     */
    protected function throwException()
    {
        throw new ProductNotFoundException("Invalid Product");
    }
}
