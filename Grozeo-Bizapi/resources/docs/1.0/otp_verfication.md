# OTP Verification

---

OTP Verification

### Details

| Method | Uri             | Authorization |
| :----- | :-------------- | :------------ |
| POST   | `signup/verify` | NO            |

### Request

```json
{
    "mobile": "77xxxxxx",
    "otp": "8006"
}
```

### Response 1

```json
{
    "status": "ok",
    "data": {
        "is_verified": true,
        "is_registered": false,
        "user": {}
    }
}
```

### Response 2

```json
{
    "status": "ok",
    "data": {
        "is_verified": true,
        "is_registered": true,
        "user": {
            "cust_id": 60,
            "cust_branch_id": 1,
            "cust_customer_id": 1054,
            "cust_mobile": "7736767406",
            "cust_email": "harish.a@velosit.in",
            "password": null,
            "cust_customer_name": "harish",
            "cust_ref_code": "A14RF00031",
            "cust_prom_reward_point": 0,
            "cust_status": "registered",
            "cust_avatar": null,
            "api_token": null,
            "delivery_info": {},
            "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOlwvXC9teS1waGFybWFjeS1hcGkudGVzdFwvYXBpXC9zaWdudXBcL3ZlcmlmeSIsImlhdCI6MTU3ODk4NDgwMywiZXhwIjoxNTc4OTg4NDAzLCJuYmYiOjE1Nzg5ODQ4MDMsImp0aSI6InlqcW5KeW9WUXN0MlEyWm8iLCJzdWIiOjYwLCJwcnYiOiI4YjQyMmU2ZjY1NzkzMmI4YWViY2IxYmYxZTM1NmRkNzZhMzY1YmYyIn0.7ZNNiU89DPWUdHMSvRXijJ1K0HApDfEgtcOaZHrJhbk"
        }
    }
}
```
