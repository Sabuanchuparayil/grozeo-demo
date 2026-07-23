# Save Cart

---

Save Cart

### Details

| Method | Uri     | Authorization |
| :----- | :------ | :------------ |
| POST   | `/cart` | Yes           |




### Request

```json
{
    "cart_group_id": 3,
    "cart_product_id": 2,
    "cart_order_qty": 2,
    "cart_branch_id":1,
    "type":1,
    "order_method":2,
    
}
```
type-1 wishlist data added to cart.
type-2 Directly added to cart

order_method 
I need Delivery -1
I can Collect -2

### Response

```json
{
    "status": "ok",
    "data": {
        "cart_product_ids": 
        [
            { 
                "cart_product_id": 2, 
                "cart_order_qty": 2 
                }
        ]
    }
}
```
