# Medicine search

---

### Details

| Method | Uri               | Authorization |
| :----- | :---------------- | :------------ |
| POST   | `key/search` | NO            |

### Request


```json
{
"param":"cool"
}
```

### Response 

```json
{
    "status": "ok",
    "data": [
        {
            "stit_ID": 39,
            "stit_SKU": "COOL-PAN PANTOPRAZOLE Antacids, Antireflux Agents & Antiulcerants TAB 40mg Tablet ",
            "isMedicine": 1
        },
        {
            "stit_ID": 42,
            "stit_SKU": "PEPTICOOL PANTOPRAZOLE Antacids, Antireflux Agents & Antiulcerants CAP DXR Capsule SR ",
            "isMedicine": 1
        },
        {
            "stit_ID": 51,
            "stit_SKU": "ANCOOL ALUMINIUM HYDROXIDE Antacids, Antireflux Agents & Antiulcerants O-SUSP SF Suspension ",
            "isMedicine": 1
        }
    ]
}

```
