# Edit Customer Details

---

 Edit Customer Details

### Details

| Method | Uri               | Authorization |
| :----- | :---------------- | :------------ |
| PUT   | `api/customer/` | YES            |

### Request
```json
{
    "city": "TVm",
    "email": "harish.a@velosit.in",
    "name": "Mahesh",
    "house_name": "Velosit",
    "house_no": "H10",
    "land_mark": "West Of Kerala Spinners",
    "mobile": "7736767406",
    "pincode": "695023",
    "post": "Avalookunnu",
    "state": "Kerala",
    "latitude":"1.2",
    "longitude":"2.8"
}


```


### Response 

```json
{
    "status": "ok",
    "data": {
        "cust_email": "harish.a@velosit.in",
        "cust_customer_name": "Mahesh",
        "deliverInfo": {
            "deli_delivery_pin": "695023",
            "deli_post": "Avalookunnu",
            "deli_state": "Kerala",
            "deli_latitude": "1.2",
            "deli_longitude": "2.8",
            "deli_contact_no": "7736767406",
            "deli_house_name": "Velosit",
            "deli_house_no": "H10",
            "deli_land_mark": "West Of Kerala Spinners",
            "deli_is_primary": 1
        }
    }
}
```


