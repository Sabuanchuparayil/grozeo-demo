# Order List


### Details

| Method | Uri   | Authorization |
| : |   :-   |  :  |
| GET | `api/order/list/{order_method}` | YES |

<br>
<b>Note:</b><br>
order_method -1 :i need a delivery<br>
order_method -2 :i can collect<br>



### Response

```json
{
    "status": "ok",
    "data": [
        {
            "order_id": 2,
            "order_order_id": "2005060002",
            "status_id": 7,
            "created_at": "06-05-2020",
            "order_status_addinfo": "",
            "order_trackURL": "",
            "order_history": [
                {
                    "order_id": 2,
                    "order_status": 0,
                    "created_at": "2020-05-06 05:52:21",
                    "get_order_status": {
                        "status_id": 0,
                        "status": "Checkout"
                    },
                    "status": "Checkout"
                },
                {
                    "order_id": 2,
                    "order_status": 1,
                    "created_at": "2020-05-06 05:52:21",
                    "get_order_status": {
                        "status_id": 1,
                        "status": "Payment Initiated"
                    },
                    "status": "Payment Initiated"
                },
                {
                    "order_id": 2,
                    "order_status": 4,
                    "created_at": "2020-05-06 05:56:35",
                    "get_order_status": {
                        "status_id": 4,
                        "status": "Order Placed"
                    },
                    "status": "Order Placed"
                }
            ]
        },
        {
            "order_id": 1,
            "order_order_id": "2005060001",
            "status_id": 7,
            "created_at": "06-05-2020",
            "order_status_addinfo": "",
            "order_trackURL": "",
            "order_history": [
                {
                    "order_id": 1,
                    "order_status": 0,
                    "created_at": "2020-05-06 05:37:36",
                    "get_order_status": {
                        "status_id": 0,
                        "status": "Checkout"
                    },
                    "status": "Checkout"
                },
                {
                    "order_id": 1,
                    "order_status": 1,
                    "created_at": "2020-05-06 05:37:36",
                    "get_order_status": {
                        "status_id": 1,
                        "status": "Payment Initiated"
                    },
                    "status": "Payment Initiated"
                },
                {
                    "order_id": 1,
                    "order_status": 4,
                    "created_at": "2020-05-06 05:40:41",
                    "get_order_status": {
                        "status_id": 4,
                        "status": "Order Placed"
                    },
                    "status": "Order Placed"
                }
            ]
        }
    ]
}
````

