# Medicine Details

---

### Details

| Method | Uri               | Authorization |
| :----- | :---------------- | :------------ |
| POST   | `medicine/details` | NO            |

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
        "fsi_uid": 22,
        "item_group_id": 22,
        "item_name": "OTHER ANTIASTHMATIC & COPD PREPARATIONS LEVOLIN INHALER",
        "brand_name": "OTHER ANTIASTHMATIC & COPD PREPARATIONS",
        "category_id": 5,
        "category_name": "Antiasthmatic & COPD Preparations",
        "variant": "50mcg",
        "isMedicine": 1,
        "fsi_displaylabel": "",
        "fsi_def_itemmaster_id": 23,
        "item_master": [
            {
                "stit_ID": 23,
                "stit_fsiuid": 22,
                "quantity": "",
                "itemId": 23,
                "short_description": "Levolin 50mcg Inhaler belongs to a group of medicines called fast-acting bronchodilators or â€œrelieversâ€\u009d. It is used to treat the symptoms of asthma and chronic obstructive pulmonary disease (COPD) such as coughing, wheezing and feeling short of breath.\n\n ",
                "long_description": null,
                "stock_available": 0,
                "selling_prize": 12,
                "mrp": 0,
                "main_image": [],
                "additional_image": [],
                "selling_price": 0,
                "godown_itemId": 242,
                "default_value": 0
            }
        ],
        "composition_name": "PANTOPRAZOLE",
        "isPrescription": 0,
        "manufacture": "Soigner Pharma ",
        "tabmenu": {
            "depthinfo": {
                "about": "test",
                "use": "use",
                "sideEffect": "sideeffect",
                "medicine work": "work",
                "MoreInfo": "info",
                "medicineDiseases": ""
            },
            "generalInfo": {
                "overview": "Levolin 50mcg Inhaler belongs to a group of medicines called fast-acting bronchodilators or â€œrelieversâ€\u009d. It is used to treat the symptoms of asthma and chronic obstructive pulmonary disease (COPD) such as coughing, wheezing and feeling short of breath.\n\n ",
                "medicine content": ""
            },
            "patientConcers": {
                "medadv_id": 1,
                "medicineMaster_id": 23,
                "advice_id": 1,
                "precaution_id": 1,
                "medadv_content": "test1",
                "safety_advice": {
                    "advice_id": 1,
                    "advice_name": "Alchohol",
                    "advice_status": 1
                },
                "safety_precaution": {
                    "precaution_id": 1,
                    "precaution_name": "Safe",
                    "precaution_status": 1
                }
            }
        },
        "alternateMedicine": {
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
                }
            ],
            "viewDetails": {
                "total_data": 25,
                "limit_data": 5
            }
        },
        "approvel": 1
    }
}
```
