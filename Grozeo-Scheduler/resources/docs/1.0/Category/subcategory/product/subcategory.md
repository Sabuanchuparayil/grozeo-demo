# Subcategory  

---
Subcategory

### Details

| Method | Uri   | Authorization |
| : |   :-   |  :  |
| POST | `/category/{id}/subcategory` | NO |



### Response

```json
{
    "status": "ok",
    "data": [
        {
            "category_id": 1,
            "category_name": "Cold and Cough",
            "image_url": null,
            "banner_image_url": null,
            "parent_category": 1,
            "status": "1",
            "pdt_count": 0,
            "level": null,
            "categories": [
                {
                    "sub_category_id": 1,
                    "sub_category": "Nasal Congestion",
                    "sub_category_image": "",
                    "status": "1",
                    "main_category": 1,
                    "subcat_bmd_id": null,
                    "subcat_bmd_name": null
                },
                {
                    "sub_category_id": 2,
                    "sub_category": "Cough",
                    "sub_category_image": "",
                    "status": "1",
                    "main_category": 1,
                    "subcat_bmd_id": null,
                    "subcat_bmd_name": null
                },
                {
                    "sub_category_id": 4,
                    "sub_category": "nas",
                    "sub_category_image": "",
                    "status": "0",
                    "main_category": 1,
                    "subcat_bmd_id": null,
                    "subcat_bmd_name": null
                }
            ]
        },
        {
            "category_id": 2,
            "category_name": "Sub Category ",
            "image_url": null,
            "banner_image_url": null,
            "parent_category": 1,
            "status": "0",
            "pdt_count": 0,
            "level": null,
            "categories": [
                {
                    "sub_category_id": 3,
                    "sub_category": "Third level Category",
                    "sub_category_image": "",
                    "status": "1",
                    "main_category": 2,
                    "subcat_bmd_id": null,
                    "subcat_bmd_name": null
                }
            ]
        },
        {
            "category_id": 5,
            "category_name": "Cold ",
            "image_url": null,
            "banner_image_url": null,
            "parent_category": 1,
            "status": "0",
            "pdt_count": 0,
            "level": null,
            "categories": []
        }
    ]
}
```
