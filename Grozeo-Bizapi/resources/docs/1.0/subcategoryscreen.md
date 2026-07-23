# Subcategory Screen


### Details

| Method | Uri   | Authorization |
| : |   :-   |  :  |
| POST | `subcategoryscreen` | NO |


Case: 1(subcategory details)
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
            "id": 19,
            "screen": "Subcategory",
            "type": "product",
            "type_id": 9,
            "image_url": "https://devassetbrm.s3-ap-southeast-1.amazonaws.com/product/main/f52a29ff-b637-4db1-ad8e-04073d73f396",
            "description": "Leorem hayr tfhfh v ff",
            "title": "Products",
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
                                    "image_url": "https://.s3.ap-southeast-1.amazonaws.com/products/preview-64753f49-e9c7-432d-b614-64fc82612a1c.jpg",
                                    "image_thumb_url": "https://.s3.ap-southeast-1.amazonaws.com/products/thumbnail-64753f49-e9c7-432d-b614-64fc82612a1c.jpg"
                                }
                            ],
                            "default_value": 0,
                            "selling_price": 0,
                            "godown_itemId": 631
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
                                    "image_url": "https://.s3.ap-southeast-1.amazonaws.com/products/preview-066cd2df-16fa-44ab-8b93-a63cbc9f984b.jpg",
                                    "image_thumb_url": "https://.s3.ap-southeast-1.amazonaws.com/products/thumbnail-066cd2df-16fa-44ab-8b93-a63cbc9f984b.jpg"
                                }
                            ],
                            "default_value": 0,
                            "selling_price": 0,
                            "godown_itemId": 89
                        }
                    ]
                },
                {
                    "fsi_uid": 7,
                    "item_group_id": 7,
                    "item_name": "Stobal Cough Syrup",
                    "brand_name": "SBL",
                    "category_id": 2,
                    "category_name": "cough",
                    "variant": " ",
                    "isMedicine": 0,
                    "item_master": [
                        {
                            "stit_ID": 3,
                            "quantity": "60 ml Syrup",
                            "stit_fsiuid": 7,
                            "stock_available": 0,
                            "selling_prize": 0,
                            "isMedicine": 0,
                            "mrp": 0,
                            "main_image": [
                                {
                                    "id": 140,
                                    "product_id": 3,
                                    "image_url": "https://.s3.ap-southeast-1.amazonaws.com/products/preview-5a9d2bd3-e7d4-4d52-8e8b-4b52ec78670d.jpg",
                                    "image_thumb_url": "https://.s3.ap-southeast-1.amazonaws.com/products/thumbnail-5a9d2bd3-e7d4-4d52-8e8b-4b52ec78670d.jpg"
                                }
                            ],
                            "default_value": 0,
                            "selling_price": 0,
                            "godown_itemId": 781
                        }
                    ]
                },
                {
                    "fsi_uid": 9,
                    "item_group_id": 9,
                    "item_name": "Honitus Herbal Cough",
                    "brand_name": "Dabur",
                    "category_id": 2,
                    "category_name": "cough",
                    "variant": "syrup",
                    "isMedicine": 0,
                    "item_master": [
                        {
                            "stit_ID": 5,
                            "quantity": "100ml",
                            "stit_fsiuid": 9,
                            "stock_available": 0,
                            "selling_prize": 0,
                            "isMedicine": 0,
                            "mrp": 0,
                            "main_image": [
                                {
                                    "id": 143,
                                    "product_id": 5,
                                    "image_url": "https://.s3.ap-southeast-1.amazonaws.com/products/preview-47eb9e35-d74c-401b-9c4e-a52d1d169b9d.jpg",
                                    "image_thumb_url": "https://.s3.ap-southeast-1.amazonaws.com/products/thumbnail-47eb9e35-d74c-401b-9c4e-a52d1d169b9d.jpg"
                                }
                            ],
                            "default_value": 0,
                            "selling_price": 0,
                            "godown_itemId": 669
                        },
                        {
                            "stit_ID": 6,
                            "quantity": "200ml",
                            "stit_fsiuid": 9,
                            "stock_available": 20,
                            "selling_prize": 99.5,
                            "isMedicine": 0,
                            "mrp": 130,
                            "main_image": [
                                {
                                    "id": 147,
                                    "product_id": 6,
                                    "image_url": "https://.s3.ap-southeast-1.amazonaws.com/products/preview-0b811771-d50f-4c48-b36a-e4bf8a959573.jpg",
                                    "image_thumb_url": "https://.s3.ap-southeast-1.amazonaws.com/products/thumbnail-0b811771-d50f-4c48-b36a-e4bf8a959573.jpg"
                                }
                            ],
                            "default_value": 1,
                            "selling_price": 99.5,
                            "godown_itemId": 801
                        }
                    ]
                },
                {
                    "fsi_uid": 10,
                    "item_group_id": 10,
                    "item_name": "Cough Drops",
                    "brand_name": "Vicks",
                    "category_id": 2,
                    "category_name": "cough",
                    "variant": "",
                    "isMedicine": 0,
                    "item_master": [
                        {
                            "stit_ID": 7,
                            "quantity": "190 lozenges",
                            "stit_fsiuid": 10,
                            "stock_available": 10,
                            "selling_prize": 99.5,
                            "isMedicine": 0,
                            "mrp": 130,
                            "main_image": [
                                {
                                    "id": 150,
                                    "product_id": 7,
                                    "image_url": "https://.s3.ap-southeast-1.amazonaws.com/products/preview-ff060648-75ab-4ac8-b11d-2418a1867d05.jpg",
                                    "image_thumb_url": "https://.s3.ap-southeast-1.amazonaws.com/products/thumbnail-ff060648-75ab-4ac8-b11d-2418a1867d05.jpg"
                                }
                            ],
                            "default_value": 1,
                            "selling_price": 99.5,
                            "godown_itemId": 768
                        }
                    ]
                },
                {
                    "fsi_uid": 12,
                    "item_group_id": 12,
                    "item_name": "Bronchoherb Cough Syrup",
                    "brand_name": "SBL",
                    "category_id": 2,
                    "category_name": "cough",
                    "variant": "syrup",
                    "isMedicine": 0,
                    "item_master": [
                        {
                            "stit_ID": 11,
                            "quantity": "100ml",
                            "stit_fsiuid": 12,
                            "stock_available": 0,
                            "selling_prize": 0,
                            "isMedicine": 0,
                            "mrp": 0,
                            "main_image": [
                                {
                                    "id": 131,
                                    "product_id": 11,
                                    "image_url": "https://.s3.ap-southeast-1.amazonaws.com/products/preview-ac27e98a-bc67-4fed-b1e9-7043c63eeb0a.jpg",
                                    "image_thumb_url": "https://.s3.ap-southeast-1.amazonaws.com/products/thumbnail-ac27e98a-bc67-4fed-b1e9-7043c63eeb0a.jpg"
                                }
                            ],
                            "default_value": 0,
                            "selling_price": 0,
                            "godown_itemId": 268
                        },
                        {
                            "stit_ID": 12,
                            "quantity": "180ml",
                            "stit_fsiuid": 12,
                            "stock_available": 0,
                            "selling_prize": 0,
                            "isMedicine": 0,
                            "mrp": 0,
                            "main_image": [
                                {
                                    "id": 128,
                                    "product_id": 12,
                                    "image_url": "https://.s3.ap-southeast-1.amazonaws.com/products/preview-09fc243d-fa5d-4f70-b752-386fafb2ce74.jpg",
                                    "image_thumb_url": "https://.s3.ap-southeast-1.amazonaws.com/products/thumbnail-09fc243d-fa5d-4f70-b752-386fafb2ce74.jpg"
                                }
                            ],
                            "default_value": 0,
                            "selling_price": 0,
                            "godown_itemId": 756
                        }
                    ]
                },
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
                            "isMedicine": 0,
                            "mrp": 220,
                            "main_image": [
                                {
                                    "id": 263,
                                    "product_id": 13,
                                    "image_url": "https://.s3.ap-southeast-1.amazonaws.com/products/preview-380b9d66-0b8d-4b80-a6bb-78b2f5700af7.jpg",
                                    "image_thumb_url": "https://.s3.ap-southeast-1.amazonaws.com/products/thumbnail-380b9d66-0b8d-4b80-a6bb-78b2f5700af7.jpg"
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
                            "isMedicine": 0,
                            "mrp": 0,
                            "main_image": [
                                {
                                    "id": 261,
                                    "product_id": 14,
                                    "image_url": "https://.s3.ap-southeast-1.amazonaws.com/products/preview-b1485f1f-9865-4a8d-a4cc-543df4d0c951.jpg",
                                    "image_thumb_url": "https://.s3.ap-southeast-1.amazonaws.com/products/thumbnail-b1485f1f-9865-4a8d-a4cc-543df4d0c951.jpg"
                                }
                            ],
                            "default_value": 0,
                            "selling_price": 0,
                            "godown_itemId": 641
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
                            "isMedicine": 0,
                            "mrp": 0,
                            "main_image": [
                                {
                                    "id": 124,
                                    "product_id": 15,
                                    "image_url": "https://.s3.ap-southeast-1.amazonaws.com/products/preview-7e68203f-9d1e-4799-b598-379deaf65989.jpg",
                                    "image_thumb_url": "https://.s3.ap-southeast-1.amazonaws.com/products/thumbnail-7e68203f-9d1e-4799-b598-379deaf65989.jpg"
                                }
                            ],
                            "default_value": 0,
                            "selling_price": 0,
                            "godown_itemId": 60
                        }
                    ]
                },
                {
                    "fsi_uid": 42,
                    "item_group_id": 42,
                    "item_name": "Stobal Cough Syrup",
                    "brand_name": "SBL",
                    "category_id": 2,
                    "category_name": "cough",
                    "variant": "syrup",
                    "isMedicine": 0,
                    "item_master": [
                        {
                            "stit_ID": 8,
                            "quantity": "180ml",
                            "stit_fsiuid": 42,
                            "stock_available": 100,
                            "selling_prize": 14.95,
                            "isMedicine": 0,
                            "mrp": 15,
                            "main_image": [
                                {
                                    "id": 157,
                                    "product_id": 8,
                                    "image_url": "https://.s3.ap-southeast-1.amazonaws.com/products/preview-7f6204b3-521f-4a1e-a250-ad3fb7178e35.jpg",
                                    "image_thumb_url": "https://.s3.ap-southeast-1.amazonaws.com/products/thumbnail-7f6204b3-521f-4a1e-a250-ad3fb7178e35.jpg"
                                }
                            ],
                            "default_value": 1,
                            "selling_price": 14.95,
                            "godown_itemId": 242
                        },
                        {
                            "stit_ID": 9,
                            "quantity": "60ml",
                            "stit_fsiuid": 42,
                            "stock_available": 0,
                            "selling_prize": 0,
                            "isMedicine": 0,
                            "mrp": 0,
                            "main_image": [
                                {
                                    "id": 154,
                                    "product_id": 9,
                                    "image_url": "https://.s3.ap-southeast-1.amazonaws.com/products/preview-fb963957-f032-47c0-872b-368a4134e63e.jpg",
                                    "image_thumb_url": "https://.s3.ap-southeast-1.amazonaws.com/products/thumbnail-fb963957-f032-47c0-872b-368a4134e63e.jpg"
                                }
                            ],
                            "default_value": 0,
                            "selling_price": 0,
                            "godown_itemId": 294
                        },
                        {
                            "stit_ID": 10,
                            "quantity": "115ml",
                            "stit_fsiuid": 42,
                            "stock_available": 0,
                            "selling_prize": 0,
                            "isMedicine": 0,
                            "mrp": 0,
                            "main_image": [
                                {
                                    "id": 155,
                                    "product_id": 10,
                                    "image_url": "https://.s3.ap-southeast-1.amazonaws.com/products/preview-3e8d1177-8cbb-4685-b139-e90837383aa0.jpg",
                                    "image_thumb_url": "https://.s3.ap-southeast-1.amazonaws.com/products/thumbnail-3e8d1177-8cbb-4685-b139-e90837383aa0.jpg"
                                }
                            ],
                            "default_value": 0,
                            "selling_price": 0,
                            "godown_itemId": 981
                        }
                    ]
                }
            ],
            "total_count": 9,
            "min_count": 9,
            "pagenate_details": {
                "currentpage": 1,
                "first_page_url": "http://localhost/my-pharmacy-api/public/api/subcategoryscreen?page=1",
                "from": 1,
                "last_page": 1,
                "last_page_url": "http://localhost/my-pharmacy-api/public/api/subcategoryscreen?page=1",
                "next_page_url": null,
                "path": "http://localhost/my-pharmacy-api/public/api/subcategoryscreen",
                "per_page": 10,
                "prev_page_url": null,
                "to": 9,
                "total": 9
            }
        },
        {
            "id": 20,
            "screen": "Subcategory",
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
Case:2(Sort)
************************
### Request

```json

{
 "requested_id":"1",
 "branch_id":"1",
 "order_method":"1",

 "sort": {
       "price": "1"
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
            "id": 19,
            "screen": "Subcategory",
            "type": "product",
            "type_id": 9,
            "image_url": "https://devassetbrm.s3-ap-southeast-1.amazonaws.com/product/main/f52a29ff-b637-4db1-ad8e-04073d73f396",
            "description": "Leorem hayr tfhfh v ff",
            "title": "Products",
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
                                    "image_url": "https://.s3.ap-southeast-1.amazonaws.com/products/preview-64753f49-e9c7-432d-b614-64fc82612a1c.jpg",
                                    "image_thumb_url": "https://.s3.ap-southeast-1.amazonaws.com/products/thumbnail-64753f49-e9c7-432d-b614-64fc82612a1c.jpg"
                                }
                            ],
                            "default_value": 0,
                            "selling_price": 0,
                            "godown_itemId": 955
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
                                    "image_url": "https://.s3.ap-southeast-1.amazonaws.com/products/preview-066cd2df-16fa-44ab-8b93-a63cbc9f984b.jpg",
                                    "image_thumb_url": "https://.s3.ap-southeast-1.amazonaws.com/products/thumbnail-066cd2df-16fa-44ab-8b93-a63cbc9f984b.jpg"
                                }
                            ],
                            "default_value": 0,
                            "selling_price": 0,
                            "godown_itemId": 99
                        }
                    ]
                },
                {
                    "fsi_uid": 7,
                    "item_group_id": 7,
                    "item_name": "Stobal Cough Syrup",
                    "brand_name": "SBL",
                    "category_id": 2,
                    "category_name": "cough",
                    "variant": " ",
                    "isMedicine": 0,
                    "item_master": [
                        {
                            "stit_ID": 3,
                            "quantity": "60 ml Syrup",
                            "stit_fsiuid": 7,
                            "stock_available": 0,
                            "selling_prize": 0,
                            "isMedicine": 0,
                            "mrp": 0,
                            "main_image": [
                                {
                                    "id": 140,
                                    "product_id": 3,
                                    "image_url": "https://.s3.ap-southeast-1.amazonaws.com/products/preview-5a9d2bd3-e7d4-4d52-8e8b-4b52ec78670d.jpg",
                                    "image_thumb_url": "https://.s3.ap-southeast-1.amazonaws.com/products/thumbnail-5a9d2bd3-e7d4-4d52-8e8b-4b52ec78670d.jpg"
                                }
                            ],
                            "default_value": 0,
                            "selling_price": 0,
                            "godown_itemId": 932
                        }
                    ]
                },
                {
                    "fsi_uid": 9,
                    "item_group_id": 9,
                    "item_name": "Honitus Herbal Cough",
                    "brand_name": "Dabur",
                    "category_id": 2,
                    "category_name": "cough",
                    "variant": "syrup",
                    "isMedicine": 0,
                    "item_master": [
                        {
                            "stit_ID": 5,
                            "quantity": "100ml",
                            "stit_fsiuid": 9,
                            "stock_available": 0,
                            "selling_prize": 0,
                            "isMedicine": 0,
                            "mrp": 0,
                            "main_image": [
                                {
                                    "id": 143,
                                    "product_id": 5,
                                    "image_url": "https://.s3.ap-southeast-1.amazonaws.com/products/preview-47eb9e35-d74c-401b-9c4e-a52d1d169b9d.jpg",
                                    "image_thumb_url": "https://.s3.ap-southeast-1.amazonaws.com/products/thumbnail-47eb9e35-d74c-401b-9c4e-a52d1d169b9d.jpg"
                                }
                            ],
                            "default_value": 0,
                            "selling_price": 0,
                            "godown_itemId": 749
                        },
                        {
                            "stit_ID": 6,
                            "quantity": "200ml",
                            "stit_fsiuid": 9,
                            "stock_available": 20,
                            "selling_prize": 99.5,
                            "isMedicine": 0,
                            "mrp": 130,
                            "main_image": [
                                {
                                    "id": 147,
                                    "product_id": 6,
                                    "image_url": "https://.s3.ap-southeast-1.amazonaws.com/products/preview-0b811771-d50f-4c48-b36a-e4bf8a959573.jpg",
                                    "image_thumb_url": "https://.s3.ap-southeast-1.amazonaws.com/products/thumbnail-0b811771-d50f-4c48-b36a-e4bf8a959573.jpg"
                                }
                            ],
                            "default_value": 1,
                            "selling_price": 99.5,
                            "godown_itemId": 710
                        }
                    ]
                },
                {
                    "fsi_uid": 12,
                    "item_group_id": 12,
                    "item_name": "Bronchoherb Cough Syrup",
                    "brand_name": "SBL",
                    "category_id": 2,
                    "category_name": "cough",
                    "variant": "syrup",
                    "isMedicine": 0,
                    "item_master": [
                        {
                            "stit_ID": 11,
                            "quantity": "100ml",
                            "stit_fsiuid": 12,
                            "stock_available": 0,
                            "selling_prize": 0,
                            "isMedicine": 0,
                            "mrp": 0,
                            "main_image": [
                                {
                                    "id": 131,
                                    "product_id": 11,
                                    "image_url": "https://.s3.ap-southeast-1.amazonaws.com/products/preview-ac27e98a-bc67-4fed-b1e9-7043c63eeb0a.jpg",
                                    "image_thumb_url": "https://.s3.ap-southeast-1.amazonaws.com/products/thumbnail-ac27e98a-bc67-4fed-b1e9-7043c63eeb0a.jpg"
                                }
                            ],
                            "default_value": 0,
                            "selling_price": 0,
                            "godown_itemId": 587
                        },
                        {
                            "stit_ID": 12,
                            "quantity": "180ml",
                            "stit_fsiuid": 12,
                            "stock_available": 0,
                            "selling_prize": 0,
                            "isMedicine": 0,
                            "mrp": 0,
                            "main_image": [
                                {
                                    "id": 128,
                                    "product_id": 12,
                                    "image_url": "https://.s3.ap-southeast-1.amazonaws.com/products/preview-09fc243d-fa5d-4f70-b752-386fafb2ce74.jpg",
                                    "image_thumb_url": "https://.s3.ap-southeast-1.amazonaws.com/products/thumbnail-09fc243d-fa5d-4f70-b752-386fafb2ce74.jpg"
                                }
                            ],
                            "default_value": 0,
                            "selling_price": 0,
                            "godown_itemId": 823
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
                            "isMedicine": 0,
                            "mrp": 0,
                            "main_image": [
                                {
                                    "id": 124,
                                    "product_id": 15,
                                    "image_url": "https://.s3.ap-southeast-1.amazonaws.com/products/preview-7e68203f-9d1e-4799-b598-379deaf65989.jpg",
                                    "image_thumb_url": "https://.s3.ap-southeast-1.amazonaws.com/products/thumbnail-7e68203f-9d1e-4799-b598-379deaf65989.jpg"
                                }
                            ],
                            "default_value": 0,
                            "selling_price": 0,
                            "godown_itemId": 24
                        }
                    ]
                },
                {
                    "fsi_uid": 42,
                    "item_group_id": 42,
                    "item_name": "Stobal Cough Syrup",
                    "brand_name": "SBL",
                    "category_id": 2,
                    "category_name": "cough",
                    "variant": "syrup",
                    "isMedicine": 0,
                    "item_master": [
                        {
                            "stit_ID": 8,
                            "quantity": "180ml",
                            "stit_fsiuid": 42,
                            "stock_available": 100,
                            "selling_prize": 14.95,
                            "isMedicine": 0,
                            "mrp": 15,
                            "main_image": [
                                {
                                    "id": 157,
                                    "product_id": 8,
                                    "image_url": "https://.s3.ap-southeast-1.amazonaws.com/products/preview-7f6204b3-521f-4a1e-a250-ad3fb7178e35.jpg",
                                    "image_thumb_url": "https://.s3.ap-southeast-1.amazonaws.com/products/thumbnail-7f6204b3-521f-4a1e-a250-ad3fb7178e35.jpg"
                                }
                            ],
                            "default_value": 1,
                            "selling_price": 14.95,
                            "godown_itemId": 130
                        },
                        {
                            "stit_ID": 9,
                            "quantity": "60ml",
                            "stit_fsiuid": 42,
                            "stock_available": 0,
                            "selling_prize": 0,
                            "isMedicine": 0,
                            "mrp": 0,
                            "main_image": [
                                {
                                    "id": 154,
                                    "product_id": 9,
                                    "image_url": "https://.s3.ap-southeast-1.amazonaws.com/products/preview-fb963957-f032-47c0-872b-368a4134e63e.jpg",
                                    "image_thumb_url": "https://.s3.ap-southeast-1.amazonaws.com/products/thumbnail-fb963957-f032-47c0-872b-368a4134e63e.jpg"
                                }
                            ],
                            "default_value": 0,
                            "selling_price": 0,
                            "godown_itemId": 78
                        },
                        {
                            "stit_ID": 10,
                            "quantity": "115ml",
                            "stit_fsiuid": 42,
                            "stock_available": 0,
                            "selling_prize": 0,
                            "isMedicine": 0,
                            "mrp": 0,
                            "main_image": [
                                {
                                    "id": 155,
                                    "product_id": 10,
                                    "image_url": "https://.s3.ap-southeast-1.amazonaws.com/products/preview-3e8d1177-8cbb-4685-b139-e90837383aa0.jpg",
                                    "image_thumb_url": "https://.s3.ap-southeast-1.amazonaws.com/products/thumbnail-3e8d1177-8cbb-4685-b139-e90837383aa0.jpg"
                                }
                            ],
                            "default_value": 0,
                            "selling_price": 0,
                            "godown_itemId": 206
                        }
                    ]
                },
                {
                    "fsi_uid": 10,
                    "item_group_id": 10,
                    "item_name": "Cough Drops",
                    "brand_name": "Vicks",
                    "category_id": 2,
                    "category_name": "cough",
                    "variant": "",
                    "isMedicine": 0,
                    "item_master": [
                        {
                            "stit_ID": 7,
                            "quantity": "190 lozenges",
                            "stit_fsiuid": 10,
                            "stock_available": 10,
                            "selling_prize": 99.5,
                            "isMedicine": 0,
                            "mrp": 130,
                            "main_image": [
                                {
                                    "id": 150,
                                    "product_id": 7,
                                    "image_url": "https://.s3.ap-southeast-1.amazonaws.com/products/preview-ff060648-75ab-4ac8-b11d-2418a1867d05.jpg",
                                    "image_thumb_url": "https://.s3.ap-southeast-1.amazonaws.com/products/thumbnail-ff060648-75ab-4ac8-b11d-2418a1867d05.jpg"
                                }
                            ],
                            "default_value": 1,
                            "selling_price": 99.5,
                            "godown_itemId": 803
                        }
                    ]
                },
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
                            "isMedicine": 0,
                            "mrp": 220,
                            "main_image": [
                                {
                                    "id": 263,
                                    "product_id": 13,
                                    "image_url": "https://.s3.ap-southeast-1.amazonaws.com/products/preview-380b9d66-0b8d-4b80-a6bb-78b2f5700af7.jpg",
                                    "image_thumb_url": "https://.s3.ap-southeast-1.amazonaws.com/products/thumbnail-380b9d66-0b8d-4b80-a6bb-78b2f5700af7.jpg"
                                }
                            ],
                            "default_value": 1,
                            "selling_price": 189.2,
                            "godown_itemId": 139
                        },
                        {
                            "stit_ID": 14,
                            "quantity": "200 lozenges",
                            "stit_fsiuid": 13,
                            "stock_available": 0,
                            "selling_prize": 0,
                            "isMedicine": 0,
                            "mrp": 0,
                            "main_image": [
                                {
                                    "id": 261,
                                    "product_id": 14,
                                    "image_url": "https://.s3.ap-southeast-1.amazonaws.com/products/preview-b1485f1f-9865-4a8d-a4cc-543df4d0c951.jpg",
                                    "image_thumb_url": "https://.s3.ap-southeast-1.amazonaws.com/products/thumbnail-b1485f1f-9865-4a8d-a4cc-543df4d0c951.jpg"
                                }
                            ],
                            "default_value": 0,
                            "selling_price": 0,
                            "godown_itemId": 816
                        }
                    ]
                }
            ],
            "total_count": 9,
            "min_count": 9,
            "pagenate_details": {
                "currentpage": 1,
                "first_page_url": "http://localhost/my-pharmacy-api/public/api/subcategoryscreen?page=1",
                "from": 1,
                "last_page": 1,
                "last_page_url": "http://localhost/my-pharmacy-api/public/api/subcategoryscreen?page=1",
                "next_page_url": null,
                "path": "http://localhost/my-pharmacy-api/public/api/subcategoryscreen",
                "per_page": 10,
                "prev_page_url": null,
                "to": 9,
                "total": 9
            }
        },
        {
            "id": 20,
            "screen": "Subcategory",
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
Case:3(Sort and filter)
************************
### Request

```json

{
 "requested_id":"1",
 "branch_id":"1",
 "order_method":"1",

 "sort": {
       "price": "1"
   },
  "filter": {
       "category": [],
       "brands": ["23"],
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
            "id": 19,
            "screen": "Subcategory",
            "type": "product",
            "type_id": 9,
            "image_url": "https://devassetbrm.s3-ap-southeast-1.amazonaws.com/product/main/f52a29ff-b637-4db1-ad8e-04073d73f396",
            "description": "Leorem hayr tfhfh v ff",
            "title": "Products",
            "background_img": "https://devassetbrm.s3-ap-southeast-1.amazonaws.com/product/main/f52a29ff-b637-4db1-ad8e-04073d73f396",
            "is_active": 1,
            "sub_id": 0,
            "order": 2,
            "delivery_type": 1,
            "value": [
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
                            "isMedicine": 0,
                            "mrp": 0,
                            "main_image": [
                                {
                                    "id": 124,
                                    "product_id": 15,
                                    "image_url": "https://.s3.ap-southeast-1.amazonaws.com/products/preview-7e68203f-9d1e-4799-b598-379deaf65989.jpg",
                                    "image_thumb_url": "https://.s3.ap-southeast-1.amazonaws.com/products/thumbnail-7e68203f-9d1e-4799-b598-379deaf65989.jpg"
                                }
                            ],
                            "default_value": 0,
                            "selling_price": 0,
                            "godown_itemId": 792
                        }
                    ]
                },
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
                            "isMedicine": 0,
                            "mrp": 220,
                            "main_image": [
                                {
                                    "id": 263,
                                    "product_id": 13,
                                    "image_url": "https://.s3.ap-southeast-1.amazonaws.com/products/preview-380b9d66-0b8d-4b80-a6bb-78b2f5700af7.jpg",
                                    "image_thumb_url": "https://.s3.ap-southeast-1.amazonaws.com/products/thumbnail-380b9d66-0b8d-4b80-a6bb-78b2f5700af7.jpg"
                                }
                            ],
                            "default_value": 1,
                            "selling_price": 189.2,
                            "godown_itemId": 981
                        },
                        {
                            "stit_ID": 14,
                            "quantity": "200 lozenges",
                            "stit_fsiuid": 13,
                            "stock_available": 0,
                            "selling_prize": 0,
                            "isMedicine": 0,
                            "mrp": 0,
                            "main_image": [
                                {
                                    "id": 261,
                                    "product_id": 14,
                                    "image_url": "https://.s3.ap-southeast-1.amazonaws.com/products/preview-b1485f1f-9865-4a8d-a4cc-543df4d0c951.jpg",
                                    "image_thumb_url": "https://.s3.ap-southeast-1.amazonaws.com/products/thumbnail-b1485f1f-9865-4a8d-a4cc-543df4d0c951.jpg"
                                }
                            ],
                            "default_value": 0,
                            "selling_price": 0,
                            "godown_itemId": 433
                        }
                    ]
                }
            ],
            "total_count": 2,
            "min_count": 9,
            "pagenate_details": {
                "currentpage": 1,
                "first_page_url": "http://localhost/my-pharmacy-api/public/api/subcategoryscreen?page=1",
                "from": 1,
                "last_page": 1,
                "last_page_url": "http://localhost/my-pharmacy-api/public/api/subcategoryscreen?page=1",
                "next_page_url": null,
                "path": "http://localhost/my-pharmacy-api/public/api/subcategoryscreen",
                "per_page": 10,
                "prev_page_url": null,
                "to": 2,
                "total": 2
            }
        },
        {
            "id": 20,
            "screen": "Subcategory",
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