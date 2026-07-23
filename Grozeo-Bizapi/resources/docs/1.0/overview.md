# Overview



## Base Url

<larecipe-badge type="primary" rounded>Dev</larecipe-badge>

```text
http://dev.api.mypharmacy.velosit.in/api/
```

<larecipe-badge type="primary" rounded>Uat</larecipe-badge>

```text
Uat: 
```

<a name="authorization"></a>
## Authorization

> {primary} Authorization is done through headers

| Header | Value |
| : |   :-   |
| Authorization | Bearer {token} |

<a name="response"></a>
## Response Structure

<larecipe-badge type="success" rounded>Success</larecipe-badge>

```json
{
	"status": "ok",
	"data": {
		...
	}
}
```

<larecipe-badge type="danger" rounded>Error</larecipe-badge>

```json
{
    "status": "error",
    "error": {
        "msg": "Token has expired",
        "code": "401"
    }
}
```


<larecipe-badge type="danger" rounded>Error</larecipe-badge>
```json
{
    "status": "error",
    "error": {
        "msg": "Token Signature could not be verified."
    }
}
```

##Suppose token is expired.At that time call below Api (Refresh Api)


| Method | Uri             | Authorization |
| :----- | :-------------- | :------------ |
| POST   | `refresh` | NO            |

## 
|Header | Value |
| : |   :-   |
|Authorization | Bearer {expired token} |

<larecipe-badge type="success" rounded>Success</larecipe-badge>

```json
{
    "status": "ok",
    "data": {
        "access_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOlwvXC9teS1waGFybWFjeS1hcGkudGVzdFwvYXBpXC9yZWZyZXNoIiwiaWF0IjoxNTc5MDY0MDM4LCJleHAiOjE1NzkwNjc2NjEsIm5iZiI6MTU3OTA2NDA2MSwianRpIjoiSmhINVJYTW92RGdxRkZTSSIsInN1YiI6NjAsInBydiI6IjhiNDIyZTZmNjU3OTMyYjhhZWJjYjFiZjFlMzU2ZGQ3NmEzNjViZjIifQ.rYRM56upfSjsWTfUjVzPYcGs78dlhsiQ6HA9dVkpZVA",
        "token_type": "bearer",
        "expires_in": 3600
    }
}

```


<larecipe-badge type="danger" rounded>Error</larecipe-badge>
```json
{
    "status": "error",
    "error": {
        "msg": "The token has been blacklisted"
    }
}
```
##LOGOUT API


| Method | Uri             | Authorization |
| :----- | :-------------- | :------------ |
| POST   | `logout` | YES            |

## 
|Header | Value |
| : |   :-   |
|Authorization | Bearer {token} |

<larecipe-badge type="success" rounded>Success</larecipe-badge>

```json
{
    "status": "ok",
    "data": "Successfully logged out"
}

```


