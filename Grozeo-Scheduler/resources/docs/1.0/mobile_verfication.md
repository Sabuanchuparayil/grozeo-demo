# Mobile Verification

---
Mobile Verification

### Details

| Method | Uri   | Authorization |
| : |   :-   |  :  |
| POST | `/signup/mobile` | NO |


### Request

```json

{
    "mobile":"77xxxxxx"
}
```


### Response 1

```json
{
    "status": "ok",
    "msg": "otp sent successfully"
}


```

### Response 2

```json
{
    "status": "ok",
    "msg": "otp has been sent already"
}
```
