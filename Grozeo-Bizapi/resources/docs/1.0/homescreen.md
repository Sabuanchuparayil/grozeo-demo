# HomeScreen

---
HomeScreen Api

### Details

| Method | Uri   | Authorization |
| : |   :-   |  :  |
| GET | `home/main/{id}\{order_method}` | NO |

Default id =1

order method 1- delivery
order method 2- I can collect

### Response

```json
{
    "status": "ok",
    "data": [
        {
            "id": 1,
            "screen": "Home",
            "type": "advertisement",
            "type_id": 1,
            "image_url": "https://devassetbrm.s3-ap-southeast-1.amazonaws.com/product/main/f52a29ff-b637-4db1-ad8e-04073d73f396",
            "description": "Leorem hayr tfhfh v ff",
            "title": "Advertisement",
            "background_img": "https://devassetbrm.s3-ap-southeast-1.amazonaws.com/product/main/f52a29ff-b637-4db1-ad8e-04073d73f396",
            "is_active": 1,
            "sub_id": 0,
            "order": 1,
            "delivery_type": 0,
            "value": [
                {
                    "adzone_id": 1,
                    "adzone_name": "Home Top Banner",
                    "adzone_type": "advertisement",
                    "adzone_screen": "Home",
                    "adzone_status": 1,
                    "adzone_cretedOn": "2020-03-30 10:06:38",
                    "adzone_cretedBy": 0,
                    "adzone_updatedOn": "2020-04-03 18:19:21",
                    "adzone_updatedBy": 106,
                    "adzone_details": [
                        {
                            "adv_id": 7,
                            "adv_usageType": "2",
                            "adv_title": "Bottom",
                            "adv_imageurl": "",
                            "adzone_id": 1,
                            "adv_link": "",
                            "adv_offer": "",
                            "adv_offerpercent": 0,
                            "adv_offerType": "",
                            "adv_offerValueId": 0,
                            "adv_startdate": "2020-05-16",
                            "adv_enddate": "2020-05-22",
                            "adv_status": 1,
                            "adv_createdOn": "2020-05-07 13:07:07",
                            "adv_createdBy": 0,
                            "adv_updatedOn": "0000-00-00 00:00:00",
                            "adv_updatedBy": 0
                        }
                    ]
                }
            ]
        },
        {
            "id": 2,
            "screen": "Home",
            "type": "category",
            "type_id": 2,
            "image_url": "https://devassetbrm.s3-ap-southeast-1.amazonaws.com/product/main/f52a29ff-b637-4db1-ad8e-04073d73f396",
            "description": "The Leoremnhbd eterbc fff",
            "title": "Shop by Category",
            "background_img": "https://devassetbrm.s3-ap-southeast-1.amazonaws.com/product/main/f52a29ff-b637-4db1-ad8e-04073d73f396",
            "is_active": 1,
            "sub_id": 0,
            "order": 2,
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
            "total_count": 10,
            "min_count": 9
        },
        {
            "id": 3,
            "screen": "Home",
            "type": "Featured Products",
            "type_id": 3,
            "image_url": "https://devassetbrm.s3-ap-southeast-1.amazonaws.com/product/main/f52a29ff-b637-4db1-ad8e-04073d73f396",
            "description": "The Prodytr dhd eyrt",
            "title": "Featured Products",
            "background_img": "https://devassetbrm.s3-ap-southeast-1.amazonaws.com/product/main/f52a29ff-b637-4db1-ad8e-04073d73f396",
            "is_active": 1,
            "sub_id": 0,
            "order": 3,
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
                            "godown_itemId": 960
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
                            "isMedicine": 0,
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
                            "godown_itemId": 898
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
                            "isMedicine": 0,
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
                            "godown_itemId": 541
                        },
                        {
                            "stit_ID": 6,
                            "quantity": "200ml",
                            "stit_fsiuid": 9,
                            "stock_available": 30,
                            "selling_prize": 99.5,
                            "isMedicine": 0,
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
                            "godown_itemId": 172
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
                            "isMedicine": 0,
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
                            "godown_itemId": 485
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
                                    "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-09fc243d-fa5d-4f70-b752-386fafb2ce74.jpg",
                                    "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-09fc243d-fa5d-4f70-b752-386fafb2ce74.jpg"
                                }
                            ],
                            "default_value": 0,
                            "godown_itemId": 128
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
                            "stock_available": 100,
                            "selling_prize": 189.2,
                            "isMedicine": 0,
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
                            "godown_itemId": 773
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
                                    "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-b1485f1f-9865-4a8d-a4cc-543df4d0c951.jpg",
                                    "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-b1485f1f-9865-4a8d-a4cc-543df4d0c951.jpg"
                                }
                            ],
                            "default_value": 0,
                            "godown_itemId": 80
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
                            "isMedicine": 0,
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
                            "godown_itemId": 537
                        }
                    ]
                },
                {
                    "fsi_uid": 18,
                    "item_group_id": 18,
                    "item_name": "  Organic India Tulsi Green Tea Lemon Ginger",
                    "brand_name": "  Organic India",
                    "category_id": 3,
                    "category_name": "OrganicIndia",
                    "variant": "Tea bags",
                    "isMedicine": 0,
                    "item_master": [
                        {
                            "stit_ID": 19,
                            "quantity": "25",
                            "stit_fsiuid": 18,
                            "stock_available": 0,
                            "selling_prize": 0,
                            "isMedicine": 0,
                            "mrp": 0,
                            "main_image": [
                                {
                                    "id": 125,
                                    "product_id": 19,
                                    "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-e093db06-f00b-4f20-abaf-c1127c861dc9.jpg",
                                    "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-e093db06-f00b-4f20-abaf-c1127c861dc9.jpg"
                                }
                            ],
                            "default_value": 0,
                            "godown_itemId": 38
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
                            "isMedicine": 0,
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
                            "godown_itemId": 368
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
                            "isMedicine": 0,
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
                            "godown_itemId": 451
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
                            "isMedicine": 0,
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
                            "godown_itemId": 304
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
                            "isMedicine": 0,
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
                            "godown_itemId": 773
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
                            "isMedicine": 0,
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
                            "godown_itemId": 661
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
                            "isMedicine": 0,
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
                            "godown_itemId": 627
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
                            "isMedicine": 0,
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
                            "godown_itemId": 437
                        },
                        {
                            "stit_ID": 35,
                            "quantity": "500gm powder",
                            "stit_fsiuid": 34,
                            "stock_available": 0,
                            "selling_prize": 0,
                            "isMedicine": 0,
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
                            "godown_itemId": 799
                        }
                    ]
                },
                {
                    "fsi_uid": 37,
                    "item_group_id": 37,
                    "item_name": "Adel Glucorect Drop",
                    "brand_name": "Adel",
                    "category_id": 29,
                    "category_name": "Homeopathy Medicines",
                    "variant": "Adel Pekana Germany",
                    "isMedicine": 0,
                    "item_master": [
                        {
                            "stit_ID": 38,
                            "quantity": "20ml drop",
                            "stit_fsiuid": 37,
                            "stock_available": 0,
                            "selling_prize": 0,
                            "isMedicine": 0,
                            "mrp": 0,
                            "main_image": [
                                {
                                    "id": 95,
                                    "product_id": 38,
                                    "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-7bcc9b8b-7317-498b-9a0f-0c384057a575.jpg",
                                    "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-7bcc9b8b-7317-498b-9a0f-0c384057a575.jpg"
                                }
                            ],
                            "default_value": 0,
                            "godown_itemId": 853
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
                            "godown_itemId": 505
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
                            "godown_itemId": 491
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
                            "godown_itemId": 793
                        }
                    ]
                },
                {
                    "fsi_uid": 51,
                    "item_group_id": 51,
                    "item_name": "PediaSure Refill Pack Premium Chocolate",
                    "brand_name": "PediaSure",
                    "category_id": 57,
                    "category_name": "For Children",
                    "variant": "packet of powder",
                    "isMedicine": 0,
                    "item_master": [
                        {
                            "stit_ID": 50,
                            "quantity": "1kg ",
                            "stit_fsiuid": 51,
                            "stock_available": 0,
                            "selling_prize": 0,
                            "isMedicine": 0,
                            "mrp": 0,
                            "main_image": [
                                {
                                    "id": 300,
                                    "product_id": 50,
                                    "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-7332b08c-b8e8-4cb1-b682-ae1e62859f79.jpg",
                                    "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-7332b08c-b8e8-4cb1-b682-ae1e62859f79.jpg"
                                }
                            ],
                            "default_value": 0,
                            "godown_itemId": 911
                        }
                    ]
                },
                {
                    "fsi_uid": 53,
                    "item_group_id": 53,
                    "item_name": "PediaSure Refill Pack Vanilla delight",
                    "brand_name": "PediaSure",
                    "category_id": 57,
                    "category_name": "For Children",
                    "variant": "powder",
                    "isMedicine": 0,
                    "item_master": [
                        {
                            "stit_ID": 55,
                            "quantity": "750 g",
                            "stit_fsiuid": 53,
                            "stock_available": 0,
                            "selling_prize": 0,
                            "isMedicine": 0,
                            "mrp": 0,
                            "main_image": [
                                {
                                    "id": 299,
                                    "product_id": 55,
                                    "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-7e0a89d2-3f1a-4f4c-ac3f-284495a89b10.jpg",
                                    "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-7e0a89d2-3f1a-4f4c-ac3f-284495a89b10.jpg"
                                }
                            ],
                            "default_value": 0,
                            "godown_itemId": 120
                        }
                    ]
                },
                {
                    "fsi_uid": 56,
                    "item_group_id": 56,
                    "item_name": "PediaSure Refill Pack Kesar Badam",
                    "brand_name": "PediaSure",
                    "category_id": 57,
                    "category_name": "For Children",
                    "variant": "box of powder",
                    "isMedicine": 0,
                    "item_master": [
                        {
                            "stit_ID": 58,
                            "quantity": "1 kg",
                            "stit_fsiuid": 56,
                            "stock_available": 0,
                            "selling_prize": 0,
                            "isMedicine": 0,
                            "mrp": 0,
                            "main_image": [],
                            "default_value": 0,
                            "godown_itemId": 766
                        }
                    ]
                },
                {
                    "fsi_uid": 60,
                    "item_group_id": 60,
                    "item_name": "SBL Berberis Aquifolium Gel",
                    "brand_name": "SBL",
                    "category_id": 143,
                    "category_name": "Skin Care Products",
                    "variant": "tube of gel",
                    "isMedicine": 0,
                    "item_master": [
                        {
                            "stit_ID": 62,
                            "quantity": "25g",
                            "stit_fsiuid": 60,
                            "stock_available": 0,
                            "selling_prize": 0,
                            "isMedicine": 0,
                            "mrp": 0,
                            "main_image": [
                                {
                                    "id": 81,
                                    "product_id": 62,
                                    "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-23d9130d-3120-471e-b378-bb81f0794f99.jpg",
                                    "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-23d9130d-3120-471e-b378-bb81f0794f99.jpg"
                                }
                            ],
                            "default_value": 0,
                            "godown_itemId": 558
                        }
                    ]
                },
                {
                    "fsi_uid": 67,
                    "item_group_id": 67,
                    "item_name": "SBL Wipe Clear Ache Lotion",
                    "brand_name": "SBL",
                    "category_id": 143,
                    "category_name": "Skin Care Products",
                    "variant": "",
                    "isMedicine": 0,
                    "item_master": [
                        {
                            "stit_ID": 69,
                            "quantity": "30ml",
                            "stit_fsiuid": 67,
                            "stock_available": 0,
                            "selling_prize": 0,
                            "isMedicine": 0,
                            "mrp": 0,
                            "main_image": [
                                {
                                    "id": 114,
                                    "product_id": 69,
                                    "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-b5de9964-9d9f-45a7-8df1-6db4fa5c58a3.jpg",
                                    "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-b5de9964-9d9f-45a7-8df1-6db4fa5c58a3.jpg"
                                }
                            ],
                            "default_value": 0,
                            "godown_itemId": 392
                        }
                    ]
                },
                {
                    "fsi_uid": 72,
                    "item_group_id": 72,
                    "item_name": "Jiva Arjuna Tablet",
                    "brand_name": "Jiva",
                    "category_id": 116,
                    "category_name": "Cardiac Care",
                    "variant": "tablet",
                    "isMedicine": 0,
                    "item_master": [
                        {
                            "stit_ID": 74,
                            "quantity": "120 tablets",
                            "stit_fsiuid": 72,
                            "stock_available": 0,
                            "selling_prize": 0,
                            "isMedicine": 0,
                            "mrp": 0,
                            "main_image": [
                                {
                                    "id": 71,
                                    "product_id": 74,
                                    "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-f0464a1c-49e1-4369-8f4a-faa13a4e8e7e.jpg",
                                    "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-f0464a1c-49e1-4369-8f4a-faa13a4e8e7e.jpg"
                                }
                            ],
                            "default_value": 0,
                            "godown_itemId": 675
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
                            "godown_itemId": 748
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
                            "godown_itemId": 77
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
                            "godown_itemId": 301
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
                            "godown_itemId": 391
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
                            "godown_itemId": 56
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
                            "godown_itemId": 837
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
                            "godown_itemId": 400
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
                            "godown_itemId": 121
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
                            "godown_itemId": 875
                        }
                    ]
                },
                {
                    "fsi_uid": 107,
                    "item_group_id": 107,
                    "item_name": "PediaSure Refill Pack Vanilla delight",
                    "brand_name": "PediaSure",
                    "category_id": 57,
                    "category_name": "For Children",
                    "variant": "powders",
                    "isMedicine": 0,
                    "item_master": [
                        {
                            "stit_ID": 54,
                            "quantity": "200 g",
                            "stit_fsiuid": 107,
                            "stock_available": 0,
                            "selling_prize": 0,
                            "isMedicine": 0,
                            "mrp": 0,
                            "main_image": [
                                {
                                    "id": 298,
                                    "product_id": 54,
                                    "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-b041bbd5-0de2-44fb-b104-e4f0a1cc31c2.jpg",
                                    "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-b041bbd5-0de2-44fb-b104-e4f0a1cc31c2.jpg"
                                }
                            ],
                            "default_value": 0,
                            "godown_itemId": 467
                        }
                    ]
                },
                {
                    "fsi_uid": 108,
                    "item_group_id": 108,
                    "item_name": "PediaSure Refill Pack Vanilla delight",
                    "brand_name": "PediaSure",
                    "category_id": 57,
                    "category_name": "For Children",
                    "variant": "powderi",
                    "isMedicine": 0,
                    "item_master": [
                        {
                            "stit_ID": 52,
                            "quantity": "1kg",
                            "stit_fsiuid": 108,
                            "stock_available": 0,
                            "selling_prize": 0,
                            "isMedicine": 0,
                            "mrp": 0,
                            "main_image": [
                                {
                                    "id": 296,
                                    "product_id": 52,
                                    "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-7887c1c7-9e64-4880-b1bd-976d76b44a5f.jpg",
                                    "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-7887c1c7-9e64-4880-b1bd-976d76b44a5f.jpg"
                                }
                            ],
                            "default_value": 0,
                            "godown_itemId": 142
                        }
                    ]
                },
                {
                    "fsi_uid": 109,
                    "item_group_id": 109,
                    "item_name": "Accu Chek Arjunarishta",
                    "brand_name": "Accu Chek",
                    "category_id": 107,
                    "category_name": "Milk Thistle (Liver Care)",
                    "variant": "",
                    "isMedicine": 0,
                    "item_master": [
                        {
                            "stit_ID": 108,
                            "quantity": "",
                            "stit_fsiuid": 109,
                            "stock_available": 0,
                            "selling_prize": 0,
                            "isMedicine": 0,
                            "mrp": 0,
                            "main_image": [
                                {
                                    "id": 18,
                                    "product_id": 108,
                                    "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-9d53b10a-59dc-4285-8280-2fe402085afa.jpg",
                                    "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-9d53b10a-59dc-4285-8280-2fe402085afa.jpg"
                                }
                            ],
                            "default_value": 0,
                            "godown_itemId": 162
                        }
                    ]
                },
                {
                    "fsi_uid": 110,
                    "item_group_id": 110,
                    "item_name": "Imiana Acid Phosphoric Dilution 30 ",
                    "brand_name": "Imiana",
                    "category_id": 32,
                    "category_name": "Adult Diapers",
                    "variant": "",
                    "isMedicine": 0,
                    "item_master": [
                        {
                            "stit_ID": 109,
                            "quantity": "",
                            "stit_fsiuid": 110,
                            "stock_available": 0,
                            "selling_prize": 0,
                            "isMedicine": 0,
                            "mrp": 0,
                            "main_image": [
                                {
                                    "id": 12,
                                    "product_id": 109,
                                    "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-45e8a492-42c3-4d63-a7e2-65b7a7d988bc.jpg",
                                    "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-45e8a492-42c3-4d63-a7e2-65b7a7d988bc.jpg"
                                }
                            ],
                            "default_value": 0,
                            "godown_itemId": 768
                        }
                    ]
                },
                {
                    "fsi_uid": 111,
                    "item_group_id": 111,
                    "item_name": "Accu Chek Heart Guard Capsule",
                    "brand_name": "Accu Chek",
                    "category_id": 4,
                    "category_name": "Accu-Check",
                    "variant": "Tablet",
                    "isMedicine": 0,
                    "item_master": [
                        {
                            "stit_ID": 110,
                            "quantity": "52tabs",
                            "stit_fsiuid": 111,
                            "stock_available": 0,
                            "selling_prize": 0,
                            "isMedicine": 0,
                            "mrp": 0,
                            "main_image": [
                                {
                                    "id": 9,
                                    "product_id": 110,
                                    "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-0ea1616c-9314-406f-965d-c90bda802818.jpg",
                                    "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-0ea1616c-9314-406f-965d-c90bda802818.jpg"
                                }
                            ],
                            "default_value": 0,
                            "godown_itemId": 128
                        }
                    ]
                },
                {
                    "fsi_uid": 114,
                    "item_group_id": 114,
                    "item_name": "Himalaya Neem Leaf Juice",
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
                            "isMedicine": 0,
                            "mrp": 0,
                            "main_image": [
                                {
                                    "id": 6,
                                    "product_id": 113,
                                    "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-07516323-5472-4908-8cd6-d3bcb09bfe6a.jpg",
                                    "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-07516323-5472-4908-8cd6-d3bcb09bfe6a.jpg"
                                }
                            ],
                            "default_value": 0,
                            "godown_itemId": 285
                        }
                    ]
                },
                {
                    "fsi_uid": 115,
                    "item_group_id": 115,
                    "item_name": "Baby Staples Aloe Vera Juice",
                    "brand_name": "Baby Staples",
                    "category_id": 31,
                    "category_name": "Ayurvedic Medicines",
                    "variant": "100",
                    "isMedicine": 0,
                    "item_master": [
                        {
                            "stit_ID": 114,
                            "quantity": "10",
                            "stit_fsiuid": 115,
                            "stock_available": 0,
                            "selling_prize": 0,
                            "isMedicine": 0,
                            "mrp": 0,
                            "main_image": [
                                {
                                    "id": 3,
                                    "product_id": 114,
                                    "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-057aec43-bf91-446a-b6c3-6bcf26edad7e.jpg",
                                    "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-057aec43-bf91-446a-b6c3-6bcf26edad7e.jpg"
                                }
                            ],
                            "default_value": 0,
                            "godown_itemId": 227
                        }
                    ]
                },
                {
                    "fsi_uid": 118,
                    "item_group_id": 118,
                    "item_name": "Himalaya Wipe Clear Ache Lotion",
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
                            "isMedicine": 0,
                            "mrp": 0,
                            "main_image": [
                                {
                                    "id": 1,
                                    "product_id": 115,
                                    "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-cb8767db-7220-4908-805b-04f14010ad75.jpg",
                                    "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-cb8767db-7220-4908-805b-04f14010ad75.jpg"
                                }
                            ],
                            "default_value": 0,
                            "godown_itemId": 441
                        }
                    ]
                }
            ],
            "total_count": 39,
            "min_count": 9
        },
        {
            "id": 7,
            "screen": "Home",
            "type": "Brand",
            "type_id": 7,
            "image_url": "https://devassetbrm.s3-ap-southeast-1.amazonaws.com/product/main/f52a29ff-b637-4db1-ad8e-04073d73f396",
            "description": "",
            "title": "Top Brand",
            "background_img": "https://devassetbrm.s3-ap-southeast-1.amazonaws.com/product/main/f52a29ff-b637-4db1-ad8e-04073d73f396",
            "is_active": 1,
            "sub_id": 0,
            "order": 5,
            "delivery_type": 1,
            "value": [
                {
                    "brand_id": 19,
                    "brand_name": "Dabur",
                    "manufacture_id": 1,
                    "img_url": "http://dev.admin.mypharmacy.velosit.in/resources/brand/dabur.png",
                    "img_name": "",
                    "top_brand": 1,
                    "status": "1"
                },
                {
                    "brand_id": 23,
                    "brand_name": "Himalaya",
                    "manufacture_id": 1,
                    "img_url": "http://dev.admin.mypharmacy.velosit.in/resources/brand/himalaya.png",
                    "img_name": "himalaya",
                    "top_brand": 1,
                    "status": "1"
                },
                {
                    "brand_id": 24,
                    "brand_name": "Vicks",
                    "manufacture_id": 1,
                    "img_url": "http://dev.admin.mypharmacy.velosit.in/resources/gogos/viks.png",
                    "img_name": "vicks",
                    "top_brand": 1,
                    "status": "1"
                },
                {
                    "brand_id": 29,
                    "brand_name": "HealthVit",
                    "manufacture_id": 1,
                    "img_url": "http://dev.admin.mypharmacy.velosit.in/resources/gogos/healthvit.png",
                    "img_name": null,
                    "top_brand": 1,
                    "status": "1"
                },
                {
                    "brand_id": 32,
                    "brand_name": "Accu Chek",
                    "manufacture_id": 1,
                    "img_url": "http://dev.admin.mypharmacy.velosit.in/resources/brand/accuchek.png",
                    "img_name": null,
                    "top_brand": 1,
                    "status": "1"
                },
                {
                    "brand_id": 33,
                    "brand_name": "PediaSure",
                    "manufacture_id": 1,
                    "img_url": "http://dev.admin.mypharmacy.velosit.in/resources/gogos/pediasure.png",
                    "img_name": null,
                    "top_brand": 1,
                    "status": "1"
                },
                {
                    "brand_id": 36,
                    "brand_name": "Bakson's",
                    "manufacture_id": 1,
                    "img_url": "https://gogomedsdev.s3-ap-southeast-1.amazonaws.com/brand/be0dff56-d19e-497a-ae76-c5109fb7c7a6.jpg",
                    "img_name": null,
                    "top_brand": 1,
                    "status": "1"
                },
                {
                    "brand_id": 37,
                    "brand_name": "Natura",
                    "manufacture_id": 1,
                    "img_url": null,
                    "img_name": null,
                    "top_brand": 1,
                    "status": "0"
                }
            ],
            "total_count": 8,
            "min_count": 9
        },
        {
            "id": 9,
            "screen": "Home",
            "type": "Popular products",
            "type_id": 3,
            "image_url": "",
            "description": "",
            "title": "Popular products",
            "background_img": "",
            "is_active": 1,
            "sub_id": 3,
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
                            "godown_itemId": 263
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
                            "isMedicine": 0,
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
                            "godown_itemId": 321
                        },
                        {
                            "stit_ID": 6,
                            "quantity": "200ml",
                            "stit_fsiuid": 9,
                            "stock_available": 30,
                            "selling_prize": 99.5,
                            "isMedicine": 0,
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
                            "godown_itemId": 658
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
                            "godown_itemId": 95
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
                            "isMedicine": 0,
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
                            "godown_itemId": 83
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
                                    "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-09fc243d-fa5d-4f70-b752-386fafb2ce74.jpg",
                                    "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-09fc243d-fa5d-4f70-b752-386fafb2ce74.jpg"
                                }
                            ],
                            "default_value": 0,
                            "godown_itemId": 723
                        }
                    ]
                },
                {
                    "fsi_uid": 15,
                    "item_group_id": 15,
                    "item_name": "Healthgenie SV 201 Steam Vaporizer",
                    "brand_name": "Healthgenie",
                    "category_id": 7,
                    "category_name": "Vaporizers",
                    "variant": "box",
                    "isMedicine": 0,
                    "item_master": [
                        {
                            "stit_ID": 16,
                            "quantity": "1 unit",
                            "stit_fsiuid": 15,
                            "stock_available": 0,
                            "selling_prize": 0,
                            "isMedicine": 0,
                            "mrp": 0,
                            "main_image": [
                                {
                                    "id": 266,
                                    "product_id": 16,
                                    "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-0549886a-6072-44ec-936e-70f496d53d4e.jpg",
                                    "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-0549886a-6072-44ec-936e-70f496d53d4e.jpg"
                                }
                            ],
                            "default_value": 0,
                            "godown_itemId": 70
                        }
                    ]
                },
                {
                    "fsi_uid": 16,
                    "item_group_id": 16,
                    "item_name": "GNC Vitamin C 500mg Tablet",
                    "brand_name": "GNC",
                    "category_id": 15,
                    "category_name": "Vitamin C",
                    "variant": "",
                    "isMedicine": 0,
                    "item_master": [
                        {
                            "stit_ID": 17,
                            "quantity": "90 tablets",
                            "stit_fsiuid": 16,
                            "stock_available": 0,
                            "selling_prize": 0,
                            "isMedicine": 0,
                            "mrp": 0,
                            "main_image": [
                                {
                                    "id": 268,
                                    "product_id": 17,
                                    "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-7ee02656-1385-4439-85e8-05b384164dc1.jpg",
                                    "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-7ee02656-1385-4439-85e8-05b384164dc1.jpg"
                                }
                            ],
                            "default_value": 0,
                            "godown_itemId": 94
                        }
                    ]
                },
                {
                    "fsi_uid": 17,
                    "item_group_id": 17,
                    "item_name": "GNC Vitamin C 1000mg with Bioflavonoid",
                    "brand_name": "GNC",
                    "category_id": 15,
                    "category_name": "Vitamin C",
                    "variant": "",
                    "isMedicine": 0,
                    "item_master": [
                        {
                            "stit_ID": 18,
                            "quantity": "180 caplets",
                            "stit_fsiuid": 17,
                            "stock_available": 0,
                            "selling_prize": 0,
                            "isMedicine": 0,
                            "mrp": 0,
                            "main_image": [
                                {
                                    "id": 276,
                                    "product_id": 18,
                                    "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-c9783954-db57-4e1d-929c-40a70117ca27.jpg",
                                    "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-c9783954-db57-4e1d-929c-40a70117ca27.jpg"
                                }
                            ],
                            "default_value": 0,
                            "godown_itemId": 194
                        }
                    ]
                },
                {
                    "fsi_uid": 19,
                    "item_group_id": 19,
                    "item_name": "  Organic India Turmeric Formula Veg Capsule",
                    "brand_name": "  Organic India",
                    "category_id": 3,
                    "category_name": "OrganicIndia",
                    "variant": "Vegcaps",
                    "isMedicine": 0,
                    "item_master": [
                        {
                            "stit_ID": 20,
                            "quantity": " 60 vegicaps",
                            "stit_fsiuid": 19,
                            "stock_available": 0,
                            "selling_prize": 0,
                            "isMedicine": 0,
                            "mrp": 0,
                            "main_image": [
                                {
                                    "id": 121,
                                    "product_id": 20,
                                    "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-fc1f7b60-8c3d-4e28-b619-1e65000d3854.jpg",
                                    "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-fc1f7b60-8c3d-4e28-b619-1e65000d3854.jpg"
                                }
                            ],
                            "default_value": 0,
                            "godown_itemId": 918
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
                            "isMedicine": 0,
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
                            "godown_itemId": 457
                        }
                    ]
                }
            ],
            "total_count": 26,
            "min_count": 9
        },
        {
            "id": 25,
            "screen": "Home",
            "type": "shop by concern",
            "type_id": 11,
            "image_url": "https://devassetbrm.s3-ap-southeast-1.amazonaws.com/product/main/f52a29ff-b637-4db1-ad8e-04073d73f396",
            "description": "Leorem hayr tfhfh v ff",
            "title": "Shop by Health Concern",
            "background_img": "https://devassetbrm.s3-ap-southeast-1.amazonaws.com/product/main/f52a29ff-b637-4db1-ad8e-04073d73f396",
            "is_active": 1,
            "sub_id": 0,
            "order": 6,
            "delivery_type": 1,
            "value": [
                {
                    "disease_id": 1,
                    "disease_name": "Cough",
                    "disease_description": "Bad Throat",
                    "disease_image": "https://gogomedsdev.s3-ap-southeast-1.amazonaws.com/AWSDISEASEDBUCKETFOLDER52359f69-1adb-4f8e-ac03-5e4b30fe5ba4.jpg",
                    "dept_id": null,
                    "dept_name": null,
                    "disease_created_on": "2020-04-01 16:02:00",
                    "disease_updated_on": null
                },
                {
                    "disease_id": 2,
                    "disease_name": "Fever",
                    "disease_description": "Increased temperature",
                    "disease_image": "https://gogomedsdev.s3-ap-southeast-1.amazonaws.com/disease/3b483663-0cad-48b9-bcf0-9d24f04cd6d4.jpg",
                    "dept_id": null,
                    "dept_name": null,
                    "disease_created_on": "2020-04-01 16:02:34",
                    "disease_updated_on": "2020-04-03 14:39:43"
                },
                {
                    "disease_id": 3,
                    "disease_name": "Vomiting",
                    "disease_description": "Fever",
                    "disease_image": "https://gogomedsdev.s3-ap-southeast-1.amazonaws.com/disease/3afeaf60-62a9-4e47-926b-99b052055108.jpg",
                    "dept_id": null,
                    "dept_name": null,
                    "disease_created_on": "2020-04-02 14:36:13",
                    "disease_updated_on": null
                },
                {
                    "disease_id": 4,
                    "disease_name": "Cold",
                    "disease_description": "Running Nose",
                    "disease_image": "https://gogomedsdev.s3-ap-southeast-1.amazonaws.com/AWSDISEASEDBUCKETFOLDER15305aeb-f016-4d86-a85f-10b1a40f971a.jpg",
                    "dept_id": null,
                    "dept_name": null,
                    "disease_created_on": "2020-04-13 13:46:45",
                    "disease_updated_on": null
                }
            ],
            "total_count": 4,
            "min_count": 9
        }
    ]
}
  

```
