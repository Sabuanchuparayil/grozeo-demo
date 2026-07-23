# Inner Subcategory Screen


### Details

| Method | Uri   | Authorization |
| : |   :-   |  :  |
| POST | `/innersubcategoryscreen` | NO |


Case: 1(Inner subcategory Screen details)
************
### Request

```json
{
 "requested_id":"1",
 "branch_id":"1",
 "order_method":"1",

 "sort": {
       "price": ""
   },
  "filter": {
       "category": [],
       "brands": [],
       "price_range":[]
   }

}

 
```
### Response

```json

{
    "status": "ok",
    "data": [
        {
            "id": 22,
            "screen": "InnerSubcategory",
            "type": "product",
            "type_id": 9,
            "image_url": "https://devassetbrm.s3-ap-southeast-1.amazonaws.com/product/main/f52a29ff-b637-4db1-ad8e-04073d73f396",
            "description": "Leorem hayr tfhfh v ff",
            "title": "Product",
            "background_img": "https://devassetbrm.s3-ap-southeast-1.amazonaws.com/product/main/f52a29ff-b637-4db1-ad8e-04073d73f396",
            "is_active": 1,
            "sub_id": 0,
            "order": 2,
            "delivery_type": 1,
            "value": [
                {
                    "fsi_uid": 4,
                    "item_group_id": 4,
                    "item_name": "Vaporub",
                    "brand_name": "Vicks",
                    "category_id": 1,
                    "category_name": "Nasal Congestion",
                    "variant": "lite",
                    "isMedicine": 0,
                    "item_master": [
                        {
                            "stit_ID": 2,
                            "quantity": "100 ml",
                            "stit_fsiuid": 4,
                            "stock_available": 0,
                            "selling_prize": 0,
                            "isMedicine": 0,
                            "mrp": 0,
                            "main_image": [
                                {
                                    "id": 138,
                                    "product_id": 2,
                                    "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-64753f49-e9c7-432d-b614-64fc82612a1c.jpg",
                                    "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-64753f49-e9c7-432d-b614-64fc82612a1c.jpg"
                                }
                            ],
                            "default_value": 0,
                            "selling_price": 0,
                            "godown_itemId": 466
                        }
                    ]
                },
                {
                    "fsi_uid": 6,
                    "item_group_id": 6,
                    "item_name": "Inhailer",
                    "brand_name": "Vicks",
                    "category_id": 1,
                    "category_name": "Nasal Congestion",
                    "variant": "Inhaller",
                    "isMedicine": 0,
                    "item_master": [
                        {
                            "stit_ID": 1,
                            "quantity": "50gm",
                            "stit_fsiuid": 6,
                            "stock_available": 0,
                            "selling_prize": 0,
                            "isMedicine": 0,
                            "mrp": 0,
                            "main_image": [
                                {
                                    "id": 133,
                                    "product_id": 1,
                                    "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-066cd2df-16fa-44ab-8b93-a63cbc9f984b.jpg",
                                    "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-066cd2df-16fa-44ab-8b93-a63cbc9f984b.jpg"
                                }
                            ],
                            "default_value": 0,
                            "selling_price": 0,
                            "godown_itemId": 525
                        }
                    ]
                }
            ],
            "total_count": 2,
            "min_count": 9,
            "pagenate_details": {
                "currentpage": 1,
                "first_page_url": "http://localhost/pharmacy/public/api/innersubcategoryscreen?page=1",
                "from": 1,
                "last_page": 1,
                "last_page_url": "http://localhost/pharmacy/public/api/innersubcategoryscreen?page=1",
                "next_page_url": null,
                "path": "http://localhost/pharmacy/public/api/innersubcategoryscreen",
                "per_page": 10,
                "prev_page_url": null,
                "to": 2,
                "total": 2
            }
        },
        {
            "id": 23,
            "screen": "InnerSubcategory",
            "type": "SubCategory",
            "type_id": 4,
            "image_url": "https://devassetbrm.s3-ap-southeast-1.amazonaws.com/product/main/f52a29ff-b637-4db1-ad8e-04073d73f396",
            "description": "Leorem hayr tfhfh v ff",
            "title": "Browse by Category",
            "background_img": "https://devassetbrm.s3-ap-southeast-1.amazonaws.com/product/main/f52a29ff-b637-4db1-ad8e-04073d73f396",
            "is_active": 1,
            "sub_id": 0,
            "order": 1,
            "delivery_type": 1,
            "value": [
                {
                    "sub_category_id": 1,
                    "sub_category": "Nasal Congestion",
                    "sub_category_image": "",
                    "status": "1",
                    "isMedicine": 0,
                    "main_category": 1,
                    "subcat_bmd_id": null,
                    "subcat_bmd_name": null
                },
                {
                    "sub_category_id": 2,
                    "sub_category": "cough",
                    "sub_category_image": "",
                    "status": "1",
                    "isMedicine": 0,
                    "main_category": 1,
                    "subcat_bmd_id": null,
                    "subcat_bmd_name": null
                },
                {
                    "sub_category_id": 5,
                    "sub_category": "Chest Rubs & Balms",
                    "sub_category_image": "",
                    "status": "1",
                    "isMedicine": 0,
                    "main_category": 1,
                    "subcat_bmd_id": null,
                    "subcat_bmd_name": null
                },
                {
                    "sub_category_id": 7,
                    "sub_category": "Vaporizers",
                    "sub_category_image": "",
                    "status": "1",
                    "isMedicine": 0,
                    "main_category": 1,
                    "subcat_bmd_id": null,
                    "subcat_bmd_name": null
                },
                {
                    "sub_category_id": 9,
                    "sub_category": "Sore Throat",
                    "sub_category_image": "",
                    "status": "1",
                    "isMedicine": 0,
                    "main_category": 1,
                    "subcat_bmd_id": null,
                    "subcat_bmd_name": null
                }
            ],
            "total_count": 5,
            "min_count": 9
        }
    ]
}
```
Case:2(Sort and filter)
************************
### Request

```json
{
 "requested_id":"1",
 "branch_id":"1",
 "order_method":"1",

 "sort": {
       "price": ""
   },
  "filter": {
       "category": [],
       "brands": [],
       "price_range":["0","100"]
   }

}

 

```
### Response


```json
{
    "status": "ok",
    "data": [
        {
            "id": 22,
            "screen": "InnerSubcategory",
            "type": "product",
            "type_id": 9,
            "image_url": "https://devassetbrm.s3-ap-southeast-1.amazonaws.com/product/main/f52a29ff-b637-4db1-ad8e-04073d73f396",
            "description": "Leorem hayr tfhfh v ff",
            "title": "Product",
            "background_img": "https://devassetbrm.s3-ap-southeast-1.amazonaws.com/product/main/f52a29ff-b637-4db1-ad8e-04073d73f396",
            "is_active": 1,
            "sub_id": 0,
            "order": 2,
            "delivery_type": 1,
            "value": [
                {
                    "fsi_uid": 4,
                    "item_group_id": 4,
                    "item_name": "Vaporub",
                    "brand_name": "Vicks",
                    "category_id": 1,
                    "category_name": "Nasal Congestion",
                    "variant": "lite",
                    "isMedicine": 0,
                    "item_master": [
                        {
                            "stit_ID": 2,
                            "quantity": "100 ml",
                            "stit_fsiuid": 4,
                            "stock_available": 0,
                            "selling_prize": 0,
                            "isMedicine": 0,
                            "mrp": 0,
                            "main_image": [
                                {
                                    "id": 138,
                                    "product_id": 2,
                                    "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-64753f49-e9c7-432d-b614-64fc82612a1c.jpg",
                                    "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-64753f49-e9c7-432d-b614-64fc82612a1c.jpg"
                                }
                            ],
                            "default_value": 0,
                            "selling_price": 0,
                            "godown_itemId": 36
                        }
                    ]
                },
                {
                    "fsi_uid": 6,
                    "item_group_id": 6,
                    "item_name": "Inhailer",
                    "brand_name": "Vicks",
                    "category_id": 1,
                    "category_name": "Nasal Congestion",
                    "variant": "Inhaller",
                    "isMedicine": 0,
                    "item_master": [
                        {
                            "stit_ID": 1,
                            "quantity": "50gm",
                            "stit_fsiuid": 6,
                            "stock_available": 0,
                            "selling_prize": 0,
                            "isMedicine": 0,
                            "mrp": 0,
                            "main_image": [
                                {
                                    "id": 133,
                                    "product_id": 1,
                                    "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-066cd2df-16fa-44ab-8b93-a63cbc9f984b.jpg",
                                    "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-066cd2df-16fa-44ab-8b93-a63cbc9f984b.jpg"
                                }
                            ],
                            "default_value": 0,
                            "selling_price": 0,
                            "godown_itemId": 145
                        }
                    ]
                }
            ],
            "total_count": 2,
            "min_count": 9,
            "pagenate_details": {
                "currentpage": 1,
                "first_page_url": "http://localhost/pharmacy/public/api/innersubcategoryscreen?page=1",
                "from": 1,
                "last_page": 1,
                "last_page_url": "http://localhost/pharmacy/public/api/innersubcategoryscreen?page=1",
                "next_page_url": null,
                "path": "http://localhost/pharmacy/public/api/innersubcategoryscreen",
                "per_page": 10,
                "prev_page_url": null,
                "to": 2,
                "total": 2
            }
        },
        {
            "id": 23,
            "screen": "InnerSubcategory",
            "type": "SubCategory",
            "type_id": 4,
            "image_url": "https://devassetbrm.s3-ap-southeast-1.amazonaws.com/product/main/f52a29ff-b637-4db1-ad8e-04073d73f396",
            "description": "Leorem hayr tfhfh v ff",
            "title": "Browse by Category",
            "background_img": "https://devassetbrm.s3-ap-southeast-1.amazonaws.com/product/main/f52a29ff-b637-4db1-ad8e-04073d73f396",
            "is_active": 1,
            "sub_id": 0,
            "order": 1,
            "delivery_type": 1,
            "value": [
                {
                    "sub_category_id": 1,
                    "sub_category": "Nasal Congestion",
                    "sub_category_image": "",
                    "status": "1",
                    "isMedicine": 0,
                    "main_category": 1,
                    "subcat_bmd_id": null,
                    "subcat_bmd_name": null
                },
                {
                    "sub_category_id": 2,
                    "sub_category": "cough",
                    "sub_category_image": "",
                    "status": "1",
                    "isMedicine": 0,
                    "main_category": 1,
                    "subcat_bmd_id": null,
                    "subcat_bmd_name": null
                },
                {
                    "sub_category_id": 5,
                    "sub_category": "Chest Rubs & Balms",
                    "sub_category_image": "",
                    "status": "1",
                    "isMedicine": 0,
                    "main_category": 1,
                    "subcat_bmd_id": null,
                    "subcat_bmd_name": null
                },
                {
                    "sub_category_id": 7,
                    "sub_category": "Vaporizers",
                    "sub_category_image": "",
                    "status": "1",
                    "isMedicine": 0,
                    "main_category": 1,
                    "subcat_bmd_id": null,
                    "subcat_bmd_name": null
                },
                {
                    "sub_category_id": 9,
                    "sub_category": "Sore Throat",
                    "sub_category_image": "",
                    "status": "1",
                    "isMedicine": 0,
                    "main_category": 1,
                    "subcat_bmd_id": null,
                    "subcat_bmd_name": null
                }
            ],
            "total_count": 5,
            "min_count": 9
        }
    ]
}

```
