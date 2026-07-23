# Popular Products list

---
Brand Screen

### Details

| Method | Uri   | Authorization |
| : |   :-   |  :  |
| POST | `product/popularproductslist` | NO |

### Request

```json
{
"id" :"9",
 "category_id" :""
}
```

### Response

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

```
