# Upload prescription 


### Details

| Method | Uri   | Authorization |
| : |   :-   |  :  |
| POST | `/document/uploadprescription` | YES |



In menu option the priority is set to 0 and cart page to upload the image at that time,priority should be 1
### Request 

```json
{
"priority":1,
"file":[
    {
    "name":"45.jpg",
    "description":"test"
    },
    {
    "name":"277.jpg",
    "description":"disc"
    }

    ]

}

```
### Request

```json
{
    "status": "ok",
    "msg": "Successfully upload"
}
```
