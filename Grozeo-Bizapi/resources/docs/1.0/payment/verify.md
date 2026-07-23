# Verify instamojo

---
Instamojo

### Details

| Method | Uri   | Authorization |
| : |   :-   |  :  |
| POST | `/pharmacy/instamojo/verify` | Yes |

### Request
```json
{
	"payment_request_id":"c24a67f0153848429ed3f1299de88412"
}

```

### Responses

```json
{
    "status": "ok",
    "data": {
        "id": "c24a67f0153848429ed3f1299de88412",
        "phone": "+918129160154",
        "email": "harish.a@velosit.in",
        "buyer_name": "harish",
        "amount": "70.00",
        "purpose": "BRM",
        "expires_at": "2020-05-05T10:49:04Z",
        "status": "Completed",
        "send_sms": false,
        "send_email": false,
        "sms_status": null,
        "email_status": null,
        "shorturl": null,
        "longurl": "https://test.instamojo.com/@praseed/c24a67f0153848429ed3f1299de88412",
        "redirect_url": "http://localhost/my-pharmacy-api/public/payment/result/redirect",
        "webhook": null,
        "payments": [
            {
                "payment_id": "MOJO0505F05N57121888",
                "status": "Credit",
                "currency": "INR",
                "amount": "70.00",
                "buyer_name": "harish",
                "buyer_phone": "+918129160154",
                "buyer_email": "harish.a@velosit.in",
                "shipping_address": null,
                "shipping_city": null,
                "shipping_state": null,
                "shipping_zip": null,
                "shipping_country": null,
                "quantity": 1,
                "unit_price": "70.00",
                "fees": "1.33",
                "variants": [],
                "custom_fields": [],
                "affiliate_commission": "0",
                "payment_request": "https://test.instamojo.com/api/1.1/payment-requests/c24a67f0153848429ed3f1299de88412/",
                "instrument_type": "NETBANKING",
                "billing_instrument": "Domestic Netbanking Regular",
                "tax_invoice_id": "",
                "failure": null,
                "payout": null,
                "created_at": "2020-05-05T10:41:39.878223Z"
            }
        ],
        "allow_repeated_payments": false,
        "customer_id": null,
        "created_at": "2020-05-05T10:39:10.691293Z",
        "modified_at": "2020-05-05T10:41:44.408448Z"
    }
}
```
