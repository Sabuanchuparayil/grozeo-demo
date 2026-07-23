# Pincode Details Fetch

---
Pincode Details Fetch

### Details

| Method | Uri   | Authorization |
| : |   :-   |  :  |
| POST | `/signup/pincode` | NO |



### Request

```json
{

  "pincode":"605023"

}
```

### Resonponse

```json
{
    "status": "ok",
    "data": {
        "psof_id": 1,
        "postoffice": "Connaught Place",
        "pincode": 605023,
        "dst_id": 3,
        "psof_lati": null,
        "psof_long": null,
        "google_formatted_address": null,
        "psof_isPostOffice": null,
        "status": null,
        "created_by": 0,
        "created_on": "2018-10-23",
        "updated_by": 0,
        "updated_on": "2018-10-23",
        "district_and_state": {
            "dst_Id": 3,
            "dst_Name": "Kollam",
            "st_name": "Kerala"
        }
    }
}
```

### Error Response

```json
{
    "status": "error",
    "error": {
        "msg": "Pincode is not Available."
    }
}
```

```json
{
    "status": "error",
    "error": {
        "msg": "Invalid Pincode"
    }
}
```
