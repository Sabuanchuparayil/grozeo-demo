# Create Wishlist

---

Create Wishlist

### Details

| Method | Uri               | Authorization |
| :----- | :---------------- | :------------ |
| POST   | `api/wishlist` | YES            |

### Request

```json
{
	
    "product_id":13,
    "group_id":13,
    "branch_id":1,
    "order_method":2

}
```
Note:
order_method 
I need Delivery -1
I can Collect -2

### Response 1

```json
{
    "status": "ok",
    "msg": "Item successfully saved"
}
```


