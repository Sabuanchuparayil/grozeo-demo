# Cart Details

---
Cart Details

### Details

| Method | Uri   | Authorization |
| : |   :-   |  :  |
| POST | `/cart/checkprocessed` | Yes |



### Note
selection is key only used in order_method is 1<br>

```json
selection:[
    1 is  all_available_product_quality
    2 is not_available_product_quality_in_48_hours
    3 is all_product_in_48_hours

]
```


### Request
```json
{
"branch_id":10,
"order_method":1,
"selection":[1,2]
}
```



### Response

```json
{
    "status": "ok",
    "data": [
        {
            "item_count": 1,
            "pricedetails": [
                {
                    "label": "Basket Value",
                    "value": "₹ 99.5",
                    "color_code": "#858383",
                    "is_bold": false,
                    "is_italics": false,
                    "order": 1
                },
                {
                    "label": "Amount Before Tax",
                    "value": "₹ 86.57",
                    "color_code": "#858383",
                    "is_bold": false,
                    "is_italics": false,
                    "order": 2
                },
                {
                    "label": "Gst",
                    "value": "₹ 11.94",
                    "color_code": "#858383",
                    "is_bold": false,
                    "is_italics": false,
                    "order": 3
                },
                {
                    "label": "KFC",
                    "value": "₹ 1",
                    "color_code": "#858383",
                    "is_bold": false,
                    "is_italics": false,
                    "order": 4
                },
                {
                    "label": "Delivery charge",
                    "value": "₹ 40",
                    "color_code": "#858383",
                    "is_bold": false,
                    "is_italics": false,
                    "order": 5
                },
                {
                    "label": "Courier charge",
                    "value": "₹ 0",
                    "color_code": "#858383",
                    "is_bold": false,
                    "is_italics": false,
                    "order": 6
                },
                {
                    "label": "Discount",
                    "value": "₹ 0",
                    "color_code": "#858383",
                    "is_bold": false,
                    "is_italics": false,
                    "order": 7
                },
                {
                    "label": "Round Off",
                    "value": "₹ 0.5",
                    "color_code": "#858383",
                    "is_bold": false,
                    "is_italics": false,
                    "order": 8
                },
                {
                    "label": "Total",
                    "value": "₹ 140",
                    "color_code": "#000000",
                    "is_bold": true,
                    "is_italics": false,
                    "order": 9
                }
            ],
            "nearest_retailer": 46
        }
    ]
}
```
