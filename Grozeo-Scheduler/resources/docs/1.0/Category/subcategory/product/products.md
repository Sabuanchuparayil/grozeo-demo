# Subcategory/product 

---
Product

### Details

| Method | Uri   | Authorization |
| : |   :-   |  :  |
| POST | `/subcategory/products` | NO |




### Request for Product

```json
{
    
	"category_id":2

}

```

### Response for product

```json
{
    "status": "ok",
    "data": [
        {
            "fsi_uid": 2,
            "item_group_id": 2,
            "item_name": "Dolo",
            "brand_name": "SBL",
            "category_id": 2,
            "category_name": "Cough",
            "variant": "100grams",
            "item_master": [
                {
                    "stit_ID": 1,
                    "quantity": "10",
                    "stit_fsiuid": 2,
                    "main_image": [],
                    "default_value": 0,
                    "stock_available": 0,
                    "mrp": 0,
                    "selling_price": 0,
                    "godown_itemId": 393
                }
            ]
        },
        {
            "fsi_uid": 3,
            "item_group_id": 3,
            "item_name": "Dolo",
            "brand_name": "SBL",
            "category_id": 2,
            "category_name": "Cough",
            "variant": "250grams",
            "item_master": [
                {
                    "stit_ID": 2,
                    "quantity": "10",
                    "stit_fsiuid": 3,
                    "main_image": [],
                    "default_value": 0,
                    "stock_available": 0,
                    "mrp": 0,
                    "selling_price": 0,
                    "godown_itemId": 265
                }
            ]
        }
    ]
}
```


