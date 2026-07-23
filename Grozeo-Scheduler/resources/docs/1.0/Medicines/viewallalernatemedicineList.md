# View All Alternate Medicine

---

### Details

| Method | Uri               | Authorization |
| :----- | :---------------- | :------------ |
| POST   | `medicine/viewall` | NO            |

### Request


```json
{
 "stit_ID":"23",
 "isMedicine" :"1",
 "branch_id":1
}
```

### Response 

```json
{
    "status": "ok",
    "data": {
        "currentpage": 1,
        "MedicineList": [
            {
                "stit_ID": 23,
                "fsi_uid": 22,
                "item_group_id": 22,
                "item_name": "OTHER ANTIASTHMATIC & COPD PREPARATIONS LEVOLIN INHALER",
                "brand_name": "OTHER ANTIASTHMATIC & COPD PREPARATIONS",
                "stock_available": 0,
                "main_image": [],
                "mrp": 0,
                "isMedicine": "1"
            },
            {
                "stit_ID": 27,
                "fsi_uid": 26,
                "item_group_id": 26,
                "item_name": "OMEPRAZOLE + DOMPERIDONE ACILOC-RD",
                "brand_name": "OMEPRAZOLE + DOMPERIDONE",
                "stock_available": 0,
                "main_image": [],
                "mrp": 0,
                "isMedicine": "1"
            },
            {
                "stit_ID": 29,
                "fsi_uid": 29,
                "item_group_id": 29,
                "item_name": "PANTOPRAZOLE Bronchoherb Cough Syrup",
                "brand_name": "PANTOPRAZOLE",
                "stock_available": 0,
                "main_image": [],
                "mrp": 0,
                "isMedicine": "1"
            },
            {
                "stit_ID": 37,
                "fsi_uid": 36,
                "item_group_id": 36,
                "item_name": "FAMOTIDINE Wheat Grass Amla Juice",
                "brand_name": "FAMOTIDINE",
                "stock_available": 0,
                "main_image": [],
                "mrp": 0,
                "isMedicine": "1"
            },
            {
                "stit_ID": 41,
                "fsi_uid": 40,
                "item_group_id": 40,
                "item_name": "PANTOPRAZOLE + DOMPERIDONE PENTALINK-D",
                "brand_name": "PANTOPRAZOLE + DOMPERIDONE",
                "stock_available": 0,
                "main_image": [],
                "mrp": 0,
                "isMedicine": "1"
            },
            {
                "stit_ID": 44,
                "fsi_uid": 44,
                "item_group_id": 44,
                "item_name": "OMEPRAZOLE DIOCID",
                "brand_name": "OMEPRAZOLE",
                "stock_available": 0,
                "main_image": [],
                "mrp": 0,
                "isMedicine": "1"
            },
            {
                "stit_ID": 46,
                "fsi_uid": 46,
                "item_group_id": 46,
                "item_name": "OMEPRAZOLE + DOMPERIDONE DIOCID-DSR",
                "brand_name": "OMEPRAZOLE + DOMPERIDONE",
                "stock_available": 0,
                "main_image": [],
                "mrp": 0,
                "isMedicine": "1"
            },
            {
                "stit_ID": 4,
                "fsi_uid": 48,
                "item_group_id": 48,
                "item_name": "PARACETAMOL CROCIN",
                "brand_name": "PARACETAMOL",
                "stock_available": 0,
                "main_image": [],
                "mrp": 0,
                "isMedicine": "1"
            },
            {
                "stit_ID": 48,
                "fsi_uid": 49,
                "item_group_id": 49,
                "item_name": "PANTOPRAZOLE + DOMPERIDONE PROLEX-DSR",
                "brand_name": "PANTOPRAZOLE + DOMPERIDONE",
                "stock_available": 0,
                "main_image": [],
                "mrp": 0,
                "isMedicine": "1"
            },
            {
                "stit_ID": 53,
                "fsi_uid": 54,
                "item_group_id": 54,
                "item_name": "LANSOPRAZOLE B-LANSO",
                "brand_name": "LANSOPRAZOLE",
                "stock_available": 0,
                "main_image": [],
                "mrp": 0,
                "isMedicine": "1"
            }
        ],
        "first_page_url": "http://my-pharmacy-api.test/api/medicine/viewall?page=1",
        "from": 1,
        "last_page": 3,
        "last_page_url": "http://my-pharmacy-api.test/api/medicine/viewall?page=3",
        "next_page_url": "http://my-pharmacy-api.test/api/medicine/viewall?page=2",
        "path": "http://my-pharmacy-api.test/api/medicine/viewall",
        "per_page": 10,
        "prev_page_url": null,
        "to": 10,
        "total": 25
    }
}
```
