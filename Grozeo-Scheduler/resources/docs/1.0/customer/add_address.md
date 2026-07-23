# Get Customer Address Details

---

 Get Customer Address Details

### Details

| Method | Uri               | Authorization |
| :----- | :---------------- | :------------ |
| POST   | `api/customer/address` | YES            |

### Request

```json
{
	
	"deli_delivery_pin": 695023,
	"deli_house_no": "H1001",
	"deli_house_name": "test",
	"deli_land_mark": "West Of Kerala Spinners",
	"deli_post": "Avalookunnu",
	"deli_city": "Alappuzha",
    "deli_state": "Kerala",
    "deli_name": "harish",
    "deli_contact_no": "7736767406",
    "deli_latitude": "1.1",
    "deli_longitude":"1.2",
    "deli_type": "Home"
	
	
        }

```


### Response 

```json
{
    "status": "ok",
    "data": {
        "deli_id": 113,
        "deli_customer_id": 60,
        "deli_delivery_pin": 695023,
        "deli_branch_id": 0,
        "deli_house_no": "H1001",
        "deli_house_name": "test",
        "deli_land_mark": "West Of Kerala Spinners",
        "deli_post": "Avalookunnu",
        "deli_city": "Alappuzha",
        "deli_state": "Kerala",
        "deli_status": "active",
        "deli_name": "harish",
        "deli_contact_no": "77xxxxxx",
        "deli_is_primary": 0,
        "deli_latitude": 1.1,
        "deli_longitude": 1.2,
        "deli_type": "Home"
    }
}
```


