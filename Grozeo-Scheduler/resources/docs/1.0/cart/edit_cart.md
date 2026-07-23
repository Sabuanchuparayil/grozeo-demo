# Edit Cart 

---
Edit Cart

### Details

| Method | Uri   | Authorization |
| : |   :-   |  :  |
| PUT | `/cart` | Yes |

### Request

```json
{
	"cart_product_id":22,
	"cart_order_qty":10,
    "order_method":1,
}

```

### Response

```json
{
   "status": "ok",
   "msg": "cart updated successfully"
}

```
