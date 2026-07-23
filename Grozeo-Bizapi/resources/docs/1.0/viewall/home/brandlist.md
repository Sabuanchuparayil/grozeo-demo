# Home Brand list

---
Brand Screen

### Details

| Method | Uri   | Authorization |
| : |   :-   |  :  |
| POST | `product/brandlist` | NO |


Home Brand List
**************************************
### Request

```json
{
"id" :"",
 "category_id" :""
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
            "brand_id": 6,
            "brand_name": "Zemaica Healthcare",
            "img_url": null,
            "img_name": null,
            "top_brand": null,
            "status": "1"
        },
        {
            "brand_id": 7,
            "brand_name": "Ayursun Pharma",
            "img_url": null,
            "img_name": null,
            "top_brand": null,
            "status": "1"
        },
        {
            "brand_id": 8,
            "brand_name": "Baby Staples",
            "img_url": null,
            "img_name": null,
            "top_brand": null,
            "status": "1"
        },
        {
            "brand_id": 9,
            "brand_name": "Dhathri",
            "img_url": null,
            "img_name": null,
            "top_brand": null,
            "status": "1"
        },
        {
            "brand_id": 10,
            "brand_name": "Donum Naturals",
            "img_url": null,
            "img_name": null,
            "top_brand": null,
            "status": "1"
        },
        {
            "brand_id": 11,
            "brand_name": "Durex Play",
            "img_url": null,
            "img_name": null,
            "top_brand": null,
            "status": "1"
        }
    
   
    ]
}
Category Brand List
*****************************
```
### Request

```json
{
"id" :"14",
"category_id" :"1"
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

````