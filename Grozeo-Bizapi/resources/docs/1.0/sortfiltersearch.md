# SORT FILTER SEARCH

---
sortfilterSearch API

### Details

| Method | Uri   | Authorization |
| : |   :-   |  :  |
| POST | `product/sortFilterSearch` | NO |

Home category Featured product

### Request

```json
{
"id" :"2",
"request_id":"1",
"branch_id":"1",
"innerscreen_id":"11",
"sort":2,
"filter":{"3":"",
"4":"",
"5":""
}
}
```

### Resonponse

```json
{
    "status": "ok",
    "data": [
        {
            "currentpage": 1,
            "ProductList": [
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
                            "selling_prize": 12,
                            "mrp": 0,
                            "main_image": [
                                {
                                    "id": 10,
                                    "product_id": 15,
                                    "image_url": "http://dev.admin.mypharmacy.velosit.in/resources/gogopdts/kofletsyrup.jpg",
                                    "image_thumb_url": null
                                }
                            ],
                            "default_value": 0,
                            "selling_price": 0,
                            "godown_itemId": 11
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
                            "selling_prize": 15,
                            "mrp": 0,
                            "main_image": [
                                {
                                    "id": 8,
                                    "product_id": 13,
                                    "image_url": "http://dev.admin.mypharmacy.velosit.in/resources/gogopdts/koflet1.jpg",
                                    "image_thumb_url": null
                                }
                            ],
                            "default_value": 0,
                            "selling_price": 0,
                            "godown_itemId": 262
                        },
                        {
                            "stit_ID": 14,
                            "quantity": "200 lozenges",
                            "stit_fsiuid": 13,
                            "stock_available": 0,
                            "selling_prize": 15,
                            "mrp": 0,
                            "main_image": [
                                {
                                    "id": 9,
                                    "product_id": 14,
                                    "image_url": "http://dev.admin.mypharmacy.velosit.in/resources/gogopdts/koflet200.jpg",
                                    "image_thumb_url": null
                                }
                            ],
                            "default_value": 0,
                            "selling_price": 0,
                            "godown_itemId": 444
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
                            "selling_prize": 15,
                            "mrp": 0,
                            "main_image": [],
                            "default_value": 0,
                            "selling_price": 0,
                            "godown_itemId": 366
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
                            "selling_prize": 12,
                            "mrp": 0,
                            "main_image": [],
                            "default_value": 0,
                            "selling_price": 0,
                            "godown_itemId": 710
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
                            "selling_prize": 12,
                            "mrp": 0,
                            "main_image": [],
                            "default_value": 0,
                            "selling_price": 0,
                            "godown_itemId": 828
                        }
                    ]
                }
            ],
            "first_page_url": "http://localhost/my-pharmacy-api/public/api/product/sortFilterSearch?page=1",
            "from": 1,
            "last_page": 1,
            "last_page_url": "http://localhost/my-pharmacy-api/public/api/product/sortFilterSearch?page=1",
            "next_page_url": null,
            "path": "http://localhost/my-pharmacy-api/public/api/product/sortFilterSearch",
            "per_page": 10,
            "prev_page_url": null,
            "to": 5,
            "total": 5
        }
    ]
}
```
Home category Brand

### Request

```json
{
"id" :"2",
"request_id":"1",
"innerscreen_id":"14",
"sort":2,
"branch_id":"1",
"filter":{"3":"",
"4":"",
"5":""
}
}
```

### Resonponse

```json
{
    "status": "ok",
    "data": [
        {
            "currentpage": 1,
            "ProductList": [
                {
                    "fsi_uid": 50,
                    "item_group_id": 50,
                    "item_name": "OMEE-MPS",
                    "brand_name": "ALUMINIUM HYDROXIDE",
                    "category_id": 1,
                    "category_name": "Antacids, Antireflux Agents & Antiulcerants",
                    "variant": "Liquid Mint",
                    "isMedicine": 1,
                    "item_master": [
                        {
                            "stit_ID": 49,
                            "quantity": "",
                            "stit_fsiuid": 50,
                            "stock_available": 0,
                            "selling_prize": 12,
                            "mrp": 0,
                            "isMedicine": 1,
                            "main_image": [],
                            "default_value": 0,
                            "selling_price": 0,
                            "godown_itemId": 511
                        }
                    ]
                },
                {
                    "fsi_uid": 59,
                    "item_group_id": 59,
                    "item_name": "MUCAINE GEL MINT",
                    "brand_name": "ALUMINIUM HYDROXIDE",
                    "category_id": 1,
                    "category_name": "Antacids, Antireflux Agents & Antiulcerants",
                    "variant": "Gel Mint",
                    "isMedicine": 1,
                    "item_master": [
                        {
                            "stit_ID": 61,
                            "quantity": "",
                            "stit_fsiuid": 59,
                            "stock_available": 0,
                            "selling_prize": 12,
                            "mrp": 0,
                            "isMedicine": 1,
                            "main_image": [],
                            "default_value": 0,
                            "selling_price": 0,
                            "godown_itemId": 479
                        }
                    ]
                },
                {
                    "fsi_uid": 66,
                    "item_group_id": 66,
                    "item_name": "RIFLUX",
                    "brand_name": "ALUMINIUM HYDROXIDE",
                    "category_id": 1,
                    "category_name": "Antacids, Antireflux Agents & Antiulcerants",
                    "variant": "Liquid",
                    "isMedicine": 1,
                    "item_master": [
                        {
                            "stit_ID": 68,
                            "quantity": "",
                            "stit_fsiuid": 66,
                            "stock_available": 0,
                            "selling_prize": 12,
                            "mrp": 0,
                            "isMedicine": 1,
                            "main_image": [],
                            "default_value": 0,
                            "selling_price": 0,
                            "godown_itemId": 140
                        }
                    ]
                },
                {
                    "fsi_uid": 78,
                    "item_group_id": 78,
                    "item_name": "RIFLUX FORTE",
                    "brand_name": "ALUMINIUM HYDROXIDE",
                    "category_id": 1,
                    "category_name": "Antacids, Antireflux Agents & Antiulcerants",
                    "variant": "Liquid",
                    "isMedicine": 1,
                    "item_master": [
                        {
                            "stit_ID": 80,
                            "quantity": "",
                            "stit_fsiuid": 78,
                            "stock_available": 0,
                            "selling_prize": 12,
                            "mrp": 0,
                            "isMedicine": 1,
                            "main_image": [],
                            "default_value": 0,
                            "selling_price": 0,
                            "godown_itemId": 867
                        }
                    ]
                },
                {
                    "fsi_uid": 112,
                    "item_group_id": 112,
                    "item_name": "ACIGON",
                    "brand_name": "ALUMINIUM HYDROXIDE",
                    "category_id": 1,
                    "category_name": "Antacids, Antireflux Agents & Antiulcerants",
                    "variant": "",
                    "isMedicine": 1,
                    "item_master": [
                        {
                            "stit_ID": 111,
                            "quantity": "",
                            "stit_fsiuid": 112,
                            "stock_available": 0,
                            "selling_prize": 12,
                            "mrp": 0,
                            "isMedicine": 1,
                            "main_image": [],
                            "default_value": 0,
                            "selling_price": 0,
                            "godown_itemId": 731
                        }
                    ]
                }
            ],
            "first_page_url": "http://localhost/my-pharmacy-api/public/api/product/sortFilterSearch?page=1",
            "from": 1,
            "last_page": 1,
            "last_page_url": "http://localhost/my-pharmacy-api/public/api/product/sortFilterSearch?page=1",
            "next_page_url": null,
            "path": "http://localhost/my-pharmacy-api/public/api/product/sortFilterSearch",
            "per_page": 10,
            "prev_page_url": null,
            "to": 5,
            "total": 5
        }
    ]
}
```
Home category Product

### Request

```json
{
"id" :"2",
"request_id":"1",
"innerscreen_id":"13",
"sort":2,
"branch_id":"1",
"filter":{"3":"",
"4":"",
"5":""
}
}
```

### Resonponse

```json
{
    "status": "ok",
    "data": [
        {
            "currentpage": 1,
            "ProductList": [
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
                            "selling_prize": 12,
                            "mrp": 0,
                            "main_image": [],
                            "default_value": 0,
                            "selling_price": 0,
                            "godown_itemId": 109
                        }
                    ]
                },
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
                            "selling_prize": 12,
                            "mrp": 0,
                            "main_image": [],
                            "default_value": 0,
                            "selling_price": 0,
                            "godown_itemId": 159
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
                            "selling_prize": 12,
                            "mrp": 0,
                            "main_image": [],
                            "default_value": 0,
                            "selling_price": 0,
                            "godown_itemId": 479
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
                            "selling_prize": 12,
                            "mrp": 0,
                            "main_image": [
                                {
                                    "id": 10,
                                    "product_id": 15,
                                    "image_url": "http://dev.admin.mypharmacy.velosit.in/resources/gogopdts/kofletsyrup.jpg",
                                    "image_thumb_url": null
                                }
                            ],
                            "default_value": 0,
                            "selling_price": 0,
                            "godown_itemId": 942
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
                            "selling_prize": 15,
                            "mrp": 0,
                            "main_image": [],
                            "default_value": 0,
                            "selling_price": 0,
                            "godown_itemId": 561
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
                            "selling_prize": 15,
                            "mrp": 0,
                            "main_image": [
                                {
                                    "id": 8,
                                    "product_id": 13,
                                    "image_url": "http://dev.admin.mypharmacy.velosit.in/resources/gogopdts/koflet1.jpg",
                                    "image_thumb_url": null
                                }
                            ],
                            "default_value": 0,
                            "selling_price": 0,
                            "godown_itemId": 171
                        },
                        {
                            "stit_ID": 14,
                            "quantity": "200 lozenges",
                            "stit_fsiuid": 13,
                            "stock_available": 0,
                            "selling_prize": 15,
                            "mrp": 0,
                            "main_image": [
                                {
                                    "id": 9,
                                    "product_id": 14,
                                    "image_url": "http://dev.admin.mypharmacy.velosit.in/resources/gogopdts/koflet200.jpg",
                                    "image_thumb_url": null
                                }
                            ],
                            "default_value": 0,
                            "selling_price": 0,
                            "godown_itemId": 363
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
                            "selling_prize": 12,
                            "mrp": 0,
                            "main_image": [],
                            "default_value": 0,
                            "selling_price": 0,
                            "godown_itemId": 850
                        },
                        {
                            "stit_ID": 6,
                            "quantity": "200ml",
                            "stit_fsiuid": 9,
                            "stock_available": 0,
                            "selling_prize": 12,
                            "mrp": 0,
                            "main_image": [],
                            "default_value": 0,
                            "selling_price": 0,
                            "godown_itemId": 768
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
                            "selling_prize": 12,
                            "mrp": 0,
                            "main_image": [
                                {
                                    "id": 13,
                                    "product_id": 7,
                                    "image_url": "http://dev.admin.mypharmacy.velosit.in/resources/gogopdts/vickscoughdrops.jpg",
                                    "image_thumb_url": null
                                }
                            ],
                            "default_value": 0,
                            "selling_price": 0,
                            "godown_itemId": 684
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
                            "selling_prize": 12,
                            "mrp": 0,
                            "main_image": [],
                            "default_value": 0,
                            "selling_price": 0,
                            "godown_itemId": 798
                        },
                        {
                            "stit_ID": 12,
                            "quantity": "180ml",
                            "stit_fsiuid": 12,
                            "stock_available": 0,
                            "selling_prize": 12,
                            "mrp": 0,
                            "main_image": [],
                            "default_value": 0,
                            "selling_price": 0,
                            "godown_itemId": 374
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
                            "selling_prize": 12,
                            "mrp": 0,
                            "main_image": [],
                            "default_value": 0,
                            "selling_price": 0,
                            "godown_itemId": 850
                        }
                    ]
                }
            ],
            "first_page_url": "http://localhost/my-pharmacy-api/public/api/product/sortFilterSearch?page=1",
            "from": 1,
            "last_page": 2,
            "last_page_url": "http://localhost/my-pharmacy-api/public/api/product/sortFilterSearch?page=2",
            "next_page_url": "http://localhost/my-pharmacy-api/public/api/product/sortFilterSearch?page=2",
            "path": "http://localhost/my-pharmacy-api/public/api/product/sortFilterSearch",
            "per_page": 10,
            "prev_page_url": null,
            "to": 10,
            "total": 18
        }
    ]
}
```

Home Featured Product

### Request

```json
{
"id" :"3",
"request_id":"",
"innerscreen_id":"",
"sort":2,
"branch_id":"1",
"filter":{"3":"",
"4":"",
"5":""
}
}
```

### Resonponse

```json
{
    "status": "ok",
    "data": [
        {
            "currentpage": 1,
            "ProductList": [
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
                            "selling_prize": 12,
                            "isMedicine": 0,
                            "mrp": 0,
                            "main_image": [
                                {
                                    "id": 10,
                                    "product_id": 15,
                                    "image_url": "http://dev.admin.mypharmacy.velosit.in/resources/gogopdts/kofletsyrup.jpg",
                                    "image_thumb_url": null
                                }
                            ],
                            "default_value": 0,
                            "selling_price": 0,
                            "godown_itemId": 60
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
                            "selling_prize": 15,
                            "isMedicine": 0,
                            "mrp": 0,
                            "main_image": [
                                {
                                    "id": 8,
                                    "product_id": 13,
                                    "image_url": "http://dev.admin.mypharmacy.velosit.in/resources/gogopdts/koflet1.jpg",
                                    "image_thumb_url": null
                                }
                            ],
                            "default_value": 0,
                            "selling_price": 0,
                            "godown_itemId": 688
                        },
                        {
                            "stit_ID": 14,
                            "quantity": "200 lozenges",
                            "stit_fsiuid": 13,
                            "stock_available": 0,
                            "selling_prize": 15,
                            "isMedicine": 0,
                            "mrp": 0,
                            "main_image": [
                                {
                                    "id": 9,
                                    "product_id": 14,
                                    "image_url": "http://dev.admin.mypharmacy.velosit.in/resources/gogopdts/koflet200.jpg",
                                    "image_thumb_url": null
                                }
                            ],
                            "default_value": 0,
                            "selling_price": 0,
                            "godown_itemId": 882
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
                            "selling_prize": 15,
                            "isMedicine": 0,
                            "mrp": 0,
                            "main_image": [],
                            "default_value": 0,
                            "selling_price": 0,
                            "godown_itemId": 200
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
                            "selling_prize": 12,
                            "isMedicine": 0,
                            "mrp": 0,
                            "main_image": [],
                            "default_value": 0,
                            "selling_price": 0,
                            "godown_itemId": 495
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
                            "selling_prize": 12,
                            "isMedicine": 0,
                            "mrp": 0,
                            "main_image": [],
                            "default_value": 0,
                            "selling_price": 0,
                            "godown_itemId": 523
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
                            "selling_prize": 12,
                            "isMedicine": 0,
                            "mrp": 0,
                            "main_image": [
                                {
                                    "id": 3,
                                    "product_id": 50,
                                    "image_url": "http://dev.admin.mypharmacy.velosit.in/resources/gogopdts/pediasurechoclate.jpg",
                                    "image_thumb_url": null
                                }
                            ],
                            "default_value": 0,
                            "selling_price": 0,
                            "godown_itemId": 287
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
                            "selling_prize": 12,
                            "isMedicine": 0,
                            "mrp": 0,
                            "main_image": [
                                {
                                    "id": 6,
                                    "product_id": 55,
                                    "image_url": "http://dev.admin.mypharmacy.velosit.in/resources/gogopdts/pedidelight.jpg",
                                    "image_thumb_url": null
                                }
                            ],
                            "default_value": 0,
                            "selling_price": 0,
                            "godown_itemId": 912
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
                            "selling_prize": 12,
                            "isMedicine": 0,
                            "mrp": 0,
                            "main_image": [
                                {
                                    "id": 4,
                                    "product_id": 58,
                                    "image_url": "http://dev.admin.mypharmacy.velosit.in/resources/gogopdts/pediasurebadam.jpg",
                                    "image_thumb_url": null
                                }
                            ],
                            "default_value": 0,
                            "selling_price": 0,
                            "godown_itemId": 245
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
                            "selling_prize": 1,
                            "isMedicine": 0,
                            "mrp": 0,
                            "main_image": [],
                            "default_value": 0,
                            "selling_price": 0,
                            "godown_itemId": 97
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
                            "selling_prize": 12,
                            "isMedicine": 0,
                            "mrp": 0,
                            "main_image": [],
                            "default_value": 0,
                            "selling_price": 0,
                            "godown_itemId": 578
                        }
                    ]
                }
            ],
            "first_page_url": "http://localhost/my-pharmacy-api/public/api/product/sortFilterSearch?page=1",
            "from": 1,
            "last_page": 2,
            "last_page_url": "http://localhost/my-pharmacy-api/public/api/product/sortFilterSearch?page=2",
            "next_page_url": "http://localhost/my-pharmacy-api/public/api/product/sortFilterSearch?page=2",
            "path": "http://localhost/my-pharmacy-api/public/api/product/sortFilterSearch",
            "per_page": 10,
            "prev_page_url": null,
            "to": 10,
            "total": 16
        }
    ]
}
```
Home Brand product

### Request

```json
{
"id" :"7",
"request_id":"1",
"innerscreen_id":"",
"branch_id":"1",
"sort":2,
"filter":{"3":"1",
"4":"1",
"5":""
}
}
```

### Resonponse

```json
{
    "status": "ok",
    "data": [
        {
            "currentpage": 1,
            "ProductList": [
                {
                    "fsi_uid": 50,
                    "item_group_id": 50,
                    "item_name": "OMEE-MPS",
                    "brand_name": "ALUMINIUM HYDROXIDE",
                    "category_id": 1,
                    "category_name": "Antacids, Antireflux Agents & Antiulcerants",
                    "variant": "Liquid Mint",
                    "isMedicine": 1,
                    "item_master": [
                        {
                            "stit_ID": 49,
                            "quantity": "",
                            "stit_fsiuid": 50,
                            "stock_available": 0,
                            "selling_prize": 12,
                            "mrp": 0,
                            "isMedicine": 1,
                            "main_image": [],
                            "default_value": 0,
                            "selling_price": 0,
                            "godown_itemId": 433
                        }
                    ]
                },
                {
                    "fsi_uid": 59,
                    "item_group_id": 59,
                    "item_name": "MUCAINE GEL MINT",
                    "brand_name": "ALUMINIUM HYDROXIDE",
                    "category_id": 1,
                    "category_name": "Antacids, Antireflux Agents & Antiulcerants",
                    "variant": "Gel Mint",
                    "isMedicine": 1,
                    "item_master": [
                        {
                            "stit_ID": 61,
                            "quantity": "",
                            "stit_fsiuid": 59,
                            "stock_available": 0,
                            "selling_prize": 12,
                            "mrp": 0,
                            "isMedicine": 1,
                            "main_image": [],
                            "default_value": 0,
                            "selling_price": 0,
                            "godown_itemId": 458
                        }
                    ]
                },
                {
                    "fsi_uid": 66,
                    "item_group_id": 66,
                    "item_name": "RIFLUX",
                    "brand_name": "ALUMINIUM HYDROXIDE",
                    "category_id": 1,
                    "category_name": "Antacids, Antireflux Agents & Antiulcerants",
                    "variant": "Liquid",
                    "isMedicine": 1,
                    "item_master": [
                        {
                            "stit_ID": 68,
                            "quantity": "",
                            "stit_fsiuid": 66,
                            "stock_available": 0,
                            "selling_prize": 12,
                            "mrp": 0,
                            "isMedicine": 1,
                            "main_image": [],
                            "default_value": 0,
                            "selling_price": 0,
                            "godown_itemId": 790
                        }
                    ]
                },
                {
                    "fsi_uid": 78,
                    "item_group_id": 78,
                    "item_name": "RIFLUX FORTE",
                    "brand_name": "ALUMINIUM HYDROXIDE",
                    "category_id": 1,
                    "category_name": "Antacids, Antireflux Agents & Antiulcerants",
                    "variant": "Liquid",
                    "isMedicine": 1,
                    "item_master": [
                        {
                            "stit_ID": 80,
                            "quantity": "",
                            "stit_fsiuid": 78,
                            "stock_available": 0,
                            "selling_prize": 12,
                            "mrp": 0,
                            "isMedicine": 1,
                            "main_image": [],
                            "default_value": 0,
                            "selling_price": 0,
                            "godown_itemId": 353
                        }
                    ]
                },
                {
                    "fsi_uid": 112,
                    "item_group_id": 112,
                    "item_name": "ACIGON",
                    "brand_name": "ALUMINIUM HYDROXIDE",
                    "category_id": 1,
                    "category_name": "Antacids, Antireflux Agents & Antiulcerants",
                    "variant": "",
                    "isMedicine": 1,
                    "item_master": [
                        {
                            "stit_ID": 111,
                            "quantity": "",
                            "stit_fsiuid": 112,
                            "stock_available": 0,
                            "selling_prize": 12,
                            "mrp": 0,
                            "isMedicine": 1,
                            "main_image": [],
                            "default_value": 0,
                            "selling_price": 0,
                            "godown_itemId": 702
                        }
                    ]
                }
            ],
            "first_page_url": "http://localhost/my-pharmacy-api/public/api/product/sortFilterSearch?page=1",
            "from": 1,
            "last_page": 1,
            "last_page_url": "http://localhost/my-pharmacy-api/public/api/product/sortFilterSearch?page=1",
            "next_page_url": null,
            "path": "http://localhost/my-pharmacy-api/public/api/product/sortFilterSearch",
            "per_page": 10,
            "prev_page_url": null,
            "to": 5,
            "total": 5
        }
    ]
}
```
Home Popular product

### Request

```json
{
"id" :"9",
"request_id":"",
"innerscreen_id":"",
"sort":2,
"branch_id":"1",
"filter":{"3":"",
"4":"",
"5":""
}
}
```

### Resonponse

```json
{
    "status": "ok",
    "data": [
        {
            "currentpage": 1,
            "ProductList": [
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
                            "selling_prize": 12,
                            "isMedicine": 0,
                            "mrp": 0,
                            "main_image": [
                                {
                                    "id": 11,
                                    "product_id": 18,
                                    "image_url": "http://dev.admin.mypharmacy.velosit.in/resources/gogopdts/gnc100.jpg",
                                    "image_thumb_url": null
                                }
                            ],
                            "default_value": 0,
                            "selling_price": 0,
                            "godown_itemId": 725
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
                            "selling_prize": 12,
                            "isMedicine": 0,
                            "mrp": 0,
                            "main_image": [
                                {
                                    "id": 12,
                                    "product_id": 17,
                                    "image_url": "http://dev.admin.mypharmacy.velosit.in/resources/gogopdts/gnc500.jpg",
                                    "image_thumb_url": null
                                }
                            ],
                            "default_value": 0,
                            "selling_price": 0,
                            "godown_itemId": 570
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
                            "selling_prize": 1,
                            "isMedicine": 0,
                            "mrp": 0,
                            "main_image": [
                                {
                                    "id": 14,
                                    "product_id": 45,
                                    "image_url": "http://dev.admin.mypharmacy.velosit.in/resources/gogopdts/ureapura.jpg",
                                    "image_thumb_url": null
                                }
                            ],
                            "default_value": 0,
                            "selling_price": 0,
                            "godown_itemId": 703
                        }
                    ]
                }
            ],
            "first_page_url": "http://localhost/my-pharmacy-api/public/api/product/sortFilterSearch?page=1",
            "from": 1,
            "last_page": 1,
            "last_page_url": "http://localhost/my-pharmacy-api/public/api/product/sortFilterSearch?page=1",
            "next_page_url": null,
            "path": "http://localhost/my-pharmacy-api/public/api/product/sortFilterSearch",
            "per_page": 10,
            "prev_page_url": null,
            "to": 3,
            "total": 3
        }
    ]
}
```