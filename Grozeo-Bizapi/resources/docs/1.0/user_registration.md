# OTP Verification

---

OTP Verification

### Details

| Method | Uri               | Authorization |
| :----- | :---------------- | :------------ |
| POST   | `signup/customer` | NO            |

### Request

latitude and longitude  is not mandatory
```json
{
    "city": "Alappuzha",
    "email": "harish.a@velosit.in",
    "name": "harish",
    "house_name": "test",
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

### Response 1

```json
{
    "status": "ok",
    "data": {
        "cust_branch_id": 1,
        "cust_customer_id": 1054,
        "cust_mobile": "7736767406",
        "cust_email": "harish.a@velosit.in",
        "cust_customer_name": "harish",
        "cust_ref_code": "A28RF00038",
        "cust_status": "registered",
        "cust_id": 75,
        "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOlwvXC9teS1waGFybWFjeS1hcGkudGVzdFwvYXBpXC9zaWdudXBcL2N1c3RvbWVyIiwiaWF0IjoxNTgwMTkwMTA1LCJleHAiOjE1ODAxOTM3MDUsIm5iZiI6MTU4MDE5MDEwNSwianRpIjoiY2M2bHc2WlpxZ2o4dWhJZiIsInN1YiI6NzUsInBydiI6IjhiNDIyZTZmNjU3OTMyYjhhZWJjYjFiZjFlMzU2ZGQ3NmEzNjViZjIifQ.5HtDElQdIbXQkPeKEgKB-dgA7iGHbui8JOb6AROFjH0",
        "deliverInfo": [
            {
                "deli_id": 125,
                "deli_customer_id": 75,
                "deli_delivery_pin": 695023,
                "deli_branch_id": 1,
                "deli_house_no": "H10",
                "deli_house_name": "test",
                "deli_land_mark": "West Of Kerala Spinners",
                "deli_post": "Avalookunnu",
                "deli_city": "Alappuzha",
                "deli_state": "Kerala",
                "deli_status": "active",
                "deli_name": "harish",
                "deli_contact_no": "7736767406",
                "deli_is_primary": 1,
                "deli_latitude": null,
                "deli_longitude": null,
                "deli_type": "home"
            }
        ]
    }
}
```

### Response 2

```json
{
    "status": "error",
    "error": {
        "msg": "Mobile number not verified"
    }
}
```
