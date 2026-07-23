# Home Browse By Category List

---
Brand Screen

### Details

| Method | Uri   | Authorization |
| : |   :-   |  :  |
| POST | `product/browsebycategory` | NO |

### Request

```json
{
"id" :"12",
"category_id" :""
}
```

### Response

```json
{
    "status": "ok",
    "data": [
        {
            "sub_category_id": 32,
            "sub_category": "Adult Diapers",
            "sub_category_image": "",
            "status": "1",
            "isMedicine": 0,
            "main_category": 12,
            "subcat_bmd_id": null,
            "subcat_bmd_name": null
        },
        {
            "sub_category_id": 33,
            "sub_category": "Bone & Joint Health",
            "sub_category_image": "",
            "status": "1",
            "isMedicine": 0,
            "main_category": 12,
            "subcat_bmd_id": null,
            "subcat_bmd_name": null
        },
        {
            "sub_category_id": 34,
            "sub_category": "Living & Safety Aids",
            "sub_category_image": "",
            "status": "1",
            "isMedicine": 0,
            "main_category": 12,
            "subcat_bmd_id": null,
            "subcat_bmd_name": null
        },
        {
            "sub_category_id": 36,
            "sub_category": "Orthopaedic Supports",
            "sub_category_image": "",
            "status": "1",
            "isMedicine": 0,
            "main_category": 12,
            "subcat_bmd_id": null,
            "subcat_bmd_name": null
        }
    ]
}

```
