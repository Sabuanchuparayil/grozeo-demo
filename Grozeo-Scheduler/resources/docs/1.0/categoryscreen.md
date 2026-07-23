# Category Screen


### Details

| Method | Uri   | Authorization |
| : |   :-   |  :  |
| POST | `categoryscreen` | NO |


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
            "id": 11,
            "screen": "Category",
            "type": "Featured Products",
            "type_id": 3,
            "image_url": "https://devassetbrm.s3-ap-southeast-1.amazonaws.com/product/main/f52a29ff-b637-4db1-ad8e-04073d73f396",
            "description": "The Prodytr dhd eyrt",
            "title": "Featured Products",
            "background_img": "https://devassetbrm.s3-ap-southeast-1.amazonaws.com/product/main/f52a29ff-b637-4db1-ad8e-04073d73f396",
            "is_active": 1,
            "sub_id": 0,
            "order": 1,
            "delivery_type": 1,
            "value": [
                {
                    "fsi_uid": 6,
                    "item_group_id": 6,
                    "item_name": "Vicks Inhailer",
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
                            "godown_itemId": 308
                        }
                    ]
                },
                {
                    "fsi_uid": 7,
                    "item_group_id": 7,
                    "item_name": "SBL Stobal Cough Syrup",
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
                            "mrp": 0,
                            "main_image": [
                                {
                                    "id": 140,
                                    "product_id": 3,
                                    "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-5a9d2bd3-e7d4-4d52-8e8b-4b52ec78670d.jpg",
                                    "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-5a9d2bd3-e7d4-4d52-8e8b-4b52ec78670d.jpg"
                                }
                            ],
                            "default_value": 0,
                            "selling_price": 0,
                            "godown_itemId": 610
                        }
                    ]
                },
                {
                    "fsi_uid": 9,
                    "item_group_id": 9,
                    "item_name": "Dabur Honitus Herbal Cough",
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
                            "mrp": 0,
                            "main_image": [
                                {
                                    "id": 143,
                                    "product_id": 5,
                                    "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-47eb9e35-d74c-401b-9c4e-a52d1d169b9d.jpg",
                                    "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-47eb9e35-d74c-401b-9c4e-a52d1d169b9d.jpg"
                                }
                            ],
                            "default_value": 0,
                            "selling_price": 0,
                            "godown_itemId": 854
                        },
                        {
                            "stit_ID": 6,
                            "quantity": "200ml",
                            "stit_fsiuid": 9,
                            "stock_available": 0,
                            "selling_prize": 0,
                            "mrp": 0,
                            "main_image": [
                                {
                                    "id": 147,
                                    "product_id": 6,
                                    "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-0b811771-d50f-4c48-b36a-e4bf8a959573.jpg",
                                    "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-0b811771-d50f-4c48-b36a-e4bf8a959573.jpg"
                                }
                            ],
                            "default_value": 0,
                            "selling_price": 0,
                            "godown_itemId": 723
                        }
                    ]
                },
                {
                    "fsi_uid": 12,
                    "item_group_id": 12,
                    "item_name": "SBL Bronchoherb Cough Syrup",
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
                            "mrp": 0,
                            "main_image": [
                                {
                                    "id": 131,
                                    "product_id": 11,
                                    "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-ac27e98a-bc67-4fed-b1e9-7043c63eeb0a.jpg",
                                    "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-ac27e98a-bc67-4fed-b1e9-7043c63eeb0a.jpg"
                                }
                            ],
                            "default_value": 0,
                            "selling_price": 0,
                            "godown_itemId": 388
                        },
                        {
                            "stit_ID": 12,
                            "quantity": "180ml",
                            "stit_fsiuid": 12,
                            "stock_available": 0,
                            "selling_prize": 0,
                            "mrp": 0,
                            "main_image": [
                                {
                                    "id": 128,
                                    "product_id": 12,
                                    "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-09fc243d-fa5d-4f70-b752-386fafb2ce74.jpg",
                                    "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-09fc243d-fa5d-4f70-b752-386fafb2ce74.jpg"
                                }
                            ],
                            "default_value": 0,
                            "selling_price": 0,
                            "godown_itemId": 269
                        }
                    ]
                },
                {
                    "fsi_uid": 13,
                    "item_group_id": 13,
                    "item_name": "Himalaya Koflet Lozenges",
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
                            "stock_available": 0,
                            "selling_prize": 0,
                            "mrp": 0,
                            "main_image": [
                                {
                                    "id": 263,
                                    "product_id": 13,
                                    "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-380b9d66-0b8d-4b80-a6bb-78b2f5700af7.jpg",
                                    "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-380b9d66-0b8d-4b80-a6bb-78b2f5700af7.jpg"
                                }
                            ],
                            "default_value": 0,
                            "selling_price": 0,
                            "godown_itemId": 40
                        },
                        {
                            "stit_ID": 14,
                            "quantity": "200 lozenges",
                            "stit_fsiuid": 13,
                            "stock_available": 0,
                            "selling_prize": 0,
                            "mrp": 0,
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
                            "godown_itemId": 208
                        }
                    ]
                },
                {
                    "fsi_uid": 14,
                    "item_group_id": 14,
                    "item_name": "Himalaya Koflet Syrup ",
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
                            "godown_itemId": 827
                        }
                    ]
                },
                {
                    "fsi_uid": 20,
                    "item_group_id": 20,
                    "item_name": "Royal Black Pearl Flavoured Tea Cardamom Black",
                    "brand_name": "Royal Black Pearl",
                    "category_id": 16,
                    "category_name": "Herbal Teas",
                    "variant": "tea bags",
                    "isMedicine": 0,
                    "item_master": [
                        {
                            "stit_ID": 21,
                            "quantity": "15 tea bags",
                            "stit_fsiuid": 20,
                            "stock_available": 0,
                            "selling_prize": 0,
                            "mrp": 0,
                            "main_image": [
                                {
                                    "id": 277,
                                    "product_id": 21,
                                    "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-79966a46-843e-4133-8bdc-079e6ca2bb48.jpg",
                                    "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-79966a46-843e-4133-8bdc-079e6ca2bb48.jpg"
                                }
                            ],
                            "default_value": 0,
                            "selling_price": 0,
                            "godown_itemId": 280
                        }
                    ]
                },
                {
                    "fsi_uid": 23,
                    "item_group_id": 23,
                    "item_name": "HealthVit Ginseng & Ashwagandha Capsule",
                    "brand_name": "HealthVit",
                    "category_id": 19,
                    "category_name": "Ayurvedic Supplements",
                    "variant": "bottle of capsules",
                    "isMedicine": 0,
                    "item_master": [
                        {
                            "stit_ID": 24,
                            "quantity": "60 capsules",
                            "stit_fsiuid": 23,
                            "stock_available": 0,
                            "selling_prize": 0,
                            "mrp": 0,
                            "main_image": [
                                {
                                    "id": 280,
                                    "product_id": 24,
                                    "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-a2a56092-27ee-4023-88ae-c841cfd375ca.jpg",
                                    "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-a2a56092-27ee-4023-88ae-c841cfd375ca.jpg"
                                }
                            ],
                            "default_value": 0,
                            "selling_price": 0,
                            "godown_itemId": 442
                        }
                    ]
                }
            ],
            "total_count": 39,
            "min_count": 9
        },
        {
            "id": 12,
            "screen": "Category",
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
        },
        {
            "id": 13,
            "screen": "Category",
            "type": "product",
            "type_id": 9,
            "image_url": "",
            "description": "",
            "title": "All products",
            "background_img": "",
            "is_active": 1,
            "sub_id": null,
            "order": 4,
            "delivery_type": 1,
            "value": [
                {
                    "fsi_uid": 4,
                    "item_group_id": 4,
                    "item_name": "Vicks Vaporub",
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
                            "godown_itemId": 866
                        }
                    ]
                },
                {
                    "fsi_uid": 6,
                    "item_group_id": 6,
                    "item_name": "Vicks Inhailer",
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
                            "godown_itemId": 250
                        }
                    ]
                },
                {
                    "fsi_uid": 7,
                    "item_group_id": 7,
                    "item_name": "SBL Stobal Cough Syrup",
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
                            "mrp": 0,
                            "main_image": [
                                {
                                    "id": 140,
                                    "product_id": 3,
                                    "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-5a9d2bd3-e7d4-4d52-8e8b-4b52ec78670d.jpg",
                                    "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-5a9d2bd3-e7d4-4d52-8e8b-4b52ec78670d.jpg"
                                }
                            ],
                            "default_value": 0,
                            "selling_price": 0,
                            "godown_itemId": 275
                        }
                    ]
                },
                {
                    "fsi_uid": 9,
                    "item_group_id": 9,
                    "item_name": "Dabur Honitus Herbal Cough",
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
                            "mrp": 0,
                            "main_image": [
                                {
                                    "id": 143,
                                    "product_id": 5,
                                    "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-47eb9e35-d74c-401b-9c4e-a52d1d169b9d.jpg",
                                    "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-47eb9e35-d74c-401b-9c4e-a52d1d169b9d.jpg"
                                }
                            ],
                            "default_value": 0,
                            "selling_price": 0,
                            "godown_itemId": 599
                        },
                        {
                            "stit_ID": 6,
                            "quantity": "200ml",
                            "stit_fsiuid": 9,
                            "stock_available": 0,
                            "selling_prize": 0,
                            "mrp": 0,
                            "main_image": [
                                {
                                    "id": 147,
                                    "product_id": 6,
                                    "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-0b811771-d50f-4c48-b36a-e4bf8a959573.jpg",
                                    "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-0b811771-d50f-4c48-b36a-e4bf8a959573.jpg"
                                }
                            ],
                            "default_value": 0,
                            "selling_price": 0,
                            "godown_itemId": 525
                        }
                    ]
                },
                {
                    "fsi_uid": 10,
                    "item_group_id": 10,
                    "item_name": "Vicks Cough Drops",
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
                            "stock_available": 0,
                            "selling_prize": 99.5,
                            "mrp": 130,
                            "main_image": [
                                {
                                    "id": 150,
                                    "product_id": 7,
                                    "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-ff060648-75ab-4ac8-b11d-2418a1867d05.jpg",
                                    "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-ff060648-75ab-4ac8-b11d-2418a1867d05.jpg"
                                }
                            ],
                            "default_value": 1,
                            "selling_price": 99.5,
                            "godown_itemId": 931
                        }
                    ]
                },
                {
                    "fsi_uid": 12,
                    "item_group_id": 12,
                    "item_name": "SBL Bronchoherb Cough Syrup",
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
                            "mrp": 0,
                            "main_image": [
                                {
                                    "id": 131,
                                    "product_id": 11,
                                    "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-ac27e98a-bc67-4fed-b1e9-7043c63eeb0a.jpg",
                                    "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-ac27e98a-bc67-4fed-b1e9-7043c63eeb0a.jpg"
                                }
                            ],
                            "default_value": 0,
                            "selling_price": 0,
                            "godown_itemId": 827
                        },
                        {
                            "stit_ID": 12,
                            "quantity": "180ml",
                            "stit_fsiuid": 12,
                            "stock_available": 0,
                            "selling_prize": 0,
                            "mrp": 0,
                            "main_image": [
                                {
                                    "id": 128,
                                    "product_id": 12,
                                    "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-09fc243d-fa5d-4f70-b752-386fafb2ce74.jpg",
                                    "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-09fc243d-fa5d-4f70-b752-386fafb2ce74.jpg"
                                }
                            ],
                            "default_value": 0,
                            "selling_price": 0,
                            "godown_itemId": 643
                        }
                    ]
                },
                {
                    "fsi_uid": 13,
                    "item_group_id": 13,
                    "item_name": "Himalaya Koflet Lozenges",
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
                            "stock_available": 0,
                            "selling_prize": 0,
                            "mrp": 0,
                            "main_image": [
                                {
                                    "id": 263,
                                    "product_id": 13,
                                    "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-380b9d66-0b8d-4b80-a6bb-78b2f5700af7.jpg",
                                    "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-380b9d66-0b8d-4b80-a6bb-78b2f5700af7.jpg"
                                }
                            ],
                            "default_value": 0,
                            "selling_price": 0,
                            "godown_itemId": 216
                        },
                        {
                            "stit_ID": 14,
                            "quantity": "200 lozenges",
                            "stit_fsiuid": 13,
                            "stock_available": 0,
                            "selling_prize": 0,
                            "mrp": 0,
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
                            "godown_itemId": 789
                        }
                    ]
                },
                {
                    "fsi_uid": 14,
                    "item_group_id": 14,
                    "item_name": "Himalaya Koflet Syrup ",
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
                            "godown_itemId": 736
                        }
                    ]
                },
                {
                    "fsi_uid": 20,
                    "item_group_id": 20,
                    "item_name": "Royal Black Pearl Flavoured Tea Cardamom Black",
                    "brand_name": "Royal Black Pearl",
                    "category_id": 16,
                    "category_name": "Herbal Teas",
                    "variant": "tea bags",
                    "isMedicine": 0,
                    "item_master": [
                        {
                            "stit_ID": 21,
                            "quantity": "15 tea bags",
                            "stit_fsiuid": 20,
                            "stock_available": 0,
                            "selling_prize": 0,
                            "mrp": 0,
                            "main_image": [
                                {
                                    "id": 277,
                                    "product_id": 21,
                                    "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-79966a46-843e-4133-8bdc-079e6ca2bb48.jpg",
                                    "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-79966a46-843e-4133-8bdc-079e6ca2bb48.jpg"
                                }
                            ],
                            "default_value": 0,
                            "selling_price": 0,
                            "godown_itemId": 467
                        }
                    ]
                },
                {
                    "fsi_uid": 21,
                    "item_group_id": 21,
                    "item_name": "HealthVit Tulsi Drops",
                    "brand_name": "HealthVit",
                    "category_id": 19,
                    "category_name": "Ayurvedic Supplements",
                    "variant": "",
                    "isMedicine": 0,
                    "item_master": [
                        {
                            "stit_ID": 22,
                            "quantity": "30ml liquid",
                            "stit_fsiuid": 21,
                            "stock_available": 0,
                            "selling_prize": 0,
                            "mrp": 0,
                            "main_image": [
                                {
                                    "id": 118,
                                    "product_id": 22,
                                    "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-c5ec4933-ffd5-43a8-960a-a6db7e493db6.jpg",
                                    "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-c5ec4933-ffd5-43a8-960a-a6db7e493db6.jpg"
                                }
                            ],
                            "default_value": 0,
                            "selling_price": 0,
                            "godown_itemId": 948
                        }
                    ]
                }
            ],
            "total_count": 19,
            "min_count": 9,
            "pagenate_details": {
                "currentpage": 1,
                "first_page_url": "http://localhost/pharmacy/public/api/categoryscreen?page=1",
                "from": 1,
                "last_page": 2,
                "last_page_url": "http://localhost/pharmacy/public/api/categoryscreen?page=2",
                "next_page_url": "http://localhost/pharmacy/public/api/categoryscreen?page=2",
                "path": "http://localhost/pharmacy/public/api/categoryscreen",
                "per_page": 10,
                "prev_page_url": null,
                "to": 10,
                "total": 19
            }
        },
        {
            "id": 14,
            "screen": "Category",
            "type": "Brand",
            "type_id": 7,
            "image_url": "https://devassetbrm.s3-ap-southeast-1.amazonaws.com/product/main/f52a29ff-b637-4db1-ad8e-04073d73f396",
            "description": "",
            "title": "Top Brand",
            "background_img": "https://devassetbrm.s3-ap-southeast-1.amazonaws.com/product/main/f52a29ff-b637-4db1-ad8e-04073d73f396",
            "is_active": 1,
            "sub_id": 0,
            "order": 3,
            "delivery_type": 1,
            "value": [
                {
                    "brand_id": 1,
                    "brand_name": "OneLife",
                    "manufacture_id": 1,
                    "img_url": "https://gogomedsdev.s3-ap-southeast-1.amazonaws.com/brand/a116eee7-2929-4a23-ad2a-b7e7102ca118.png",
                    "img_name": "",
                    "top_brand": null,
                    "status": "1"
                }
            ],
            "total_count": 1,
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
            "id": 11,
            "screen": "Category",
            "type": "Featured Products",
            "type_id": 3,
            "image_url": "https://devassetbrm.s3-ap-southeast-1.amazonaws.com/product/main/f52a29ff-b637-4db1-ad8e-04073d73f396",
            "description": "The Prodytr dhd eyrt",
            "title": "Featured Products",
            "background_img": "https://devassetbrm.s3-ap-southeast-1.amazonaws.com/product/main/f52a29ff-b637-4db1-ad8e-04073d73f396",
            "is_active": 1,
            "sub_id": 0,
            "order": 1,
            "delivery_type": 1,
            "value": [
                {
                    "fsi_uid": 6,
                    "item_group_id": 6,
                    "item_name": "Vicks Inhailer",
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
                            "godown_itemId": 146
                        }
                    ]
                },
                {
                    "fsi_uid": 7,
                    "item_group_id": 7,
                    "item_name": "SBL Stobal Cough Syrup",
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
                            "mrp": 0,
                            "main_image": [
                                {
                                    "id": 140,
                                    "product_id": 3,
                                    "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-5a9d2bd3-e7d4-4d52-8e8b-4b52ec78670d.jpg",
                                    "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-5a9d2bd3-e7d4-4d52-8e8b-4b52ec78670d.jpg"
                                }
                            ],
                            "default_value": 0,
                            "selling_price": 0,
                            "godown_itemId": 683
                        }
                    ]
                },
                {
                    "fsi_uid": 9,
                    "item_group_id": 9,
                    "item_name": "Dabur Honitus Herbal Cough",
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
                            "mrp": 0,
                            "main_image": [
                                {
                                    "id": 143,
                                    "product_id": 5,
                                    "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-47eb9e35-d74c-401b-9c4e-a52d1d169b9d.jpg",
                                    "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-47eb9e35-d74c-401b-9c4e-a52d1d169b9d.jpg"
                                }
                            ],
                            "default_value": 0,
                            "selling_price": 0,
                            "godown_itemId": 34
                        },
                        {
                            "stit_ID": 6,
                            "quantity": "200ml",
                            "stit_fsiuid": 9,
                            "stock_available": 0,
                            "selling_prize": 0,
                            "mrp": 0,
                            "main_image": [
                                {
                                    "id": 147,
                                    "product_id": 6,
                                    "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-0b811771-d50f-4c48-b36a-e4bf8a959573.jpg",
                                    "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-0b811771-d50f-4c48-b36a-e4bf8a959573.jpg"
                                }
                            ],
                            "default_value": 0,
                            "selling_price": 0,
                            "godown_itemId": 237
                        }
                    ]
                },
                {
                    "fsi_uid": 12,
                    "item_group_id": 12,
                    "item_name": "SBL Bronchoherb Cough Syrup",
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
                            "mrp": 0,
                            "main_image": [
                                {
                                    "id": 131,
                                    "product_id": 11,
                                    "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-ac27e98a-bc67-4fed-b1e9-7043c63eeb0a.jpg",
                                    "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-ac27e98a-bc67-4fed-b1e9-7043c63eeb0a.jpg"
                                }
                            ],
                            "default_value": 0,
                            "selling_price": 0,
                            "godown_itemId": 365
                        },
                        {
                            "stit_ID": 12,
                            "quantity": "180ml",
                            "stit_fsiuid": 12,
                            "stock_available": 0,
                            "selling_prize": 0,
                            "mrp": 0,
                            "main_image": [
                                {
                                    "id": 128,
                                    "product_id": 12,
                                    "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-09fc243d-fa5d-4f70-b752-386fafb2ce74.jpg",
                                    "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-09fc243d-fa5d-4f70-b752-386fafb2ce74.jpg"
                                }
                            ],
                            "default_value": 0,
                            "selling_price": 0,
                            "godown_itemId": 617
                        }
                    ]
                },
                {
                    "fsi_uid": 13,
                    "item_group_id": 13,
                    "item_name": "Himalaya Koflet Lozenges",
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
                            "stock_available": 0,
                            "selling_prize": 0,
                            "mrp": 0,
                            "main_image": [
                                {
                                    "id": 263,
                                    "product_id": 13,
                                    "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-380b9d66-0b8d-4b80-a6bb-78b2f5700af7.jpg",
                                    "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-380b9d66-0b8d-4b80-a6bb-78b2f5700af7.jpg"
                                }
                            ],
                            "default_value": 0,
                            "selling_price": 0,
                            "godown_itemId": 829
                        },
                        {
                            "stit_ID": 14,
                            "quantity": "200 lozenges",
                            "stit_fsiuid": 13,
                            "stock_available": 0,
                            "selling_prize": 0,
                            "mrp": 0,
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
                            "godown_itemId": 344
                        }
                    ]
                },
                {
                    "fsi_uid": 14,
                    "item_group_id": 14,
                    "item_name": "Himalaya Koflet Syrup ",
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
                            "godown_itemId": 936
                        }
                    ]
                },
                {
                    "fsi_uid": 20,
                    "item_group_id": 20,
                    "item_name": "Royal Black Pearl Flavoured Tea Cardamom Black",
                    "brand_name": "Royal Black Pearl",
                    "category_id": 16,
                    "category_name": "Herbal Teas",
                    "variant": "tea bags",
                    "isMedicine": 0,
                    "item_master": [
                        {
                            "stit_ID": 21,
                            "quantity": "15 tea bags",
                            "stit_fsiuid": 20,
                            "stock_available": 0,
                            "selling_prize": 0,
                            "mrp": 0,
                            "main_image": [
                                {
                                    "id": 277,
                                    "product_id": 21,
                                    "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-79966a46-843e-4133-8bdc-079e6ca2bb48.jpg",
                                    "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-79966a46-843e-4133-8bdc-079e6ca2bb48.jpg"
                                }
                            ],
                            "default_value": 0,
                            "selling_price": 0,
                            "godown_itemId": 998
                        }
                    ]
                },
                {
                    "fsi_uid": 23,
                    "item_group_id": 23,
                    "item_name": "HealthVit Ginseng & Ashwagandha Capsule",
                    "brand_name": "HealthVit",
                    "category_id": 19,
                    "category_name": "Ayurvedic Supplements",
                    "variant": "bottle of capsules",
                    "isMedicine": 0,
                    "item_master": [
                        {
                            "stit_ID": 24,
                            "quantity": "60 capsules",
                            "stit_fsiuid": 23,
                            "stock_available": 0,
                            "selling_prize": 0,
                            "mrp": 0,
                            "main_image": [
                                {
                                    "id": 280,
                                    "product_id": 24,
                                    "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-a2a56092-27ee-4023-88ae-c841cfd375ca.jpg",
                                    "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-a2a56092-27ee-4023-88ae-c841cfd375ca.jpg"
                                }
                            ],
                            "default_value": 0,
                            "selling_price": 0,
                            "godown_itemId": 613
                        }
                    ]
                }
            ],
            "total_count": 39,
            "min_count": 9
        },
        {
            "id": 12,
            "screen": "Category",
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
        },
        {
            "id": 13,
            "screen": "Category",
            "type": "product",
            "type_id": 9,
            "image_url": "",
            "description": "",
            "title": "All products",
            "background_img": "",
            "is_active": 1,
            "sub_id": null,
            "order": 4,
            "delivery_type": 1,
            "value": [
                {
                    "fsi_uid": 4,
                    "item_group_id": 4,
                    "item_name": "Vicks Vaporub",
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
                            "godown_itemId": 944
                        }
                    ]
                },
                {
                    "fsi_uid": 23,
                    "item_group_id": 23,
                    "item_name": "HealthVit Ginseng & Ashwagandha Capsule",
                    "brand_name": "HealthVit",
                    "category_id": 19,
                    "category_name": "Ayurvedic Supplements",
                    "variant": "bottle of capsules",
                    "isMedicine": 0,
                    "item_master": [
                        {
                            "stit_ID": 24,
                            "quantity": "60 capsules",
                            "stit_fsiuid": 23,
                            "stock_available": 0,
                            "selling_prize": 0,
                            "mrp": 0,
                            "main_image": [
                                {
                                    "id": 280,
                                    "product_id": 24,
                                    "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-a2a56092-27ee-4023-88ae-c841cfd375ca.jpg",
                                    "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-a2a56092-27ee-4023-88ae-c841cfd375ca.jpg"
                                }
                            ],
                            "default_value": 0,
                            "selling_price": 0,
                            "godown_itemId": 252
                        }
                    ]
                },
                {
                    "fsi_uid": 35,
                    "item_group_id": 35,
                    "item_name": "Sri Sri Tattva Amla Candy Mango",
                    "brand_name": "Sri Sri Tattva",
                    "category_id": 19,
                    "category_name": "Ayurvedic Supplements",
                    "variant": "mango",
                    "isMedicine": 0,
                    "item_master": [
                        {
                            "stit_ID": 36,
                            "quantity": "400gm candy",
                            "stit_fsiuid": 35,
                            "stock_available": 0,
                            "selling_prize": 0,
                            "mrp": 0,
                            "main_image": [
                                {
                                    "id": 96,
                                    "product_id": 36,
                                    "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-0cdcf5bb-51b5-4530-80e4-850831923f24.jpg",
                                    "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-0cdcf5bb-51b5-4530-80e4-850831923f24.jpg"
                                }
                            ],
                            "default_value": 0,
                            "selling_price": 0,
                            "godown_itemId": 777
                        }
                    ]
                },
                {
                    "fsi_uid": 34,
                    "item_group_id": 34,
                    "item_name": "Sri Sri Tattva Ojasvita Chocolate",
                    "brand_name": "Sri Sri Tattva",
                    "category_id": 19,
                    "category_name": "Ayurvedic Supplements",
                    "variant": "box of powder",
                    "isMedicine": 0,
                    "item_master": [
                        {
                            "stit_ID": 34,
                            "quantity": "200gm powder",
                            "stit_fsiuid": 34,
                            "stock_available": 0,
                            "selling_prize": 0,
                            "mrp": 0,
                            "main_image": [
                                {
                                    "id": 295,
                                    "product_id": 34,
                                    "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-0ff07405-a7e2-4db2-ade9-2f21e7f78a02.jpg",
                                    "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-0ff07405-a7e2-4db2-ade9-2f21e7f78a02.jpg"
                                }
                            ],
                            "default_value": 0,
                            "selling_price": 0,
                            "godown_itemId": 276
                        },
                        {
                            "stit_ID": 35,
                            "quantity": "500gm powder",
                            "stit_fsiuid": 34,
                            "stock_available": 0,
                            "selling_prize": 0,
                            "mrp": 0,
                            "main_image": [
                                {
                                    "id": 102,
                                    "product_id": 35,
                                    "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-69a3c79f-49ec-48eb-af94-202631912f0c.jpg",
                                    "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-69a3c79f-49ec-48eb-af94-202631912f0c.jpg"
                                }
                            ],
                            "default_value": 0,
                            "selling_price": 0,
                            "godown_itemId": 113
                        }
                    ]
                },
                {
                    "fsi_uid": 32,
                    "item_group_id": 32,
                    "item_name": "Basic Ayurveda Neem Leaf Juice",
                    "brand_name": "Basic Ayurveda",
                    "category_id": 19,
                    "category_name": "Ayurvedic Supplements",
                    "variant": "bottle of liquid",
                    "isMedicine": 0,
                    "item_master": [
                        {
                            "stit_ID": 32,
                            "quantity": "500ml liquid",
                            "stit_fsiuid": 32,
                            "stock_available": 0,
                            "selling_prize": 0,
                            "mrp": 0,
                            "main_image": [
                                {
                                    "id": 291,
                                    "product_id": 32,
                                    "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-ca1f7c29-7f52-4afa-9acb-73b1b2a69d1b.jpg",
                                    "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-ca1f7c29-7f52-4afa-9acb-73b1b2a69d1b.jpg"
                                }
                            ],
                            "default_value": 0,
                            "selling_price": 0,
                            "godown_itemId": 901
                        }
                    ]
                },
                {
                    "fsi_uid": 30,
                    "item_group_id": 30,
                    "item_name": "Basic Ayurveda Ashwagandha Churna",
                    "brand_name": "Basic Ayurveda",
                    "category_id": 19,
                    "category_name": "Ayurvedic Supplements",
                    "variant": "box of powder",
                    "isMedicine": 0,
                    "item_master": [
                        {
                            "stit_ID": 30,
                            "quantity": "100gm powder",
                            "stit_fsiuid": 30,
                            "stock_available": 0,
                            "selling_prize": 0,
                            "mrp": 0,
                            "main_image": [
                                {
                                    "id": 103,
                                    "product_id": 30,
                                    "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-da28db3e-f99a-4d23-8b0d-68cf99be58c0.jpg",
                                    "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-da28db3e-f99a-4d23-8b0d-68cf99be58c0.jpg"
                                }
                            ],
                            "default_value": 0,
                            "selling_price": 0,
                            "godown_itemId": 923
                        }
                    ]
                },
                {
                    "fsi_uid": 27,
                    "item_group_id": 27,
                    "item_name": "Basic Ayurveda Van Tulsi Cough Syrup",
                    "brand_name": "Basic Ayurveda",
                    "category_id": 19,
                    "category_name": "Ayurvedic Supplements",
                    "variant": "syrup",
                    "isMedicine": 0,
                    "item_master": [
                        {
                            "stit_ID": 28,
                            "quantity": "200ml ",
                            "stit_fsiuid": 27,
                            "stock_available": 0,
                            "selling_prize": 0,
                            "mrp": 0,
                            "main_image": [
                                {
                                    "id": 109,
                                    "product_id": 28,
                                    "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-905db821-a761-497a-9c6d-e9ba3dc3ebe8.jpg",
                                    "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-905db821-a761-497a-9c6d-e9ba3dc3ebe8.jpg"
                                }
                            ],
                            "default_value": 0,
                            "selling_price": 0,
                            "godown_itemId": 711
                        }
                    ]
                },
                {
                    "fsi_uid": 25,
                    "item_group_id": 25,
                    "item_name": "HealthVit Wheat Grass Amla Juice",
                    "brand_name": "HealthVit",
                    "category_id": 19,
                    "category_name": "Ayurvedic Supplements",
                    "variant": "bottle of liquid",
                    "isMedicine": 0,
                    "item_master": [
                        {
                            "stit_ID": 26,
                            "quantity": "500ml liquid",
                            "stit_fsiuid": 25,
                            "stock_available": 0,
                            "selling_prize": 0,
                            "mrp": 0,
                            "main_image": [
                                {
                                    "id": 288,
                                    "product_id": 26,
                                    "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-96ef794d-3841-4fcc-9236-fb8493842436.jpg",
                                    "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-96ef794d-3841-4fcc-9236-fb8493842436.jpg"
                                }
                            ],
                            "default_value": 0,
                            "selling_price": 0,
                            "godown_itemId": 454
                        }
                    ]
                },
                {
                    "fsi_uid": 24,
                    "item_group_id": 24,
                    "item_name": "HealthVit Natural Ashwagandha Powder",
                    "brand_name": "HealthVit",
                    "category_id": 19,
                    "category_name": "Ayurvedic Supplements",
                    "variant": "packet of powder",
                    "isMedicine": 0,
                    "item_master": [
                        {
                            "stit_ID": 25,
                            "quantity": "100gm powder",
                            "stit_fsiuid": 24,
                            "stock_available": 0,
                            "selling_prize": 0,
                            "mrp": 0,
                            "main_image": [
                                {
                                    "id": 284,
                                    "product_id": 25,
                                    "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-3538f742-77f5-4d89-a3f8-18f2daffd1a7.jpg",
                                    "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-3538f742-77f5-4d89-a3f8-18f2daffd1a7.jpg"
                                }
                            ],
                            "default_value": 0,
                            "selling_price": 0,
                            "godown_itemId": 97
                        }
                    ]
                },
                {
                    "fsi_uid": 21,
                    "item_group_id": 21,
                    "item_name": "HealthVit Tulsi Drops",
                    "brand_name": "HealthVit",
                    "category_id": 19,
                    "category_name": "Ayurvedic Supplements",
                    "variant": "",
                    "isMedicine": 0,
                    "item_master": [
                        {
                            "stit_ID": 22,
                            "quantity": "30ml liquid",
                            "stit_fsiuid": 21,
                            "stock_available": 0,
                            "selling_prize": 0,
                            "mrp": 0,
                            "main_image": [
                                {
                                    "id": 118,
                                    "product_id": 22,
                                    "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-c5ec4933-ffd5-43a8-960a-a6db7e493db6.jpg",
                                    "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-c5ec4933-ffd5-43a8-960a-a6db7e493db6.jpg"
                                }
                            ],
                            "default_value": 0,
                            "selling_price": 0,
                            "godown_itemId": 488
                        }
                    ]
                }
            ],
            "total_count": 19,
            "min_count": 9,
            "pagenate_details": {
                "currentpage": 1,
                "first_page_url": "http://localhost/pharmacy/public/api/categoryscreen?page=1",
                "from": 1,
                "last_page": 2,
                "last_page_url": "http://localhost/pharmacy/public/api/categoryscreen?page=2",
                "next_page_url": "http://localhost/pharmacy/public/api/categoryscreen?page=2",
                "path": "http://localhost/pharmacy/public/api/categoryscreen",
                "per_page": 10,
                "prev_page_url": null,
                "to": 10,
                "total": 19
            }
        },
        {
            "id": 14,
            "screen": "Category",
            "type": "Brand",
            "type_id": 7,
            "image_url": "https://devassetbrm.s3-ap-southeast-1.amazonaws.com/product/main/f52a29ff-b637-4db1-ad8e-04073d73f396",
            "description": "",
            "title": "Top Brand",
            "background_img": "https://devassetbrm.s3-ap-southeast-1.amazonaws.com/product/main/f52a29ff-b637-4db1-ad8e-04073d73f396",
            "is_active": 1,
            "sub_id": 0,
            "order": 3,
            "delivery_type": 1,
            "value": [
                {
                    "brand_id": 1,
                    "brand_name": "OneLife",
                    "manufacture_id": 1,
                    "img_url": "https://gogomedsdev.s3-ap-southeast-1.amazonaws.com/brand/a116eee7-2929-4a23-ad2a-b7e7102ca118.png",
                    "img_name": "",
                    "top_brand": null,
                    "status": "1"
                }
            ],
            "total_count": 1,
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
            "id": 11,
            "screen": "Category",
            "type": "Featured Products",
            "type_id": 3,
            "image_url": "https://devassetbrm.s3-ap-southeast-1.amazonaws.com/product/main/f52a29ff-b637-4db1-ad8e-04073d73f396",
            "description": "The Prodytr dhd eyrt",
            "title": "Featured Products",
            "background_img": "https://devassetbrm.s3-ap-southeast-1.amazonaws.com/product/main/f52a29ff-b637-4db1-ad8e-04073d73f396",
            "is_active": 1,
            "sub_id": 0,
            "order": 1,
            "delivery_type": 1,
            "value": [
                {
                    "fsi_uid": 6,
                    "item_group_id": 6,
                    "item_name": "Vicks Inhailer",
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
                            "godown_itemId": 562
                        }
                    ]
                },
                {
                    "fsi_uid": 7,
                    "item_group_id": 7,
                    "item_name": "SBL Stobal Cough Syrup",
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
                            "mrp": 0,
                            "main_image": [
                                {
                                    "id": 140,
                                    "product_id": 3,
                                    "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-5a9d2bd3-e7d4-4d52-8e8b-4b52ec78670d.jpg",
                                    "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-5a9d2bd3-e7d4-4d52-8e8b-4b52ec78670d.jpg"
                                }
                            ],
                            "default_value": 0,
                            "selling_price": 0,
                            "godown_itemId": 974
                        }
                    ]
                },
                {
                    "fsi_uid": 9,
                    "item_group_id": 9,
                    "item_name": "Dabur Honitus Herbal Cough",
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
                            "mrp": 0,
                            "main_image": [
                                {
                                    "id": 143,
                                    "product_id": 5,
                                    "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-47eb9e35-d74c-401b-9c4e-a52d1d169b9d.jpg",
                                    "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-47eb9e35-d74c-401b-9c4e-a52d1d169b9d.jpg"
                                }
                            ],
                            "default_value": 0,
                            "selling_price": 0,
                            "godown_itemId": 536
                        },
                        {
                            "stit_ID": 6,
                            "quantity": "200ml",
                            "stit_fsiuid": 9,
                            "stock_available": 0,
                            "selling_prize": 0,
                            "mrp": 0,
                            "main_image": [
                                {
                                    "id": 147,
                                    "product_id": 6,
                                    "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-0b811771-d50f-4c48-b36a-e4bf8a959573.jpg",
                                    "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-0b811771-d50f-4c48-b36a-e4bf8a959573.jpg"
                                }
                            ],
                            "default_value": 0,
                            "selling_price": 0,
                            "godown_itemId": 887
                        }
                    ]
                },
                {
                    "fsi_uid": 12,
                    "item_group_id": 12,
                    "item_name": "SBL Bronchoherb Cough Syrup",
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
                            "mrp": 0,
                            "main_image": [
                                {
                                    "id": 131,
                                    "product_id": 11,
                                    "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-ac27e98a-bc67-4fed-b1e9-7043c63eeb0a.jpg",
                                    "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-ac27e98a-bc67-4fed-b1e9-7043c63eeb0a.jpg"
                                }
                            ],
                            "default_value": 0,
                            "selling_price": 0,
                            "godown_itemId": 302
                        },
                        {
                            "stit_ID": 12,
                            "quantity": "180ml",
                            "stit_fsiuid": 12,
                            "stock_available": 0,
                            "selling_prize": 0,
                            "mrp": 0,
                            "main_image": [
                                {
                                    "id": 128,
                                    "product_id": 12,
                                    "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-09fc243d-fa5d-4f70-b752-386fafb2ce74.jpg",
                                    "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-09fc243d-fa5d-4f70-b752-386fafb2ce74.jpg"
                                }
                            ],
                            "default_value": 0,
                            "selling_price": 0,
                            "godown_itemId": 36
                        }
                    ]
                },
                {
                    "fsi_uid": 13,
                    "item_group_id": 13,
                    "item_name": "Himalaya Koflet Lozenges",
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
                            "stock_available": 0,
                            "selling_prize": 0,
                            "mrp": 0,
                            "main_image": [
                                {
                                    "id": 263,
                                    "product_id": 13,
                                    "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-380b9d66-0b8d-4b80-a6bb-78b2f5700af7.jpg",
                                    "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-380b9d66-0b8d-4b80-a6bb-78b2f5700af7.jpg"
                                }
                            ],
                            "default_value": 0,
                            "selling_price": 0,
                            "godown_itemId": 385
                        },
                        {
                            "stit_ID": 14,
                            "quantity": "200 lozenges",
                            "stit_fsiuid": 13,
                            "stock_available": 0,
                            "selling_prize": 0,
                            "mrp": 0,
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
                            "godown_itemId": 573
                        }
                    ]
                },
                {
                    "fsi_uid": 14,
                    "item_group_id": 14,
                    "item_name": "Himalaya Koflet Syrup ",
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
                            "godown_itemId": 97
                        }
                    ]
                },
                {
                    "fsi_uid": 20,
                    "item_group_id": 20,
                    "item_name": "Royal Black Pearl Flavoured Tea Cardamom Black",
                    "brand_name": "Royal Black Pearl",
                    "category_id": 16,
                    "category_name": "Herbal Teas",
                    "variant": "tea bags",
                    "isMedicine": 0,
                    "item_master": [
                        {
                            "stit_ID": 21,
                            "quantity": "15 tea bags",
                            "stit_fsiuid": 20,
                            "stock_available": 0,
                            "selling_prize": 0,
                            "mrp": 0,
                            "main_image": [
                                {
                                    "id": 277,
                                    "product_id": 21,
                                    "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-79966a46-843e-4133-8bdc-079e6ca2bb48.jpg",
                                    "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-79966a46-843e-4133-8bdc-079e6ca2bb48.jpg"
                                }
                            ],
                            "default_value": 0,
                            "selling_price": 0,
                            "godown_itemId": 618
                        }
                    ]
                },
                {
                    "fsi_uid": 23,
                    "item_group_id": 23,
                    "item_name": "HealthVit Ginseng & Ashwagandha Capsule",
                    "brand_name": "HealthVit",
                    "category_id": 19,
                    "category_name": "Ayurvedic Supplements",
                    "variant": "bottle of capsules",
                    "isMedicine": 0,
                    "item_master": [
                        {
                            "stit_ID": 24,
                            "quantity": "60 capsules",
                            "stit_fsiuid": 23,
                            "stock_available": 0,
                            "selling_prize": 0,
                            "mrp": 0,
                            "main_image": [
                                {
                                    "id": 280,
                                    "product_id": 24,
                                    "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-a2a56092-27ee-4023-88ae-c841cfd375ca.jpg",
                                    "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-a2a56092-27ee-4023-88ae-c841cfd375ca.jpg"
                                }
                            ],
                            "default_value": 0,
                            "selling_price": 0,
                            "godown_itemId": 900
                        }
                    ]
                }
            ],
            "total_count": 39,
            "min_count": 9
        },
        {
            "id": 12,
            "screen": "Category",
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
        },
        {
            "id": 13,
            "screen": "Category",
            "type": "product",
            "type_id": 9,
            "image_url": "",
            "description": "",
            "title": "All products",
            "background_img": "",
            "is_active": 1,
            "sub_id": null,
            "order": 4,
            "delivery_type": 1,
            "value": [
                {
                    "fsi_uid": 13,
                    "item_group_id": 13,
                    "item_name": "Himalaya Koflet Lozenges",
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
                            "stock_available": 0,
                            "selling_prize": 0,
                            "mrp": 0,
                            "main_image": [
                                {
                                    "id": 263,
                                    "product_id": 13,
                                    "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-380b9d66-0b8d-4b80-a6bb-78b2f5700af7.jpg",
                                    "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-380b9d66-0b8d-4b80-a6bb-78b2f5700af7.jpg"
                                }
                            ],
                            "default_value": 0,
                            "selling_price": 0,
                            "godown_itemId": 626
                        },
                        {
                            "stit_ID": 14,
                            "quantity": "200 lozenges",
                            "stit_fsiuid": 13,
                            "stock_available": 0,
                            "selling_prize": 0,
                            "mrp": 0,
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
                            "godown_itemId": 797
                        }
                    ]
                },
                {
                    "fsi_uid": 14,
                    "item_group_id": 14,
                    "item_name": "Himalaya Koflet Syrup ",
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
                            "godown_itemId": 14
                        }
                    ]
                }
            ],
            "total_count": 2,
            "min_count": 9,
            "pagenate_details": {
                "currentpage": 1,
                "first_page_url": "http://localhost/pharmacy/public/api/categoryscreen?page=1",
                "from": 1,
                "last_page": 1,
                "last_page_url": "http://localhost/pharmacy/public/api/categoryscreen?page=1",
                "next_page_url": null,
                "path": "http://localhost/pharmacy/public/api/categoryscreen",
                "per_page": 10,
                "prev_page_url": null,
                "to": 2,
                "total": 2
            }
        },
        {
            "id": 14,
            "screen": "Category",
            "type": "Brand",
            "type_id": 7,
            "image_url": "https://devassetbrm.s3-ap-southeast-1.amazonaws.com/product/main/f52a29ff-b637-4db1-ad8e-04073d73f396",
            "description": "",
            "title": "Top Brand",
            "background_img": "https://devassetbrm.s3-ap-southeast-1.amazonaws.com/product/main/f52a29ff-b637-4db1-ad8e-04073d73f396",
            "is_active": 1,
            "sub_id": 0,
            "order": 3,
            "delivery_type": 1,
            "value": [
                {
                    "brand_id": 1,
                    "brand_name": "OneLife",
                    "manufacture_id": 1,
                    "img_url": "https://gogomedsdev.s3-ap-southeast-1.amazonaws.com/brand/a116eee7-2929-4a23-ad2a-b7e7102ca118.png",
                    "img_name": "",
                    "top_brand": null,
                    "status": "1"
                }
            ],
            "total_count": 1,
            "min_count": 9
        }
    ]
}
````

