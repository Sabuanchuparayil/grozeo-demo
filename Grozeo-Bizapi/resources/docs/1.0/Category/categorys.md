# Category 

---
Category

### Details

| Method | Uri   | Authorization |
| : |   :-   |  :  |
| GET | `/home/category` | NO |



### Response

```json
{
    "status": "ok",
    "data": [
        {
            "parent_category_id": 1,
            "parent_category": "Winter Cares",
            "image_url": "https://images-na.ssl-images-amazon.com/images/I/415QOf5j3tL._SX425_.jpg",
            "thumb_url": "https://drive.google.com/open?id=1mpknUUVEhA7S7r6nrM2WL-kT75c-ZF4Y",
            "status": "1",
            "subcategories": [
                {
                    "category_id": 1,
                    "category_name": "Cold and Cough",
                    "image_url": null,
                    "thumb_url": "https://drive.google.com/open?id=1TsECzdWLCLcU6TSuX-F4McPYPEpH601l",
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
                    "image_url": "",
                    "thumb_url": "https://drive.google.com/open?id=1TsECzdWLCLcU6TSuX-F4McPYPEpH601l",
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
                    "thumb_url": "https://drive.google.com/open?id=1TsECzdWLCLcU6TSuX-F4McPYPEpH601l",
                    "banner_image_url": null,
                    "parent_category": 1,
                    "status": "0",
                    "pdt_count": 0,
                    "level": null,
                    "categories": []
                }
            ]
        },
        {
            "parent_category_id": 2,
            "parent_category": "Parent Category ",
            "image_url": "https://images-na.ssl-images-amazon.com/images/I/415QOf5j3tL._SX425_.jpg",
            "thumb_url": "https://drive.google.com/open?id=1mpknUUVEhA7S7r6nrM2WL-kT75c-ZF4Y",
            "status": "1",
            "subcategories": [
                {
                    "category_id": 3,
                    "category_name": "read",
                    "image_url": null,
                    "thumb_url": "https://drive.google.com/open?id=1TsECzdWLCLcU6TSuX-F4McPYPEpH601l",
                    "banner_image_url": null,
                    "parent_category": 2,
                    "status": "0",
                    "pdt_count": 0,
                    "level": null,
                    "categories": []
                },
                {
                    "category_id": 4,
                    "category_name": "Second Level sub category - In active",
                    "image_url": null,
                    "thumb_url": "https://drive.google.com/open?id=1TsECzdWLCLcU6TSuX-F4McPYPEpH601l",
                    "banner_image_url": null,
                    "parent_category": 2,
                    "status": "0",
                    "pdt_count": 0,
                    "level": null,
                    "categories": []
                }
            ]
        },
        {
            "parent_category_id": 3,
            "parent_category": "In Active - top level",
            "image_url": "https://images-na.ssl-images-amazon.com/images/I/415QOf5j3tL._SX425_.jpg",
            "thumb_url": "https://drive.google.com/open?id=1mpknUUVEhA7S7r6nrM2WL-kT75c-ZF4Y",
            "status": "0",
            "subcategories": []
        },
        {
            "parent_category_id": 4,
            "parent_category": "Parentt Category",
            "image_url": "https://images-na.ssl-images-amazon.com/images/I/415QOf5j3tL._SX425_.jpg",
            "thumb_url": "https://drive.google.com/open?id=1mpknUUVEhA7S7r6nrM2WL-kT75c-ZF4Y",
            "status": "0",
            "subcategories": []
        },
        {
            "parent_category_id": 5,
            "parent_category": "Parent C",
            "image_url": "https://images-na.ssl-images-amazon.com/images/I/415QOf5j3tL._SX425_.jpg",
            "thumb_url": "https://drive.google.com/open?id=1mpknUUVEhA7S7r6nrM2WL-kT75c-ZF4Y",
            "status": "1",
            "subcategories": [
                {
                    "category_id": 6,
                    "category_name": "Category D",
                    "image_url": null,
                    "thumb_url": "https://drive.google.com/open?id=1TsECzdWLCLcU6TSuX-F4McPYPEpH601l",
                    "banner_image_url": null,
                    "parent_category": 5,
                    "status": "1",
                    "pdt_count": 0,
                    "level": null,
                    "categories": [
                        {
                            "sub_category_id": 5,
                            "sub_category": "Sub Category C",
                            "sub_category_image": "",
                            "status": "1",
                            "main_category": 6,
                            "subcat_bmd_id": null,
                            "subcat_bmd_name": null
                        }
                    ]
                },
                {
                    "category_id": 7,
                    "category_name": "test1",
                    "image_url": null,
                    "thumb_url": "https://drive.google.com/open?id=1TsECzdWLCLcU6TSuX-F4McPYPEpH601l",
                    "banner_image_url": null,
                    "parent_category": 5,
                    "status": "1",
                    "pdt_count": 0,
                    "level": null,
                    "categories": [
                        {
                            "sub_category_id": 6,
                            "sub_category": "test sub",
                            "sub_category_image": "",
                            "status": "1",
                            "main_category": 7,
                            "subcat_bmd_id": null,
                            "subcat_bmd_name": null
                        }
                    ]
                },
                {
                    "category_id": 8,
                    "category_name": "test2",
                    "image_url": null,
                    "thumb_url": "https://drive.google.com/open?id=1TsECzdWLCLcU6TSuX-F4McPYPEpH601l",
                    "banner_image_url": null,
                    "parent_category": 5,
                    "status": "1",
                    "pdt_count": 0,
                    "level": null,
                    "categories": []
                }
            ]
        },
        {
            "parent_category_id": 6,
            "parent_category": "Parent category example",
            "image_url": "https://images-na.ssl-images-amazon.com/images/I/415QOf5j3tL._SX425_.jpg",
            "thumb_url": "https://drive.google.com/open?id=1mpknUUVEhA7S7r6nrM2WL-kT75c-ZF4Y",
            "status": "1",
            "subcategories": []
        },
        {
            "parent_category_id": 7,
            "parent_category": "Category example",
            "image_url": "https://images-na.ssl-images-amazon.com/images/I/415QOf5j3tL._SX425_.jpg",
            "thumb_url": "https://drive.google.com/open?id=1mpknUUVEhA7S7r6nrM2WL-kT75c-ZF4Y",
            "status": "1",
            "subcategories": []
        },
        {
            "parent_category_id": 8,
            "parent_category": "Example_Parent_category",
            "image_url": "https://images-na.ssl-images-amazon.com/images/I/415QOf5j3tL._SX425_.jpg",
            "thumb_url": "https://drive.google.com/open?id=1mpknUUVEhA7S7r6nrM2WL-kT75c-ZF4Y",
            "status": "1",
            "subcategories": [
                {
                    "category_id": 9,
                    "category_name": "Example_category",
                    "image_url": null,
                    "thumb_url": "https://drive.google.com/open?id=1TsECzdWLCLcU6TSuX-F4McPYPEpH601l",
                    "banner_image_url": null,
                    "parent_category": 8,
                    "status": "1",
                    "pdt_count": 0,
                    "level": null,
                    "categories": [
                        {
                            "sub_category_id": 7,
                            "sub_category": "Example_Sub_categoy",
                            "sub_category_image": "",
                            "status": "1",
                            "main_category": 9,
                            "subcat_bmd_id": null,
                            "subcat_bmd_name": null
                        }
                    ]
                }
            ]
        }
    ]
}
```
