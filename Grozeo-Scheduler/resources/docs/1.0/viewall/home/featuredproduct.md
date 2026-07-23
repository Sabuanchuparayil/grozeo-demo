# Home featured product list

---
Brand Screen

### Details

| Method | Uri   | Authorization |
| : |   :-   |  :  |
| POST | `product/viewall` | NO |

### Request


Home screen  featured product list
************************************
```json
{
"id" :"3",
"request_id":""
}
```

### Response

```json

{
    "status": "ok",
    "data": [
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
                    "mrp": null,
                    "main_image": [
                        {
                            "id": 8,
                            "product_id": 13,
                            "image_url": "http://dev.admin.mypharmacy.velosit.in/resources/gogopdts/koflet1.jpg",
                            "image_thumb_url": null
                        }
                    ]
                },
                {
                    "stit_ID": 14,
                    "quantity": "200 lozenges",
                    "stit_fsiuid": 13,
                    "stock_available": 0,
                    "selling_prize": 15,
                    "isMedicine": 0,
                    "mrp": null,
                    "main_image": [
                        {
                            "id": 9,
                            "product_id": 14,
                            "image_url": "http://dev.admin.mypharmacy.velosit.in/resources/gogopdts/koflet200.jpg",
                            "image_thumb_url": null
                        }
                    ]
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
                    "isMedicine": 0,
                    "mrp": null,
                    "main_image": [
                        {
                            "id": 10,
                            "product_id": 15,
                            "image_url": "http://dev.admin.mypharmacy.velosit.in/resources/gogopdts/kofletsyrup.jpg",
                            "image_thumb_url": null
                        }
                    ]
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
                    "mrp": null,
                    "main_image": []
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
                    "mrp": null,
                    "main_image": []
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
                    "mrp": null,
                    "main_image": []
                }
            ]
        },
        
        
        
    ]
}
 ````
### Request


Category screen  featured product list
************************************

```json
{
"id" :"11",
"request_id":"1"

}
```

```json
 {
    "status": "ok",
    "data": [
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
                    "mrp": null,
                    "main_image": [
                        {
                            "id": 8,
                            "product_id": 13,
                            "image_url": "http://dev.admin.mypharmacy.velosit.in/resources/gogopdts/koflet1.jpg",
                            "image_thumb_url": null
                        }
                    ]
                },
                {
                    "stit_ID": 14,
                    "quantity": "200 lozenges",
                    "stit_fsiuid": 13,
                    "stock_available": 0,
                    "selling_prize": 15,
                    "mrp": null,
                    "main_image": [
                        {
                            "id": 9,
                            "product_id": 14,
                            "image_url": "http://dev.admin.mypharmacy.velosit.in/resources/gogopdts/koflet200.jpg",
                            "image_thumb_url": null
                        }
                    ]
                }
            ]
        }
        
        
        
    ]
}
```

Home screen  popular product list
************************************
```json
{
"id" :"9",
"request_id":""

}
```

### response
```json
{
    "status": "ok",
    "data": [
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
                    "mrp": null,
                    "main_image": [
                        {
                            "id": 12,
                            "product_id": 17,
                            "image_url": "http://dev.admin.mypharmacy.velosit.in/resources/gogopdts/gnc500.jpg",
                            "image_thumb_url": null
                        }
                    ]
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
                    "selling_prize": 12,
                    "isMedicine": 0,
                    "mrp": null,
                    "main_image": [
                        {
                            "id": 11,
                            "product_id": 18,
                            "image_url": "http://dev.admin.mypharmacy.velosit.in/resources/gogopdts/gnc100.jpg",
                            "image_thumb_url": null
                        }
                    ]
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
                    "mrp": null,
                    "main_image": [
                        {
                            "id": 14,
                            "product_id": 45,
                            "image_url": "http://dev.admin.mypharmacy.velosit.in/resources/gogopdts/ureapura.jpg",
                            "image_thumb_url": null
                        }
                    ]
                }
            ]
        }
    ]
}
`````

Home screen  Brand product list
************************************
```json
{
"id" :"7",
"request_id":""

}
```
### Response


```json
{
    "status": "ok",
    "data": [
        {
            "brand_id": 1,
            "brand_name": "OneLife",
            "img_url": null,
            "img_name": "",
            "top_brand": null,
            "status": "1"
        },
        {
            "brand_id": 2,
            "brand_name": "Potentveda",
            "img_url": null,
            "img_name": null,
            "top_brand": null,
            "status": "1"
        },
        {
            "brand_id": 3,
            "brand_name": "Simply Nutra",
            "img_url": null,
            "img_name": null,
            "top_brand": null,
            "status": "1"
        },
        {
            "brand_id": 4,
            "brand_name": "Soursop",
            "img_url": null,
            "img_name": null,
            "top_brand": null,
            "status": "1"
        },
        {
            "brand_id": 5,
            "brand_name": "Surjichem Herbs",
            "img_url": null,
            "img_name": null,
            "top_brand": null,
            "status": "1"
        },
        
        
        {
            "brand_id": 21,
            "brand_name": "Bio India",
            "img_url": null,
            "img_name": null,
            "top_brand": null,
            "status": "1"
        },
        {
            "brand_id": 22,
            "brand_name": "Allel",
            "img_url": null,
            "img_name": null,
            "top_brand": null,
            "status": "1"
        },
        {
            "brand_id": 23,
            "brand_name": "Himalaya",
            "img_url": "http://dev.admin.mypharmacy.velosit.in/resources/brand/himalaya.png",
            "img_name": "himalaya",
            "top_brand": 1,
            "status": "1"
        },
        {
            "brand_id": 24,
            "brand_name": "Vicks",
            "img_url": "http://dev.admin.mypharmacy.velosit.in/resources/gogos/viks.png",
            "img_name": "vicks",
            "top_brand": 1,
            "status": "1"
        },
        
        {
            "brand_id": 29,
            "brand_name": "HealthVit",
            "img_url": "http://dev.admin.mypharmacy.velosit.in/resources/gogos/healthvit.png",
            "img_name": null,
            "top_brand": 1,
            "status": "1"
        },
       
        {
            "brand_id": 36,
            "brand_name": "Bakson's",
            "img_url": null,
            "img_name": null,
            "top_brand": 0,
            "status": "1"
        }
    ]
}

```

Category screen  Brand product list
************************************
```json
{
"id" :"7",
"request_id":"1"

}
```

### Response
```json
{
    "status": "ok",
    "data": [
        {
            "brand_id": 1,
            "brand_name": "OneLife",
            "img_url": null,
            "img_name": "",
            "top_brand": null,
            "status": "1"
        }
    ]
}
```

Product screen  Brand  list
************************************
```json

{
"id" :"16",
"request_id":"1"

}


```

### Response
```json
{
    "status": "ok",
    "data": [
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
                    "godown_itemId": 87
                }
            ]
        },
        {
            "fsi_uid": 52,
            "item_group_id": 52,
            "item_name": "ANCOOL",
            "brand_name": "ALUMINIUM HYDROXIDE",
            "category_id": 1,
            "category_name": "Antacids, Antireflux Agents & Antiulcerants",
            "variant": "SF Suspension",
            "isMedicine": 1,
            "item_master": [
                {
                    "stit_ID": 51,
                    "quantity": "",
                    "stit_fsiuid": 52,
                    "stock_available": 0,
                    "selling_prize": 12,
                    "mrp": 0,
                    "isMedicine": 1,
                    "main_image": [],
                    "default_value": 0,
                    "selling_price": 0,
                    "godown_itemId": 743
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
                    "godown_itemId": 477
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
                    "godown_itemId": 260
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
                    "godown_itemId": 417
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
                    "godown_itemId": 720
                }
            ]
        }
    ]
}

```

Category screen  sub Category list
************************************
```json


{
"id" :"12",
"request_id":"1"

}

```
### Response

```json
{
    "status": "ok",
    "data": [
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
    ]
}

```