# Brand Screen

---
Brand Screen

### Details

| Method | Uri   | Authorization |
| : |   :-   |  :  |
| POST | `brandscreen` | NO |


Case:1(brand details)
*******************

### Request

```json
{
 "requested_id":"23",
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
            "id": 15,
            "screen": "Brand",
            "type": "category",
            "type_id": 2,
            "image_url": "https://devassetbrm.s3-ap-southeast-1.amazonaws.com/product/main/f52a29ff-b637-4db1-ad8e-04073d73f396",
            "description": "The Leoremnhbd eterbc fff",
            "title": "Shop by Category",
            "background_img": "https://devassetbrm.s3-ap-southeast-1.amazonaws.com/product/main/f52a29ff-b637-4db1-ad8e-04073d73f396",
            "is_active": 1,
            "sub_id": 0,
            "order": 1,
            "delivery_type": 1,
            "value": [
                {
                    "parent_category_id": 1,
                    "parent_category_name": "Winter Care",
                    "image_url": "http://dev.admin.mypharmacy.velosit.in/resources/gogos/images(9).jpg",
                    "status": "1"
                },
                {
                    "parent_category_id": 2,
                    "parent_category_name": "Featured",
                    "image_url": "http://dev.admin.mypharmacy.velosit.in/resources/gogos/featured.png",
                    "status": "1"
                },
                {
                    "parent_category_id": 3,
                    "parent_category_name": "Diabetes",
                    "image_url": "http://dev.admin.mypharmacy.velosit.in/resources/gogos/images(13).jpg",
                    "status": "1"
                },
                {
                    "parent_category_id": 4,
                    "parent_category_name": "Personal Care",
                    "image_url": "http://dev.admin.mypharmacy.velosit.in/resources/gogos/images(11).jpg",
                    "status": "1"
                },
                {
                    "parent_category_id": 5,
                    "parent_category_name": "Fitness & Supplements",
                    "image_url": "http://dev.admin.mypharmacy.velosit.in/resources/gogos/fitness.png",
                    "status": "1"
                },
                {
                    "parent_category_id": 6,
                    "parent_category_name": "Healthcare Devices",
                    "image_url": "http://dev.admin.mypharmacy.velosit.in/resources/gogos/images(1).jpg",
                    "status": "1"
                },
                {
                    "parent_category_id": 7,
                    "parent_category_name": "Health Conditions",
                    "image_url": "http://dev.admin.mypharmacy.velosit.in/resources/gogos/healthcondition.png",
                    "status": "1"
                },
                {
                    "parent_category_id": 8,
                    "parent_category_name": "Ayurveda Products",
                    "image_url": "http://dev.admin.mypharmacy.velosit.in/resources/gogos/ayurvedha.png",
                    "status": "1"
                },
                {
                    "parent_category_id": 9,
                    "parent_category_name": "Homeopathy",
                    "image_url": "https://gogomedsdev.s3-ap-southeast-1.amazonaws.com/category/935f0ecd-1849-4ed6-a7ac-5bdbb64af7e3.jpg",
                    "status": "1"
                }
            ],
            "total_count": 11,
            "min_count": 9
        },
        {
            "id": 16,
            "screen": "Brand",
            "type": "product",
            "type_id": 9,
            "image_url": "",
            "description": "",
            "title": "All products",
            "background_img": "",
            "is_active": 1,
            "sub_id": null,
            "order": 3,
            "delivery_type": 1,
            "value": [
                {
                    "fsi_uid": 13,
                    "item_group_id": 13,
                    "item_name": "Koflet Lozenges",
                    "brand_name": "Himalaya",
                    "category_id": 2,
                    "category_name": "cough",
                    "variant": "packet",
                    "isMedicine": 0,
                    "item_master": [
                        {
                            "stit_ID": 13,
                            "quantity": "10 lozenges",
                            "stit_fsiuid": 13,
                            "stock_available": 100,
                            "selling_prize": 189.2,
                            "mrp": 220,
                            "isMedicine": 0,
                            "main_image": [
                                {
                                    "id": 263,
                                    "product_id": 13,
                                    "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-380b9d66-0b8d-4b80-a6bb-78b2f5700af7.jpg",
                                    "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-380b9d66-0b8d-4b80-a6bb-78b2f5700af7.jpg"
                                }
                            ],
                            "default_value": 1,
                            "selling_price": 189.2,
                            "godown_itemId": 790
                        },
                        {
                            "stit_ID": 14,
                            "quantity": "200 lozenges",
                            "stit_fsiuid": 13,
                            "stock_available": 0,
                            "selling_prize": 0,
                            "mrp": 0,
                            "isMedicine": 0,
                            "main_image": [
                                {
                                    "id": 261,
                                    "product_id": 14,
                                    "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-b1485f1f-9865-4a8d-a4cc-543df4d0c951.jpg",
                                    "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-b1485f1f-9865-4a8d-a4cc-543df4d0c951.jpg"
                                }
                            ],
                            "default_value": 0,
                            "selling_price": 0,
                            "godown_itemId": 794
                        }
                    ]
                },
                {
                    "fsi_uid": 14,
                    "item_group_id": 14,
                    "item_name": "Koflet Syrup ",
                    "brand_name": "Himalaya",
                    "category_id": 2,
                    "category_name": "cough",
                    "variant": "syrup",
                    "isMedicine": 0,
                    "item_master": [
                        {
                            "stit_ID": 15,
                            "quantity": "100ml",
                            "stit_fsiuid": 14,
                            "stock_available": 0,
                            "selling_prize": 0,
                            "mrp": 0,
                            "isMedicine": 0,
                            "main_image": [
                                {
                                    "id": 124,
                                    "product_id": 15,
                                    "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-7e68203f-9d1e-4799-b598-379deaf65989.jpg",
                                    "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-7e68203f-9d1e-4799-b598-379deaf65989.jpg"
                                }
                            ],
                            "default_value": 0,
                            "selling_price": 0,
                            "godown_itemId": 648
                        }
                    ]
                },
                {
                    "fsi_uid": 44,
                    "item_group_id": 44,
                    "item_name": "DIOCID",
                    "brand_name": "OMEPRAZOLE",
                    "category_id": 1,
                    "category_name": "Antacids, Antireflux Agents & Antiulcerants",
                    "variant": "20mg Capsule",
                    "isMedicine": 1,
                    "item_master": [
                        {
                            "stit_ID": 44,
                            "quantity": "",
                            "stit_fsiuid": 44,
                            "stock_available": 0,
                            "selling_prize": 0,
                            "mrp": 0,
                            "isMedicine": 1,
                            "main_image": [
                                {
                                    "id": 204,
                                    "product_id": 44,
                                    "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-7ee2dc09-dd17-4a24-ab84-0389701bb001.jpg",
                                    "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-7ee2dc09-dd17-4a24-ab84-0389701bb001.jpg"
                                }
                            ],
                            "default_value": 0,
                            "selling_price": 0,
                            "godown_itemId": 320
                        }
                    ]
                },
                {
                    "fsi_uid": 55,
                    "item_group_id": 55,
                    "item_name": "ESTOM",
                    "brand_name": "OMEPRAZOLE",
                    "category_id": 1,
                    "category_name": "Antacids, Antireflux Agents & Antiulcerants",
                    "variant": "40mg Capsule",
                    "isMedicine": 1,
                    "item_master": [
                        {
                            "stit_ID": 56,
                            "quantity": "",
                            "stit_fsiuid": 55,
                            "stock_available": 0,
                            "selling_prize": 0,
                            "mrp": 0,
                            "isMedicine": 1,
                            "main_image": [
                                {
                                    "id": 203,
                                    "product_id": 56,
                                    "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-5e583121-798b-4f75-91e4-8be9a2576ff4.jpg",
                                    "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-5e583121-798b-4f75-91e4-8be9a2576ff4.jpg"
                                }
                            ],
                            "default_value": 0,
                            "selling_price": 0,
                            "godown_itemId": 775
                        }
                    ]
                },
                {
                    "fsi_uid": 114,
                    "item_group_id": 114,
                    "item_name": "Neem Leaf Juice",
                    "brand_name": "Himalaya",
                    "category_id": 25,
                    "category_name": "Facewash & Cleanser",
                    "variant": "",
                    "isMedicine": 0,
                    "item_master": [
                        {
                            "stit_ID": 113,
                            "quantity": "",
                            "stit_fsiuid": 114,
                            "stock_available": 0,
                            "selling_prize": 0,
                            "mrp": 0,
                            "isMedicine": 0,
                            "main_image": [
                                {
                                    "id": 6,
                                    "product_id": 113,
                                    "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-07516323-5472-4908-8cd6-d3bcb09bfe6a.jpg",
                                    "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-07516323-5472-4908-8cd6-d3bcb09bfe6a.jpg"
                                }
                            ],
                            "default_value": 0,
                            "selling_price": 0,
                            "godown_itemId": 171
                        }
                    ]
                },
                {
                    "fsi_uid": 118,
                    "item_group_id": 118,
                    "item_name": "Wipe Clear Ache Lotion",
                    "brand_name": "Himalaya",
                    "category_id": 72,
                    "category_name": "Beauty Supplements",
                    "variant": "Cream",
                    "isMedicine": 0,
                    "item_master": [
                        {
                            "stit_ID": 115,
                            "quantity": "500 gm",
                            "stit_fsiuid": 118,
                            "stock_available": 0,
                            "selling_prize": 0,
                            "mrp": 0,
                            "isMedicine": 0,
                            "main_image": [
                                {
                                    "id": 1,
                                    "product_id": 115,
                                    "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-cb8767db-7220-4908-805b-04f14010ad75.jpg",
                                    "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-cb8767db-7220-4908-805b-04f14010ad75.jpg"
                                }
                            ],
                            "default_value": 0,
                            "selling_price": 0,
                            "godown_itemId": 923
                        }
                    ]
                }
            ],
            "total_count": 6,
            "min_count": 9,
            "pagenate_details": {
                "currentpage": 1,
                "first_page_url": "http://localhost/pharmacy/public/api/brandscreen?page=1",
                "from": 1,
                "last_page": 1,
                "last_page_url": "http://localhost/pharmacy/public/api/brandscreen?page=1",
                "next_page_url": null,
                "path": "http://localhost/pharmacy/public/api/brandscreen",
                "per_page": 10,
                "prev_page_url": null,
                "to": 6,
                "total": 6
            }
        },
        {
            "id": 17,
            "screen": "Brand",
            "type": "SubCategory",
            "type_id": 4,
            "image_url": "https://devassetbrm.s3-ap-southeast-1.amazonaws.com/product/main/f52a29ff-b637-4db1-ad8e-04073d73f396",
            "description": "",
            "title": "Browse by Category",
            "background_img": "https://devassetbrm.s3-ap-southeast-1.amazonaws.com/product/main/f52a29ff-b637-4db1-ad8e-04073d73f396",
            "is_active": 1,
            "sub_id": 0,
            "order": 2,
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
                    "sub_category_id": 3,
                    "sub_category": "OrganicIndia",
                    "sub_category_image": "",
                    "status": "1",
                    "isMedicine": 0,
                    "main_category": 7,
                    "subcat_bmd_id": null,
                    "subcat_bmd_name": null
                },
                {
                    "sub_category_id": 4,
                    "sub_category": "Accu-Check",
                    "sub_category_image": "",
                    "status": "1",
                    "isMedicine": 0,
                    "main_category": 7,
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
                    "sub_category_id": 6,
                    "sub_category": "Himalaya Wellness",
                    "sub_category_image": "",
                    "status": "1",
                    "isMedicine": 0,
                    "main_category": 7,
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
                    "sub_category_id": 8,
                    "sub_category": "OneTouch",
                    "sub_category_image": "",
                    "status": "1",
                    "isMedicine": 0,
                    "main_category": 7,
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
            "total_count": 152,
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
 "requested_id":"23",
 "branch_id":"1",
 "order_method":"1",

 "sort": {
       "price": "2"
   },
  "filter": {
       "category": ["1"],
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
            "id": 15,
            "screen": "Brand",
            "type": "category",
            "type_id": 2,
            "image_url": "https://devassetbrm.s3-ap-southeast-1.amazonaws.com/product/main/f52a29ff-b637-4db1-ad8e-04073d73f396",
            "description": "The Leoremnhbd eterbc fff",
            "title": "Shop by Category",
            "background_img": "https://devassetbrm.s3-ap-southeast-1.amazonaws.com/product/main/f52a29ff-b637-4db1-ad8e-04073d73f396",
            "is_active": 1,
            "sub_id": 0,
            "order": 1,
            "delivery_type": 1,
            "value": [
                {
                    "parent_category_id": 1,
                    "parent_category_name": "Winter Care",
                    "image_url": "http://dev.admin.mypharmacy.velosit.in/resources/gogos/images(9).jpg",
                    "status": "1"
                },
                {
                    "parent_category_id": 2,
                    "parent_category_name": "Featured",
                    "image_url": "http://dev.admin.mypharmacy.velosit.in/resources/gogos/featured.png",
                    "status": "1"
                },
                {
                    "parent_category_id": 3,
                    "parent_category_name": "Diabetes",
                    "image_url": "http://dev.admin.mypharmacy.velosit.in/resources/gogos/images(13).jpg",
                    "status": "1"
                },
                {
                    "parent_category_id": 4,
                    "parent_category_name": "Personal Care",
                    "image_url": "http://dev.admin.mypharmacy.velosit.in/resources/gogos/images(11).jpg",
                    "status": "1"
                },
                {
                    "parent_category_id": 5,
                    "parent_category_name": "Fitness & Supplements",
                    "image_url": "http://dev.admin.mypharmacy.velosit.in/resources/gogos/fitness.png",
                    "status": "1"
                },
                {
                    "parent_category_id": 6,
                    "parent_category_name": "Healthcare Devices",
                    "image_url": "http://dev.admin.mypharmacy.velosit.in/resources/gogos/images(1).jpg",
                    "status": "1"
                },
                {
                    "parent_category_id": 7,
                    "parent_category_name": "Health Conditions",
                    "image_url": "http://dev.admin.mypharmacy.velosit.in/resources/gogos/healthcondition.png",
                    "status": "1"
                },
                {
                    "parent_category_id": 8,
                    "parent_category_name": "Ayurveda Products",
                    "image_url": "http://dev.admin.mypharmacy.velosit.in/resources/gogos/ayurvedha.png",
                    "status": "1"
                },
                {
                    "parent_category_id": 9,
                    "parent_category_name": "Homeopathy",
                    "image_url": "https://gogomedsdev.s3-ap-southeast-1.amazonaws.com/category/935f0ecd-1849-4ed6-a7ac-5bdbb64af7e3.jpg",
                    "status": "1"
                }
            ],
            "total_count": 11,
            "min_count": 9
        },
        {
            "id": 16,
            "screen": "Brand",
            "type": "product",
            "type_id": 9,
            "image_url": "",
            "description": "",
            "title": "All products",
            "background_img": "",
            "is_active": 1,
            "sub_id": null,
            "order": 3,
            "delivery_type": 1,
            "value": [
                {
                    "fsi_uid": 13,
                    "item_group_id": 13,
                    "item_name": "Koflet Lozenges",
                    "brand_name": "Himalaya",
                    "category_id": 2,
                    "category_name": "cough",
                    "variant": "packet",
                    "isMedicine": 0,
                    "item_master": [
                        {
                            "stit_ID": 13,
                            "quantity": "10 lozenges",
                            "stit_fsiuid": 13,
                            "stock_available": 100,
                            "selling_prize": 189.2,
                            "mrp": 220,
                            "isMedicine": 0,
                            "main_image": [
                                {
                                    "id": 263,
                                    "product_id": 13,
                                    "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-380b9d66-0b8d-4b80-a6bb-78b2f5700af7.jpg",
                                    "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-380b9d66-0b8d-4b80-a6bb-78b2f5700af7.jpg"
                                }
                            ],
                            "default_value": 1,
                            "selling_price": 189.2,
                            "godown_itemId": 392
                        },
                        {
                            "stit_ID": 14,
                            "quantity": "200 lozenges",
                            "stit_fsiuid": 13,
                            "stock_available": 0,
                            "selling_prize": 0,
                            "mrp": 0,
                            "isMedicine": 0,
                            "main_image": [
                                {
                                    "id": 261,
                                    "product_id": 14,
                                    "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-b1485f1f-9865-4a8d-a4cc-543df4d0c951.jpg",
                                    "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-b1485f1f-9865-4a8d-a4cc-543df4d0c951.jpg"
                                }
                            ],
                            "default_value": 0,
                            "selling_price": 0,
                            "godown_itemId": 507
                        }
                    ]
                },
                {
                    "fsi_uid": 14,
                    "item_group_id": 14,
                    "item_name": "Koflet Syrup ",
                    "brand_name": "Himalaya",
                    "category_id": 2,
                    "category_name": "cough",
                    "variant": "syrup",
                    "isMedicine": 0,
                    "item_master": [
                        {
                            "stit_ID": 15,
                            "quantity": "100ml",
                            "stit_fsiuid": 14,
                            "stock_available": 0,
                            "selling_prize": 0,
                            "mrp": 0,
                            "isMedicine": 0,
                            "main_image": [
                                {
                                    "id": 124,
                                    "product_id": 15,
                                    "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-7e68203f-9d1e-4799-b598-379deaf65989.jpg",
                                    "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-7e68203f-9d1e-4799-b598-379deaf65989.jpg"
                                }
                            ],
                            "default_value": 0,
                            "selling_price": 0,
                            "godown_itemId": 762
                        }
                    ]
                },
                {
                    "fsi_uid": 44,
                    "item_group_id": 44,
                    "item_name": "DIOCID",
                    "brand_name": "OMEPRAZOLE",
                    "category_id": 1,
                    "category_name": "Antacids, Antireflux Agents & Antiulcerants",
                    "variant": "20mg Capsule",
                    "isMedicine": 1,
                    "item_master": [
                        {
                            "stit_ID": 44,
                            "quantity": "",
                            "stit_fsiuid": 44,
                            "stock_available": 0,
                            "selling_prize": 0,
                            "mrp": 0,
                            "isMedicine": 1,
                            "main_image": [
                                {
                                    "id": 204,
                                    "product_id": 44,
                                    "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-7ee2dc09-dd17-4a24-ab84-0389701bb001.jpg",
                                    "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-7ee2dc09-dd17-4a24-ab84-0389701bb001.jpg"
                                }
                            ],
                            "default_value": 0,
                            "selling_price": 0,
                            "godown_itemId": 858
                        }
                    ]
                },
                {
                    "fsi_uid": 55,
                    "item_group_id": 55,
                    "item_name": "ESTOM",
                    "brand_name": "OMEPRAZOLE",
                    "category_id": 1,
                    "category_name": "Antacids, Antireflux Agents & Antiulcerants",
                    "variant": "40mg Capsule",
                    "isMedicine": 1,
                    "item_master": [
                        {
                            "stit_ID": 56,
                            "quantity": "",
                            "stit_fsiuid": 55,
                            "stock_available": 0,
                            "selling_prize": 0,
                            "mrp": 0,
                            "isMedicine": 1,
                            "main_image": [
                                {
                                    "id": 203,
                                    "product_id": 56,
                                    "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-5e583121-798b-4f75-91e4-8be9a2576ff4.jpg",
                                    "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-5e583121-798b-4f75-91e4-8be9a2576ff4.jpg"
                                }
                            ],
                            "default_value": 0,
                            "selling_price": 0,
                            "godown_itemId": 621
                        }
                    ]
                }
            ],
            "total_count": 4,
            "min_count": 9,
            "pagenate_details": {
                "currentpage": 1,
                "first_page_url": "http://localhost/pharmacy/public/api/brandscreen?page=1",
                "from": 1,
                "last_page": 1,
                "last_page_url": "http://localhost/pharmacy/public/api/brandscreen?page=1",
                "next_page_url": null,
                "path": "http://localhost/pharmacy/public/api/brandscreen",
                "per_page": 10,
                "prev_page_url": null,
                "to": 4,
                "total": 4
            }
        },
        {
            "id": 17,
            "screen": "Brand",
            "type": "SubCategory",
            "type_id": 4,
            "image_url": "https://devassetbrm.s3-ap-southeast-1.amazonaws.com/product/main/f52a29ff-b637-4db1-ad8e-04073d73f396",
            "description": "",
            "title": "Browse by Category",
            "background_img": "https://devassetbrm.s3-ap-southeast-1.amazonaws.com/product/main/f52a29ff-b637-4db1-ad8e-04073d73f396",
            "is_active": 1,
            "sub_id": 0,
            "order": 2,
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
                    "sub_category_id": 3,
                    "sub_category": "OrganicIndia",
                    "sub_category_image": "",
                    "status": "1",
                    "isMedicine": 0,
                    "main_category": 7,
                    "subcat_bmd_id": null,
                    "subcat_bmd_name": null
                },
                {
                    "sub_category_id": 4,
                    "sub_category": "Accu-Check",
                    "sub_category_image": "",
                    "status": "1",
                    "isMedicine": 0,
                    "main_category": 7,
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
                    "sub_category_id": 6,
                    "sub_category": "Himalaya Wellness",
                    "sub_category_image": "",
                    "status": "1",
                    "isMedicine": 0,
                    "main_category": 7,
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
                    "sub_category_id": 8,
                    "sub_category": "OneTouch",
                    "sub_category_image": "",
                    "status": "1",
                    "isMedicine": 0,
                    "main_category": 7,
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
            "total_count": 152,
            "min_count": 9
        }
    ]
}
```