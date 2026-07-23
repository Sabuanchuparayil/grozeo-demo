# SORT FILTER

---
sortfilter API

### Details

| Method | Uri   | Authorization |
| : |   :-   |  :  |
| POST | `product/sortFilter` | NO |


### Request
```json
{
"screen":"Subcategory",
"required_id":5

}


```


### Response

```json
{
    "status": "ok",
    "data": {
        "sort": [
            {
                "id": 3,
                "name": "price",
                "type": "Text",
                "status": 1,
                "sort_filter": 0,
                "value": [
                    {
                        "id": 1,
                        "name": "HIGH TO LOW"
                    },
                    {
                        "id": 2,
                        "name": "LOW TO HIGH"
                    }
                ]
            }
        ],
        "filter": [
            {
                "id": 1,
                "name": "Brand",
                "type": "Text",
                "status": 1,
                "sort_filter": 1,
                "value": [
                    {
                        "brand_id": 33,
                        "brand_name": "PediaSure",
                        "manufacture_id": 1,
                        "img_url": "http://dev.admin.mypharmacy.velosit.in/resources/gogos/pediasure.png",
                        "img_name": null,
                        "top_brand": 1,
                        "status": "1"
                    }
                ]
            },
            {
                "id": 2,
                "name": "Category",
                "type": "Text",
                "status": 1,
                "sort_filter": 1,
                "value": [
                    {
                        "id": 1,
                        "parent_category_name": "Winter Care"
                    },
                    {
                        "id": 2,
                        "parent_category_name": "Featured"
                    },
                    {
                        "id": 3,
                        "parent_category_name": "Diabetes"
                    },
                    {
                        "id": 4,
                        "parent_category_name": "Personal Care"
                    },
                    {
                        "id": 5,
                        "parent_category_name": "Fitness & Supplements"
                    },
                    {
                        "id": 6,
                        "parent_category_name": "Healthcare Devices"
                    },
                    {
                        "id": 7,
                        "parent_category_name": "Health Conditions"
                    },
                    {
                        "id": 8,
                        "parent_category_name": "Ayurveda Products"
                    },
                    {
                        "id": 9,
                        "parent_category_name": "Homeopathy"
                    },
                    {
                        "id": 10,
                        "parent_category_name": "Dry creams"
                    }
                ]
            },
            {
                "id": 5,
                "name": "pricerange",
                "type": "Range",
                "status": 1,
                "sort_filter": 1,
                "value": [
                    {
                        "min": 0,
                        "max": 1000
                    }
                ]
            }
        ]
    }
}
```
