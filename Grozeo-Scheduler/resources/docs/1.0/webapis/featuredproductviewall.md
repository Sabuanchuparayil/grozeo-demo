# Home featured product list

---

### Details

| Method | Uri   | Authorization |
| : |   :-   |  :  |
| POST | `site/product/viewall` | NO |

### Request


Home screen  featured product list
************************************
```json

{
"branch_id":"1",
"order_method":"1",
"id":"3",
 "requested_id":"1",
"sort": {
       "price": "1"
   },
  "filter": {
       "category": [],
       "brands": [],
       "price_range":["0","101"]
   }

}

 


```

### Response

```json

{
    "status": "ok",
    "data": {
        "Product_details": [
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
                        "godown_itemId": 185
                    }
                ]
            },
            {
                "fsi_uid": 99,
                "item_group_id": 99,
                "item_name": "Jiva Aloe Vera Juice",
                "brand_name": "Jiva",
                "category_id": 113,
                "category_name": "Stomach Care",
                "variant": "liquid",
                "isMedicine": 0,
                "item_master": [
                    {
                        "stit_ID": 101,
                        "quantity": "500ml",
                        "stit_fsiuid": 99,
                        "stock_available": 0,
                        "selling_prize": 0,
                        "isMedicine": 0,
                        "mrp": 0,
                        "main_image": [
                            {
                                "id": 24,
                                "product_id": 101,
                                "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-2f8aea71-3aec-4c45-befd-150e718b0643.jpg",
                                "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-2f8aea71-3aec-4c45-befd-150e718b0643.jpg"
                            }
                        ],
                        "default_value": 0,
                        "selling_price": 0,
                        "godown_itemId": 865
                    }
                ]
            },
            {
                "fsi_uid": 76,
                "item_group_id": 76,
                "item_name": "Jiva Arjuna Tea",
                "brand_name": "Jiva",
                "category_id": 116,
                "category_name": "Cardiac Care",
                "variant": "Granules",
                "isMedicine": 0,
                "item_master": [
                    {
                        "stit_ID": 78,
                        "quantity": "300gm",
                        "stit_fsiuid": 76,
                        "stock_available": 0,
                        "selling_prize": 0,
                        "isMedicine": 0,
                        "mrp": 0,
                        "main_image": [
                            {
                                "id": 68,
                                "product_id": 78,
                                "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-665ab74a-2b30-4900-9374-7ef877c0711c.jpg",
                                "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-665ab74a-2b30-4900-9374-7ef877c0711c.jpg"
                            }
                        ],
                        "default_value": 0,
                        "selling_price": 0,
                        "godown_itemId": 118
                    }
                ]
            },
            {
                "fsi_uid": 80,
                "item_group_id": 80,
                "item_name": "Organic India Flaxseed Oil Capsule",
                "brand_name": "Organic India",
                "category_id": 116,
                "category_name": "Cardiac Care",
                "variant": "capsules",
                "isMedicine": 0,
                "item_master": [
                    {
                        "stit_ID": 82,
                        "quantity": "60",
                        "stit_fsiuid": 80,
                        "stock_available": 0,
                        "selling_prize": 0,
                        "isMedicine": 0,
                        "mrp": 0,
                        "main_image": [
                            {
                                "id": 63,
                                "product_id": 82,
                                "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-07171eeb-1e72-4f59-951a-2baecc319acf.jpg",
                                "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-07171eeb-1e72-4f59-951a-2baecc319acf.jpg"
                            }
                        ],
                        "default_value": 0,
                        "selling_price": 0,
                        "godown_itemId": 777
                    }
                ]
            },
            {
                "fsi_uid": 84,
                "item_group_id": 84,
                "item_name": "Baidyanath Arjunarishta",
                "brand_name": "Baidyanath",
                "category_id": 116,
                "category_name": "Cardiac Care",
                "variant": "liquid",
                "isMedicine": 0,
                "item_master": [
                    {
                        "stit_ID": 86,
                        "quantity": "450ml",
                        "stit_fsiuid": 84,
                        "stock_available": 0,
                        "selling_prize": 0,
                        "isMedicine": 0,
                        "mrp": 0,
                        "main_image": [
                            {
                                "id": 48,
                                "product_id": 86,
                                "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-78de506f-e262-47f3-83ec-30bbd93dcc64.jpg",
                                "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-78de506f-e262-47f3-83ec-30bbd93dcc64.jpg"
                            }
                        ],
                        "default_value": 0,
                        "selling_price": 0,
                        "godown_itemId": 819
                    }
                ]
            },
            {
                "fsi_uid": 87,
                "item_group_id": 87,
                "item_name": "Baidyanath Kalyan Sundar Ras Gold Yukt",
                "brand_name": "Baidyanath",
                "category_id": 116,
                "category_name": "Cardiac Care",
                "variant": "tablets",
                "isMedicine": 0,
                "item_master": [
                    {
                        "stit_ID": 89,
                        "quantity": "500mg tab",
                        "stit_fsiuid": 87,
                        "stock_available": 0,
                        "selling_prize": 0,
                        "isMedicine": 0,
                        "mrp": 0,
                        "main_image": [
                            {
                                "id": 41,
                                "product_id": 89,
                                "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-9f622cac-5817-4185-8b21-ca23a89c2c6b.png",
                                "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-9f622cac-5817-4185-8b21-ca23a89c2c6b.png"
                            }
                        ],
                        "default_value": 0,
                        "selling_price": 0,
                        "godown_itemId": 298
                    }
                ]
            },
            {
                "fsi_uid": 92,
                "item_group_id": 92,
                "item_name": "Dabur Kumaryasava",
                "brand_name": "Dabur",
                "category_id": 114,
                "category_name": "Liver Care",
                "variant": "",
                "isMedicine": 0,
                "item_master": [
                    {
                        "stit_ID": 94,
                        "quantity": "680ml",
                        "stit_fsiuid": 92,
                        "stock_available": 0,
                        "selling_prize": 0,
                        "isMedicine": 0,
                        "mrp": 0,
                        "main_image": [
                            {
                                "id": 35,
                                "product_id": 94,
                                "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-a1074858-1fd2-44d2-a59e-b879c159badf.jpg",
                                "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-a1074858-1fd2-44d2-a59e-b879c159badf.jpg"
                            }
                        ],
                        "default_value": 0,
                        "selling_price": 0,
                        "godown_itemId": 71
                    }
                ]
            },
            {
                "fsi_uid": 95,
                "item_group_id": 95,
                "item_name": "Jiva Amla Tablet",
                "brand_name": "Jiva",
                "category_id": 113,
                "category_name": "Stomach Care",
                "variant": "tablets",
                "isMedicine": 0,
                "item_master": [
                    {
                        "stit_ID": 97,
                        "quantity": "120 tabs",
                        "stit_fsiuid": 95,
                        "stock_available": 0,
                        "selling_prize": 0,
                        "isMedicine": 0,
                        "mrp": 0,
                        "main_image": [
                            {
                                "id": 29,
                                "product_id": 97,
                                "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-d47b8dce-ea63-4481-904c-4b4845d622f8.jpg",
                                "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-d47b8dce-ea63-4481-904c-4b4845d622f8.jpg"
                            }
                        ],
                        "default_value": 0,
                        "selling_price": 0,
                        "godown_itemId": 822
                    }
                ]
            },
            {
                "fsi_uid": 97,
                "item_group_id": 97,
                "item_name": "Jiva Digestall Churna",
                "brand_name": "Jiva",
                "category_id": 113,
                "category_name": "Stomach Care",
                "variant": "box of Sachets",
                "isMedicine": 0,
                "item_master": [
                    {
                        "stit_ID": 99,
                        "quantity": "30 Sachets",
                        "stit_fsiuid": 97,
                        "stock_available": 0,
                        "selling_prize": 0,
                        "isMedicine": 0,
                        "mrp": 0,
                        "main_image": [
                            {
                                "id": 26,
                                "product_id": 99,
                                "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-dd5fe2d6-cb9e-430d-887d-9b7f68bccba6.jpg",
                                "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-dd5fe2d6-cb9e-430d-887d-9b7f68bccba6.jpg"
                            }
                        ],
                        "default_value": 0,
                        "selling_price": 0,
                        "godown_itemId": 702
                    }
                ]
            },
            {
                "fsi_uid": 106,
                "item_group_id": 106,
                "item_name": "PediaSure Refill Pack Kesar Badam",
                "brand_name": "PediaSure",
                "category_id": 57,
                "category_name": "For Children",
                "variant": "box of powders",
                "isMedicine": 0,
                "item_master": [
                    {
                        "stit_ID": 57,
                        "quantity": "200g",
                        "stit_fsiuid": 106,
                        "stock_available": 0,
                        "selling_prize": 0,
                        "isMedicine": 0,
                        "mrp": 0,
                        "main_image": [
                            {
                                "id": 88,
                                "product_id": 57,
                                "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-9803be8b-2846-466c-8b5e-2313e4771c04.jpg",
                                "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-9803be8b-2846-466c-8b5e-2313e4771c04.jpg"
                            }
                        ],
                        "default_value": 0,
                        "selling_price": 0,
                        "godown_itemId": 712
                    }
                ]
            }
        ],
        "currentpage": 1,
        "first_page_url": "http://localhost/pharmacy/public/api/site/product/viewall?page=1",
        "from": 1,
        "last_page": 4,
        "last_page_url": "http://localhost/pharmacy/public/api/site/product/viewall?page=4",
        "next_page_url": "http://localhost/pharmacy/public/api/site/product/viewall?page=2",
        "path": "http://localhost/pharmacy/public/api/site/product/viewall",
        "per_page": 10,
        "prev_page_url": null,
        "to": 10,
        "total": 39
    }


```
### Request


Category screen featured product list
************************************
```json

{
"branch_id":"1",
"order_method":"1",
"id" :"11",
"requested_id":"1",
"sort": {
       "price": "2"
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
    "data": {
        "Product_details": [
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
                        "stock_available": 100,
                        "selling_prize": 189.2,
                        "mrp": 220,
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
                        "godown_itemId": 359
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
                        "godown_itemId": 185
                    }
                ]
            },
            {
                "fsi_uid": 42,
                "item_group_id": 42,
                "item_name": "SBL Stobal Cough Syrup",
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
                        "mrp": 15,
                        "main_image": [
                            {
                                "id": 157,
                                "product_id": 8,
                                "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-7f6204b3-521f-4a1e-a250-ad3fb7178e35.jpg",
                                "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-7f6204b3-521f-4a1e-a250-ad3fb7178e35.jpg"
                            }
                        ],
                        "default_value": 1,
                        "selling_price": 14.95,
                        "godown_itemId": 949
                    },
                    {
                        "stit_ID": 9,
                        "quantity": "60ml",
                        "stit_fsiuid": 42,
                        "stock_available": 0,
                        "selling_prize": 0,
                        "mrp": 0,
                        "main_image": [
                            {
                                "id": 154,
                                "product_id": 9,
                                "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-fb963957-f032-47c0-872b-368a4134e63e.jpg",
                                "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-fb963957-f032-47c0-872b-368a4134e63e.jpg"
                            }
                        ],
                        "default_value": 0,
                        "selling_price": 0,
                        "godown_itemId": 544
                    },
                    {
                        "stit_ID": 10,
                        "quantity": "115ml",
                        "stit_fsiuid": 42,
                        "stock_available": 0,
                        "selling_prize": 0,
                        "mrp": 0,
                        "main_image": [
                            {
                                "id": 155,
                                "product_id": 10,
                                "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-3e8d1177-8cbb-4685-b139-e90837383aa0.jpg",
                                "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-3e8d1177-8cbb-4685-b139-e90837383aa0.jpg"
                            }
                        ],
                        "default_value": 0,
                        "selling_price": 0,
                        "godown_itemId": 189
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
                        "godown_itemId": 224
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
                        "godown_itemId": 827
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
                        "godown_itemId": 683
                    },
                    {
                        "stit_ID": 6,
                        "quantity": "200ml",
                        "stit_fsiuid": 9,
                        "stock_available": 30,
                        "selling_prize": 99.5,
                        "mrp": 130,
                        "main_image": [
                            {
                                "id": 147,
                                "product_id": 6,
                                "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-0b811771-d50f-4c48-b36a-e4bf8a959573.jpg",
                                "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-0b811771-d50f-4c48-b36a-e4bf8a959573.jpg"
                            }
                        ],
                        "default_value": 1,
                        "selling_price": 99.5,
                        "godown_itemId": 515
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
                        "godown_itemId": 776
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
                        "godown_itemId": 918
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
                        "godown_itemId": 553
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
                        "godown_itemId": 491
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
                        "godown_itemId": 839
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
                        "godown_itemId": 860
                    }
                ]
            }
        ],
        "currentpage": 1,
        "first_page_url": "http://localhost/pharmacy/public/api/site/product/viewall?page=1",
        "from": 1,
        "last_page": 2,
        "last_page_url": "http://localhost/pharmacy/public/api/site/product/viewall?page=2",
        "next_page_url": "http://localhost/pharmacy/public/api/site/product/viewall?page=2",
        "path": "http://localhost/pharmacy/public/api/site/product/viewall",
        "per_page": 10,
        "prev_page_url": null,
        "to": 10,
        "total": 14
    }
}
```
### Request


Home screen popular product list
************************************
```json
{
"branch_id":"1",
"order_method":"1",

"id" :"9",
"request_id":"",


"sort": {
       "price": "2"
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
    "data": {
        "Product_details": [
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
                        "stock_available": 60,
                        "selling_prize": 99.5,
                        "isMedicine": 0,
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
                        "godown_itemId": 314
                    }
                ]
            },
            {
                "fsi_uid": 42,
                "item_group_id": 42,
                "item_name": "SBL Stobal Cough Syrup",
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
                                "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-7f6204b3-521f-4a1e-a250-ad3fb7178e35.jpg",
                                "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-7f6204b3-521f-4a1e-a250-ad3fb7178e35.jpg"
                            }
                        ],
                        "default_value": 1,
                        "selling_price": 14.95,
                        "godown_itemId": 583
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
                                "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-fb963957-f032-47c0-872b-368a4134e63e.jpg",
                                "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-fb963957-f032-47c0-872b-368a4134e63e.jpg"
                            }
                        ],
                        "default_value": 0,
                        "selling_price": 0,
                        "godown_itemId": 311
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
                                "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-3e8d1177-8cbb-4685-b139-e90837383aa0.jpg",
                                "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-3e8d1177-8cbb-4685-b139-e90837383aa0.jpg"
                            }
                        ],
                        "default_value": 0,
                        "selling_price": 0,
                        "godown_itemId": 499
                    }
                ]
            },
            {
                "fsi_uid": 45,
                "item_group_id": 45,
                "item_name": "Adel Urea Pura Dilution 200 CH",
                "brand_name": "Adel",
                "category_id": 29,
                "category_name": "Homeopathy Medicines",
                "variant": "",
                "isMedicine": 0,
                "item_master": [
                    {
                        "stit_ID": 45,
                        "quantity": "10ml ",
                        "stit_fsiuid": 45,
                        "stock_available": 0,
                        "selling_prize": 0,
                        "isMedicine": 0,
                        "mrp": 0,
                        "main_image": [
                            {
                                "id": 90,
                                "product_id": 45,
                                "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-d7124718-1229-45df-a6b2-bbd86fb835c1.jpg",
                                "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-d7124718-1229-45df-a6b2-bbd86fb835c1.jpg"
                            }
                        ],
                        "default_value": 0,
                        "selling_price": 0,
                        "godown_itemId": 613
                    }
                ]
            },
            {
                "fsi_uid": 105,
                "item_group_id": 105,
                "item_name": "Bakson's Throat Aid Tablet",
                "brand_name": "Bakson's",
                "category_id": 144,
                "category_name": "Respiratory Wellness",
                "variant": "",
                "isMedicine": 0,
                "item_master": [
                    {
                        "stit_ID": 107,
                        "quantity": "75tabs",
                        "stit_fsiuid": 105,
                        "stock_available": 0,
                        "selling_prize": 0,
                        "isMedicine": 0,
                        "mrp": 0,
                        "main_image": [
                            {
                                "id": 19,
                                "product_id": 107,
                                "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-bca2cf73-0dea-4905-9041-25b36dba2133.jpg",
                                "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-bca2cf73-0dea-4905-9041-25b36dba2133.jpg"
                            }
                        ],
                        "default_value": 0,
                        "selling_price": 0,
                        "godown_itemId": 446
                    }
                ]
            },
            {
                "fsi_uid": 94,
                "item_group_id": 94,
                "item_name": "Jiva Triphala Tablet",
                "brand_name": "Jiva",
                "category_id": 113,
                "category_name": "Stomach Care",
                "variant": "",
                "isMedicine": 0,
                "item_master": [
                    {
                        "stit_ID": 96,
                        "quantity": "120 tab",
                        "stit_fsiuid": 94,
                        "stock_available": 0,
                        "selling_prize": 0,
                        "isMedicine": 0,
                        "mrp": 0,
                        "main_image": [
                            {
                                "id": 34,
                                "product_id": 96,
                                "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-192f18cc-4b74-4ea4-9eb1-935b148a28f1.jpg",
                                "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-192f18cc-4b74-4ea4-9eb1-935b148a28f1.jpg"
                            }
                        ],
                        "default_value": 0,
                        "selling_price": 0,
                        "godown_itemId": 733
                    }
                ]
            },
            {
                "fsi_uid": 90,
                "item_group_id": 90,
                "item_name": "Dabur Hepano Tablet",
                "brand_name": "Dabur",
                "category_id": 114,
                "category_name": "Liver Care",
                "variant": "tablets",
                "isMedicine": 0,
                "item_master": [
                    {
                        "stit_ID": 92,
                        "quantity": "60 tabs",
                        "stit_fsiuid": 90,
                        "stock_available": 0,
                        "selling_prize": 0,
                        "isMedicine": 0,
                        "mrp": 0,
                        "main_image": [
                            {
                                "id": 38,
                                "product_id": 92,
                                "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-7dc90410-9aaf-4fae-930c-a6c8311c3006.jpg",
                                "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-7dc90410-9aaf-4fae-930c-a6c8311c3006.jpg"
                            }
                        ],
                        "default_value": 0,
                        "selling_price": 0,
                        "godown_itemId": 553
                    }
                ]
            },
            {
                "fsi_uid": 85,
                "item_group_id": 85,
                "item_name": "Baidyanath Rakta Shodhak Bati",
                "brand_name": "Baidyanath",
                "category_id": 116,
                "category_name": "Cardiac Care",
                "variant": "bottle of tablets",
                "isMedicine": 0,
                "item_master": [
                    {
                        "stit_ID": 87,
                        "quantity": "50 tab",
                        "stit_fsiuid": 85,
                        "stock_available": 0,
                        "selling_prize": 0,
                        "isMedicine": 0,
                        "mrp": 0,
                        "main_image": [
                            {
                                "id": 44,
                                "product_id": 87,
                                "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-a10753e9-36bf-45c1-ae17-739b170dc1d0.jpg",
                                "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-a10753e9-36bf-45c1-ae17-739b170dc1d0.jpg"
                            }
                        ],
                        "default_value": 0,
                        "selling_price": 0,
                        "godown_itemId": 956
                    }
                ]
            },
            {
                "fsi_uid": 81,
                "item_group_id": 81,
                "item_name": "Organic India Heart Guard Capsule",
                "brand_name": "Organic India",
                "category_id": 116,
                "category_name": "Cardiac Care",
                "variant": "bottle of capsule",
                "isMedicine": 0,
                "item_master": [
                    {
                        "stit_ID": 83,
                        "quantity": "60",
                        "stit_fsiuid": 81,
                        "stock_available": 0,
                        "selling_prize": 0,
                        "isMedicine": 0,
                        "mrp": 0,
                        "main_image": [
                            {
                                "id": 110,
                                "product_id": 83,
                                "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-c6c92b18-68f9-4458-a392-3bbd3713092f.jpg",
                                "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-c6c92b18-68f9-4458-a392-3bbd3713092f.jpg"
                            }
                        ],
                        "default_value": 0,
                        "selling_price": 0,
                        "godown_itemId": 935
                    }
                ]
            },
            {
                "fsi_uid": 79,
                "item_group_id": 79,
                "item_name": "Organic India Lipid Care Capsule",
                "brand_name": "Organic India",
                "category_id": 116,
                "category_name": "Cardiac Care",
                "variant": "capsule",
                "isMedicine": 0,
                "item_master": [
                    {
                        "stit_ID": 81,
                        "quantity": "60",
                        "stit_fsiuid": 79,
                        "stock_available": 0,
                        "selling_prize": 0,
                        "isMedicine": 0,
                        "mrp": 0,
                        "main_image": [
                            {
                                "id": 60,
                                "product_id": 81,
                                "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-9e421c22-fcbc-499d-b6be-5ce557b54822.jpg",
                                "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-9e421c22-fcbc-499d-b6be-5ce557b54822.jpg"
                            }
                        ],
                        "default_value": 0,
                        "selling_price": 0,
                        "godown_itemId": 223
                    }
                ]
            },
            {
                "fsi_uid": 74,
                "item_group_id": 74,
                "item_name": "Jiva Wheat Grass + Amla Juice",
                "brand_name": "Jiva",
                "category_id": 116,
                "category_name": "Cardiac Care",
                "variant": "juice",
                "isMedicine": 0,
                "item_master": [
                    {
                        "stit_ID": 76,
                        "quantity": "500ml",
                        "stit_fsiuid": 74,
                        "stock_available": 0,
                        "selling_prize": 0,
                        "isMedicine": 0,
                        "mrp": 0,
                        "main_image": [
                            {
                                "id": 111,
                                "product_id": 76,
                                "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-19b9f6f9-5a65-428f-a22d-cc6fa4934928.jpg",
                                "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-19b9f6f9-5a65-428f-a22d-cc6fa4934928.jpg"
                            }
                        ],
                        "default_value": 0,
                        "selling_price": 0,
                        "godown_itemId": 877
                    }
                ]
            }
        ],
        "currentpage": 1,
        "first_page_url": "http://localhost/pharmacy/public/api/site/product/viewall?page=1",
        "from": 1,
        "last_page": 3,
        "last_page_url": "http://localhost/pharmacy/public/api/site/product/viewall?page=3",
        "next_page_url": "http://localhost/pharmacy/public/api/site/product/viewall?page=2",
        "path": "http://localhost/pharmacy/public/api/site/product/viewall",
        "per_page": 10,
        "prev_page_url": null,
        "to": 10,
        "total": 26
    }
}
```
