# Login  

---
Login 

### Details

| Method | Uri   | Authorization |
| : |   :-   |  :  |
| POST | `/login` | NO |

### Request

```json
{
	"cust_email":"vishnu3@gmail.com",
    "password":1234
}

```

### Response

```json
{
    "status": "ok",
    "data": {
        "access_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOlwvXC9teS1waGFybWFjeS1hcGkudGVzdFwvYXBpXC9sb2dpbiIsImlhdCI6MTU3ODkxNjg4MSwiZXhwIjoxNTc4OTIwNDgxLCJuYmYiOjE1Nzg5MTY4ODEsImp0aSI6ImhHZEZsSUNnZE92M1haTHIiLCJzdWIiOjEsInBydiI6IjhiNDIyZTZmNjU3OTMyYjhhZWJjYjFiZjFlMzU2ZGQ3NmEzNjViZjIifQ.Aixgu_Quo8QH1oCgiMWIv_STLCE_p22AoHUmoKQlqYs",
        "token_type": "bearer",
        "expires_in": 3600
    }
}
```
