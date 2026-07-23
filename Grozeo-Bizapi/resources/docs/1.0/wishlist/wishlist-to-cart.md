# Wishlist to Cart

---

Wishlist to Cart

### Details

| Method | Uri               | Authorization |
| :----- | :---------------- | :------------ |
| POST   | `api/wishlist-to-cart/{group_id}/{product_id}/{order_method}` | YES            |

Note:
order_method 
I need Delivery -1
I can Collect -2

### Response 

```json
{
    "status": "ok",
    "data": {
        "product_id": "1"
    }
}
```


