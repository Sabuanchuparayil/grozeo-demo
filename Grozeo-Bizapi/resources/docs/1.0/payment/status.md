#  instamojo status

---
Instamojo

### Details

| Method | Uri   | Authorization |
| : |   :-   |  :  |
| POST | `/pharmacy/instamojo/status` | Yes |

### Request
```json
{
	"payment_request_id":"242f554059e4420290f0570cacc1a70f"
}

```

### Response - 1

```json
{
    "status": "ok",
    "data": {
        "payment_status": 0, // 0 - pending, 1 - Success , 2 - Failed
        "msg": "Pending"
    }
}
```
### Response - 2
```json
{
    "status": "ok",
    "data": {
        "payment_status": 1,
        "msg": "Credit"
    }
}
```
