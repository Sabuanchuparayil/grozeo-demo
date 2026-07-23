# Create instamojo

---
Instamojo

### Details

| Method | Uri   | Authorization |
| : |   :-   |  :  |
| POST | `/pharmacy/checkout` | Yes |

### Request

```json
{
	"order_method":2,   // integer 1 - I can delivery,  2 - I can collect
	"selection":1,  // nullable integer,  integer value - 1,2,3,4
	"branch_id":1  // integer branch id
    "nearest_retailer_branch" : 12  // nullable Nearest retailer branch id 

     //Selection val when order_method = 1
    //1 - All available, 
    //2 - Not Available in 48 hrs, 
    //3- All available in 48 hrs,
    //4 - Both 1 and 2 (1 - All available and 2 - Not Available in 48 hrs)
}
```

### Response - 1

```json
{
    "status": "ok",   // status = 200
    "data": {
        "payment_gateway": "instamojo",
        "order_id": 34,
        "order_order_id": "2005150003",
        "details": {
            "id": "b7c6cb0252954cde9d7966ad30aae6ce",
            "phone": "+918129160154",
            "email": "harish.a@velosit.in",
            "buyer_name": "harish",
            "amount": "199.00",
            "purpose": "BRM",
            "expires_at": "2020-05-05T09:59:41Z",
            "status": "Pending",
            "send_sms": false,
            "send_email": false,
            "sms_status": null,
            "email_status": null,
            "shorturl": null,
            "longurl": "https://test.instamojo.com/@praseed/b7c6cb0252954cde9d7966ad30aae6ce",
            "redirect_url": "http://dev.api.mypharmacy.velosit.in/payment/result/redirect",
            "webhook": "http://dev.api.mypharmacy.velosit.in/payment/result/webhook",
            "allow_repeated_payments": false,
            "customer_id": null,
            "created_at": "2020-05-05T09:49:47.120591Z",
            "modified_at": "2020-05-05T09:49:47.120600Z"
        }
    }
}
```

### Response - 2

```json
{
    "status": "error",   //status = 406
    "error": {
        "msg": "Empty Stock",
        "data": {
            "type": 1,  //Stock = 0 -case
            "items": [          //product details
                {
                    "item_id": 7,
                    "name": "Cough Drops",
                    "quantity": "190 lozenges",
                    "stock_available": 0
                }
            ]
        }
    }
}
```

### Response - 3

```json
{
    "status": "error",  ////status = 406
    "error": {
        "msg": "Insufficient stock based on your Order",
        "data": {
            "type": 2,  //Stock not equal to 0 -case, order product count  more than available
            "items": [  //product details
                {
                    "item_id": 7,
                    "name": "Cough Drops",
                    "quantity": "190 lozenges",
                    "stock_available": 1
                }
            ]
        }
    }
}
```
### Response - 4

```json
//order_method = 1 , selection = 4
// Atleast one product have stock, that should be consider as Parent order
//without parent order we cant create child order. !
{
    "status": "error",
    "error": {
        "msg": "Stocks are empty for All products.",
        "data": {
            "type": 3,
            "items": []
        }
    }
}

```