# Search item

---

 Search item

### Details

| Method | Uri               | Authorization |
| :----- | :---------------- | :------------ |
| POST   | `api/medicinereminder/searchitem` | YES            |

### Request

```json
{
	"param":"",
    "branch_id":10
}

```


### Response 

```json
{
    "status": "ok",
    "data": [
        {
            "stit_ID": 2,
            "stit_itemName": "Vaporub",
            "isMedicine": 0,
            "stit_SKU": "Nasal Congestion Vicks Vaporub lite 100 ml",
            "selling_prize": 0,
            "main_image": [
                {
                    "id": 138,
                    "product_id": 2,
                    "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-64753f49-e9c7-432d-b614-64fc82612a1c.jpg",
                    "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-64753f49-e9c7-432d-b614-64fc82612a1c.jpg"
                }
            ]
        },
        {
            "stit_ID": 3,
            "stit_itemName": "Stobal Cough Syrup",
            "isMedicine": 0,
            "stit_SKU": "cough SBL Stobal Cough Syrup   60 ml Syrup",
            "selling_prize": 55.5,
            "main_image": [
                {
                    "id": 140,
                    "product_id": 3,
                    "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-5a9d2bd3-e7d4-4d52-8e8b-4b52ec78670d.jpg",
                    "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-5a9d2bd3-e7d4-4d52-8e8b-4b52ec78670d.jpg"
                }
            ]
        },
        {
            "stit_ID": 4,
            "stit_itemName": "CROCIN",
            "isMedicine": 1,
            "stit_SKU": "CROCIN PARACETAMOL Antimigraine Preparations TAB 650mg Tablet ",
            "selling_prize": 99.7,
            "main_image": [
                {
                    "id": 211,
                    "product_id": 4,
                    "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-fb828754-420c-4b31-a333-b38664b65bf8.jpg",
                    "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-fb828754-420c-4b31-a333-b38664b65bf8.jpg"
                }
            ]
        },
        {
            "stit_ID": 5,
            "stit_itemName": "Honitus Herbal Cough",
            "isMedicine": 0,
            "stit_SKU": "cough Dabur Honitus Herbal Cough syrup 100ml",
            "selling_prize": 0,
            "main_image": [
                {
                    "id": 143,
                    "product_id": 5,
                    "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-47eb9e35-d74c-401b-9c4e-a52d1d169b9d.jpg",
                    "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-47eb9e35-d74c-401b-9c4e-a52d1d169b9d.jpg"
                }
            ]
        },
        {
            "stit_ID": 6,
            "stit_itemName": "Honitus Herbal Cough",
            "isMedicine": 0,
            "stit_SKU": "cough Dabur Honitus Herbal Cough syrup 200ml",
            "selling_prize": 0,
            "main_image": [
                {
                    "id": 147,
                    "product_id": 6,
                    "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-0b811771-d50f-4c48-b36a-e4bf8a959573.jpg",
                    "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-0b811771-d50f-4c48-b36a-e4bf8a959573.jpg"
                }
            ]
        },
        {
            "stit_ID": 7,
            "stit_itemName": "Cough Drops",
            "isMedicine": 0,
            "stit_SKU": "cough Vicks Cough Drops  190 lozenges",
            "selling_prize": 0,
            "main_image": [
                {
                    "id": 150,
                    "product_id": 7,
                    "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-ff060648-75ab-4ac8-b11d-2418a1867d05.jpg",
                    "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-ff060648-75ab-4ac8-b11d-2418a1867d05.jpg"
                }
            ]
        },
        {
            "stit_ID": 8,
            "stit_itemName": "Stobal Cough Syrup",
            "isMedicine": 0,
            "stit_SKU": "cough SBL Stobal Cough Syrup syrup 180ml",
            "selling_prize": 0,
            "main_image": [
                {
                    "id": 157,
                    "product_id": 8,
                    "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-7f6204b3-521f-4a1e-a250-ad3fb7178e35.jpg",
                    "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-7f6204b3-521f-4a1e-a250-ad3fb7178e35.jpg"
                }
            ]
        },
        {
            "stit_ID": 9,
            "stit_itemName": "Stobal Cough Syrup",
            "isMedicine": 0,
            "stit_SKU": "cough SBL Stobal Cough Syrup syrup 60ml",
            "selling_prize": 0,
            "main_image": [
                {
                    "id": 154,
                    "product_id": 9,
                    "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-fb963957-f032-47c0-872b-368a4134e63e.jpg",
                    "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-fb963957-f032-47c0-872b-368a4134e63e.jpg"
                }
            ]
        },
        {
            "stit_ID": 10,
            "stit_itemName": "Stobal Cough Syrup",
            "isMedicine": 0,
            "stit_SKU": "cough SBL Stobal Cough Syrup syrup 115ml",
            "selling_prize": 0,
            "main_image": [
                {
                    "id": 155,
                    "product_id": 10,
                    "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-3e8d1177-8cbb-4685-b139-e90837383aa0.jpg",
                    "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-3e8d1177-8cbb-4685-b139-e90837383aa0.jpg"
                }
            ]
        },
        {
            "stit_ID": 11,
            "stit_itemName": "Bronchoherb Cough Syrup",
            "isMedicine": 0,
            "stit_SKU": "cough SBL Bronchoherb Cough Syrup syrup 100ml",
            "selling_prize": 0,
            "main_image": [
                {
                    "id": 131,
                    "product_id": 11,
                    "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-ac27e98a-bc67-4fed-b1e9-7043c63eeb0a.jpg",
                    "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-ac27e98a-bc67-4fed-b1e9-7043c63eeb0a.jpg"
                }
            ]
        },
        {
            "stit_ID": 12,
            "stit_itemName": "Bronchoherb Cough Syrup",
            "isMedicine": 0,
            "stit_SKU": "cough SBL Bronchoherb Cough Syrup syrup 180ml",
            "selling_prize": 0,
            "main_image": [
                {
                    "id": 128,
                    "product_id": 12,
                    "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-09fc243d-fa5d-4f70-b752-386fafb2ce74.jpg",
                    "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-09fc243d-fa5d-4f70-b752-386fafb2ce74.jpg"
                }
            ]
        },
        {
            "stit_ID": 13,
            "stit_itemName": "Koflet Lozenges",
            "isMedicine": 0,
            "stit_SKU": "cough Himalaya Koflet Lozenges packet 10 lozenges",
            "selling_prize": 0,
            "main_image": [
                {
                    "id": 263,
                    "product_id": 13,
                    "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-380b9d66-0b8d-4b80-a6bb-78b2f5700af7.jpg",
                    "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-380b9d66-0b8d-4b80-a6bb-78b2f5700af7.jpg"
                }
            ]
        },
        {
            "stit_ID": 14,
            "stit_itemName": "Koflet Lozenges",
            "isMedicine": 0,
            "stit_SKU": "cough Himalaya Koflet Lozenges packet 200 lozenges",
            "selling_prize": 0,
            "main_image": [
                {
                    "id": 261,
                    "product_id": 14,
                    "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-b1485f1f-9865-4a8d-a4cc-543df4d0c951.jpg",
                    "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-b1485f1f-9865-4a8d-a4cc-543df4d0c951.jpg"
                }
            ]
        },
        {
            "stit_ID": 15,
            "stit_itemName": "Koflet Syrup ",
            "isMedicine": 0,
            "stit_SKU": "cough Himalaya Koflet Syrup  syrup 100ml",
            "selling_prize": 0,
            "main_image": [
                {
                    "id": 124,
                    "product_id": 15,
                    "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-7e68203f-9d1e-4799-b598-379deaf65989.jpg",
                    "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-7e68203f-9d1e-4799-b598-379deaf65989.jpg"
                }
            ]
        },
        {
            "stit_ID": 16,
            "stit_itemName": "SV 201 Steam Vaporizer",
            "isMedicine": 0,
            "stit_SKU": "Vaporizers Healthgenie SV 201 Steam Vaporizer box 1 unit",
            "selling_prize": 0,
            "main_image": [
                {
                    "id": 266,
                    "product_id": 16,
                    "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-0549886a-6072-44ec-936e-70f496d53d4e.jpg",
                    "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-0549886a-6072-44ec-936e-70f496d53d4e.jpg"
                }
            ]
        },
        {
            "stit_ID": 18,
            "stit_itemName": "Vitamin C 1000mg with Bioflavonoid",
            "isMedicine": 0,
            "stit_SKU": "Vitamin C GNC Vitamin C 1000mg with Bioflavonoid  180 caplets",
            "selling_prize": 0,
            "main_image": [
                {
                    "id": 276,
                    "product_id": 18,
                    "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-c9783954-db57-4e1d-929c-40a70117ca27.jpg",
                    "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-c9783954-db57-4e1d-929c-40a70117ca27.jpg"
                }
            ]
        },
        {
            "stit_ID": 20,
            "stit_itemName": "Turmeric Formula Veg Capsule",
            "isMedicine": 0,
            "stit_SKU": "OrganicIndia   Organic India Turmeric Formula Veg Capsule Vegcaps  60 vegicaps",
            "selling_prize": 0,
            "main_image": [
                {
                    "id": 121,
                    "product_id": 20,
                    "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-fc1f7b60-8c3d-4e28-b619-1e65000d3854.jpg",
                    "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-fc1f7b60-8c3d-4e28-b619-1e65000d3854.jpg"
                }
            ]
        },
        {
            "stit_ID": 21,
            "stit_itemName": "Flavoured Tea Cardamom Black",
            "isMedicine": 0,
            "stit_SKU": "Herbal Teas Royal Black Pearl Flavoured Tea Cardamom Black tea bags 15 tea bags",
            "selling_prize": 0,
            "main_image": [
                {
                    "id": 277,
                    "product_id": 21,
                    "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-79966a46-843e-4133-8bdc-079e6ca2bb48.jpg",
                    "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-79966a46-843e-4133-8bdc-079e6ca2bb48.jpg"
                }
            ]
        },
        {
            "stit_ID": 22,
            "stit_itemName": "Tulsi Drops",
            "isMedicine": 0,
            "stit_SKU": "Ayurvedic Supplements HealthVit Tulsi Drops  30ml liquid",
            "selling_prize": 0,
            "main_image": [
                {
                    "id": 118,
                    "product_id": 22,
                    "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-c5ec4933-ffd5-43a8-960a-a6db7e493db6.jpg",
                    "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-c5ec4933-ffd5-43a8-960a-a6db7e493db6.jpg"
                }
            ]
        },
        {
            "stit_ID": 23,
            "stit_itemName": "LEVOLIN INHALER",
            "isMedicine": 1,
            "stit_SKU": "LEVOLIN INHALER OTHER ANTIASTHMATIC & COPD PREPARATIONS Antiasthmatic & COPD Preparations MDI 50mcg ",
            "selling_prize": 0,
            "main_image": [
                {
                    "id": 215,
                    "product_id": 23,
                    "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-18100c56-8e19-459f-88d9-38ffaae00330.jpg",
                    "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-18100c56-8e19-459f-88d9-38ffaae00330.jpg"
                }
            ]
        },
        {
            "stit_ID": 24,
            "stit_itemName": "Ginseng & Ashwagandha Capsule",
            "isMedicine": 0,
            "stit_SKU": "Ayurvedic Supplements HealthVit Ginseng & Ashwagandha Capsule bottle of capsules 60 capsules",
            "selling_prize": 0,
            "main_image": [
                {
                    "id": 280,
                    "product_id": 24,
                    "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-a2a56092-27ee-4023-88ae-c841cfd375ca.jpg",
                    "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-a2a56092-27ee-4023-88ae-c841cfd375ca.jpg"
                }
            ]
        },
        {
            "stit_ID": 25,
            "stit_itemName": "Natural Ashwagandha Powder",
            "isMedicine": 0,
            "stit_SKU": "Ayurvedic Supplements HealthVit Natural Ashwagandha Powder packet of powder 100gm powder",
            "selling_prize": 0,
            "main_image": [
                {
                    "id": 284,
                    "product_id": 25,
                    "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-3538f742-77f5-4d89-a3f8-18f2daffd1a7.jpg",
                    "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-3538f742-77f5-4d89-a3f8-18f2daffd1a7.jpg"
                }
            ]
        },
        {
            "stit_ID": 26,
            "stit_itemName": "Wheat Grass Amla Juice",
            "isMedicine": 0,
            "stit_SKU": "Ayurvedic Supplements HealthVit Wheat Grass Amla Juice bottle of liquid 500ml liquid",
            "selling_prize": 0,
            "main_image": [
                {
                    "id": 288,
                    "product_id": 26,
                    "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-96ef794d-3841-4fcc-9236-fb8493842436.jpg",
                    "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-96ef794d-3841-4fcc-9236-fb8493842436.jpg"
                }
            ]
        },
        {
            "stit_ID": 27,
            "stit_itemName": "ACILOC-RD",
            "isMedicine": 1,
            "stit_SKU": "ACILOC-RD OMEPRAZOLE + DOMPERIDONE Antacids, Antireflux Agents & Antiulcerants TAB 150 Tablet ",
            "selling_prize": 0,
            "main_image": [
                {
                    "id": 256,
                    "product_id": 27,
                    "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-b67f052d-223d-4160-9b44-d078a2d2e49a.jpg",
                    "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-b67f052d-223d-4160-9b44-d078a2d2e49a.jpg"
                }
            ]
        },
        {
            "stit_ID": 28,
            "stit_itemName": "Van Tulsi Cough Syrup",
            "isMedicine": 0,
            "stit_SKU": "Ayurvedic Supplements Basic Ayurveda Van Tulsi Cough Syrup syrup 200ml ",
            "selling_prize": 0,
            "main_image": [
                {
                    "id": 109,
                    "product_id": 28,
                    "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-905db821-a761-497a-9c6d-e9ba3dc3ebe8.jpg",
                    "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-905db821-a761-497a-9c6d-e9ba3dc3ebe8.jpg"
                }
            ]
        },
        {
            "stit_ID": 29,
            "stit_itemName": "Bronchoherb Cough Syrup",
            "isMedicine": 1,
            "stit_SKU": "Bronchoherb Cough Syrup PANTOPRAZOLE Antacids, Antireflux Agents & Antiulcerants TAB Tablet ",
            "selling_prize": 0,
            "main_image": [
                {
                    "id": 257,
                    "product_id": 29,
                    "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-b2f12bff-8cba-4c82-b616-7629dab8cbba.jpg",
                    "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-b2f12bff-8cba-4c82-b616-7629dab8cbba.jpg"
                }
            ]
        },
        {
            "stit_ID": 30,
            "stit_itemName": "Ashwagandha Churna",
            "isMedicine": 0,
            "stit_SKU": "Ayurvedic Supplements Basic Ayurveda Ashwagandha Churna box of powder 100gm powder",
            "selling_prize": 0,
            "main_image": [
                {
                    "id": 103,
                    "product_id": 30,
                    "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-da28db3e-f99a-4d23-8b0d-68cf99be58c0.jpg",
                    "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-da28db3e-f99a-4d23-8b0d-68cf99be58c0.jpg"
                }
            ]
        },
        {
            "stit_ID": 31,
            "stit_itemName": "DIGENE TOTAL",
            "isMedicine": 1,
            "stit_SKU": "DIGENE TOTAL PANTOPRAZOLE Antacids, Antireflux Agents & Antiulcerants TAB 40mg  ",
            "selling_prize": 0,
            "main_image": [
                {
                    "id": 219,
                    "product_id": 31,
                    "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-84a88d92-4f2f-4d0e-96c9-c970157b31bd.jpg",
                    "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-84a88d92-4f2f-4d0e-96c9-c970157b31bd.jpg"
                }
            ]
        },
        {
            "stit_ID": 32,
            "stit_itemName": "Neem Leaf Juice",
            "isMedicine": 0,
            "stit_SKU": "Ayurvedic Supplements Basic Ayurveda Neem Leaf Juice bottle of liquid 500ml liquid",
            "selling_prize": 0,
            "main_image": [
                {
                    "id": 291,
                    "product_id": 32,
                    "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-ca1f7c29-7f52-4afa-9acb-73b1b2a69d1b.jpg",
                    "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-ca1f7c29-7f52-4afa-9acb-73b1b2a69d1b.jpg"
                }
            ]
        },
        {
            "stit_ID": 34,
            "stit_itemName": "Ojasvita Chocolate",
            "isMedicine": 0,
            "stit_SKU": "Ayurvedic Supplements Sri Sri Tattva Ojasvita Chocolate box of powder 200gm powder",
            "selling_prize": 0,
            "main_image": [
                {
                    "id": 295,
                    "product_id": 34,
                    "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-0ff07405-a7e2-4db2-ade9-2f21e7f78a02.jpg",
                    "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-0ff07405-a7e2-4db2-ade9-2f21e7f78a02.jpg"
                }
            ]
        },
        {
            "stit_ID": 35,
            "stit_itemName": "Ojasvita Chocolate",
            "isMedicine": 0,
            "stit_SKU": "Ayurvedic Supplements Sri Sri Tattva Ojasvita Chocolate box of powder 500gm powder",
            "selling_prize": 0,
            "main_image": [
                {
                    "id": 102,
                    "product_id": 35,
                    "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-69a3c79f-49ec-48eb-af94-202631912f0c.jpg",
                    "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-69a3c79f-49ec-48eb-af94-202631912f0c.jpg"
                }
            ]
        },
        {
            "stit_ID": 36,
            "stit_itemName": "Amla Candy Mango",
            "isMedicine": 0,
            "stit_SKU": "Ayurvedic Supplements Sri Sri Tattva Amla Candy Mango mango 400gm candy",
            "selling_prize": 0,
            "main_image": [
                {
                    "id": 96,
                    "product_id": 36,
                    "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-0cdcf5bb-51b5-4530-80e4-850831923f24.jpg",
                    "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-0cdcf5bb-51b5-4530-80e4-850831923f24.jpg"
                }
            ]
        },
        {
            "stit_ID": 38,
            "stit_itemName": "Glucorect Drop",
            "isMedicine": 0,
            "stit_SKU": "Homeopathy Medicines Adel Glucorect Drop Adel Pekana Germany 20ml drop",
            "selling_prize": 0,
            "main_image": [
                {
                    "id": 95,
                    "product_id": 38,
                    "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-7bcc9b8b-7317-498b-9a0f-0c384057a575.jpg",
                    "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-7bcc9b8b-7317-498b-9a0f-0c384057a575.jpg"
                }
            ]
        },
        {
            "stit_ID": 39,
            "stit_itemName": "COOL-PAN",
            "isMedicine": 1,
            "stit_SKU": "COOL-PAN PANTOPRAZOLE Antacids, Antireflux Agents & Antiulcerants TAB 40mg Tablet ",
            "selling_prize": 0,
            "main_image": [
                {
                    "id": 222,
                    "product_id": 39,
                    "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-fd8bb4fe-4b13-4b5e-b0c0-80a0a14d0ff2.jpg",
                    "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-fd8bb4fe-4b13-4b5e-b0c0-80a0a14d0ff2.jpg"
                }
            ]
        },
        {
            "stit_ID": 40,
            "stit_itemName": "Acid Phosphoric Dilution 30 ",
            "isMedicine": 0,
            "stit_SKU": "Homeopathy Medicines Adel Acid Phosphoric Dilution 30   10 ml Dilution",
            "selling_prize": 0,
            "main_image": [
                {
                    "id": 93,
                    "product_id": 40,
                    "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-5c50a7d2-c6d2-4e25-85fb-d6d2502e118d.jpg",
                    "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-5c50a7d2-c6d2-4e25-85fb-d6d2502e118d.jpg"
                }
            ]
        },
        {
            "stit_ID": 41,
            "stit_itemName": "PENTALINK-D",
            "isMedicine": 1,
            "stit_SKU": "PENTALINK-D PANTOPRAZOLE + DOMPERIDONE Antacids, Antireflux Agents & Antiulcerants TAB 10mg/40mg Tablet  ",
            "selling_prize": 0,
            "main_image": [
                {
                    "id": 252,
                    "product_id": 41,
                    "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-ab207501-5d58-4965-a517-9961502ade9e.jpg",
                    "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-ab207501-5d58-4965-a517-9961502ade9e.jpg"
                }
            ]
        },
        {
            "stit_ID": 42,
            "stit_itemName": "PEPTICOOL",
            "isMedicine": 1,
            "stit_SKU": "PEPTICOOL PANTOPRAZOLE Antacids, Antireflux Agents & Antiulcerants CAP DXR Capsule SR ",
            "selling_prize": 0,
            "main_image": [
                {
                    "id": 201,
                    "product_id": 42,
                    "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-a301e495-1757-451d-843e-363f237ded18.jpg",
                    "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-a301e495-1757-451d-843e-363f237ded18.jpg"
                }
            ]
        },
        {
            "stit_ID": 43,
            "stit_itemName": "PDAVIS",
            "isMedicine": 1,
            "stit_SKU": "PDAVIS PANTOPRAZOLE Antacids, Antireflux Agents & Antiulcerants TAB 40mg Tablet ",
            "selling_prize": 0,
            "main_image": [
                {
                    "id": 205,
                    "product_id": 43,
                    "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-47715798-64da-4edf-b2d8-fbfc041307fa.jpg",
                    "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-47715798-64da-4edf-b2d8-fbfc041307fa.jpg"
                }
            ]
        },
        {
            "stit_ID": 44,
            "stit_itemName": "DIOCID",
            "isMedicine": 1,
            "stit_SKU": "DIOCID OMEPRAZOLE Antacids, Antireflux Agents & Antiulcerants CAP 20mg Capsule ",
            "selling_prize": 0,
            "main_image": [
                {
                    "id": 204,
                    "product_id": 44,
                    "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-7ee2dc09-dd17-4a24-ab84-0389701bb001.jpg",
                    "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-7ee2dc09-dd17-4a24-ab84-0389701bb001.jpg"
                }
            ]
        },
        {
            "stit_ID": 45,
            "stit_itemName": "Urea Pura Dilution 200 CH",
            "isMedicine": 0,
            "stit_SKU": "Homeopathy Medicines Adel Urea Pura Dilution 200 CH  10ml ",
            "selling_prize": 0,
            "main_image": [
                {
                    "id": 90,
                    "product_id": 45,
                    "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-d7124718-1229-45df-a6b2-bbd86fb835c1.jpg",
                    "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-d7124718-1229-45df-a6b2-bbd86fb835c1.jpg"
                }
            ]
        },
        {
            "stit_ID": 46,
            "stit_itemName": "DIOCID-DSR",
            "isMedicine": 1,
            "stit_SKU": "DIOCID-DSR OMEPRAZOLE + DOMPERIDONE Antacids, Antireflux Agents & Antiulcerants CAP DSR Capsule ",
            "selling_prize": 0,
            "main_image": [
                {
                    "id": 250,
                    "product_id": 46,
                    "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-bf8102f2-86f5-498d-8019-32d11c79479d.jpg",
                    "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-bf8102f2-86f5-498d-8019-32d11c79479d.jpg"
                }
            ]
        },
        {
            "stit_ID": 47,
            "stit_itemName": "PROLEX",
            "isMedicine": 1,
            "stit_SKU": "PROLEX PANTOPRAZOLE Antacids, Antireflux Agents & Antiulcerants INJ 40mg Injection ",
            "selling_prize": 0,
            "main_image": [
                {
                    "id": 249,
                    "product_id": 47,
                    "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-5441f5f1-65af-434b-acab-4e2c4322f5fd.jpg",
                    "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-5441f5f1-65af-434b-acab-4e2c4322f5fd.jpg"
                }
            ]
        },
        {
            "stit_ID": 48,
            "stit_itemName": "PROLEX-DSR",
            "isMedicine": 1,
            "stit_SKU": "PROLEX-DSR PANTOPRAZOLE + DOMPERIDONE Antacids, Antireflux Agents & Antiulcerants CAP DSR Capsule ",
            "selling_prize": 0,
            "main_image": [
                {
                    "id": 248,
                    "product_id": 48,
                    "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-7699c3bf-939e-4583-9499-c5868e8de8c2.jpg",
                    "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-7699c3bf-939e-4583-9499-c5868e8de8c2.jpg"
                }
            ]
        },
        {
            "stit_ID": 49,
            "stit_itemName": "OMEE-MPS",
            "isMedicine": 1,
            "stit_SKU": "OMEE-MPS ALUMINIUM HYDROXIDE Antacids, Antireflux Agents & Antiulcerants O-SUSP Liquid Mint ",
            "selling_prize": 0,
            "main_image": [
                {
                    "id": 246,
                    "product_id": 49,
                    "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-bf0d9d61-704c-4f79-a065-e96c3851eb38.jpg",
                    "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-bf0d9d61-704c-4f79-a065-e96c3851eb38.jpg"
                }
            ]
        },
        {
            "stit_ID": 50,
            "stit_itemName": "Refill Pack Premium Chocolate",
            "isMedicine": 0,
            "stit_SKU": "For Children PediaSure Refill Pack Premium Chocolate packet of powder 1kg ",
            "selling_prize": 0,
            "main_image": [
                {
                    "id": 300,
                    "product_id": 50,
                    "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-7332b08c-b8e8-4cb1-b682-ae1e62859f79.jpg",
                    "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-7332b08c-b8e8-4cb1-b682-ae1e62859f79.jpg"
                }
            ]
        },
        {
            "stit_ID": 51,
            "stit_itemName": "ANCOOL",
            "isMedicine": 1,
            "stit_SKU": "ANCOOL ALUMINIUM HYDROXIDE Antacids, Antireflux Agents & Antiulcerants O-SUSP SF Suspension ",
            "selling_prize": 0,
            "main_image": [
                {
                    "id": 206,
                    "product_id": 51,
                    "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-877e0588-f7da-4209-bf33-a812ab05cec3.jpg",
                    "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-877e0588-f7da-4209-bf33-a812ab05cec3.jpg"
                }
            ]
        },
        {
            "stit_ID": 52,
            "stit_itemName": "Refill Pack Vanilla delight",
            "isMedicine": 0,
            "stit_SKU": "For Children PediaSure Refill Pack Vanilla delight powderi 1kg",
            "selling_prize": 0,
            "main_image": [
                {
                    "id": 296,
                    "product_id": 52,
                    "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-7887c1c7-9e64-4880-b1bd-976d76b44a5f.jpg",
                    "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-7887c1c7-9e64-4880-b1bd-976d76b44a5f.jpg"
                }
            ]
        },
        {
            "stit_ID": 53,
            "stit_itemName": "B-LANSO",
            "isMedicine": 1,
            "stit_SKU": "B-LANSO LANSOPRAZOLE Antacids, Antireflux Agents & Antiulcerants CAP 30mg Capsule ",
            "selling_prize": 0,
            "main_image": [
                {
                    "id": 242,
                    "product_id": 53,
                    "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-b76f3dc7-dbb7-4dc1-9a89-4a5bb3112310.jpg",
                    "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-b76f3dc7-dbb7-4dc1-9a89-4a5bb3112310.jpg"
                }
            ]
        },
        {
            "stit_ID": 54,
            "stit_itemName": "Refill Pack Vanilla delight",
            "isMedicine": 0,
            "stit_SKU": "For Children PediaSure Refill Pack Vanilla delight powders 200 g",
            "selling_prize": 0,
            "main_image": [
                {
                    "id": 298,
                    "product_id": 54,
                    "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-b041bbd5-0de2-44fb-b104-e4f0a1cc31c2.jpg",
                    "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-b041bbd5-0de2-44fb-b104-e4f0a1cc31c2.jpg"
                }
            ]
        },
        {
            "stit_ID": 55,
            "stit_itemName": "Refill Pack Vanilla delight",
            "isMedicine": 0,
            "stit_SKU": "For Children PediaSure Refill Pack Vanilla delight powder 750 g",
            "selling_prize": 0,
            "main_image": [
                {
                    "id": 299,
                    "product_id": 55,
                    "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-7e0a89d2-3f1a-4f4c-ac3f-284495a89b10.jpg",
                    "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-7e0a89d2-3f1a-4f4c-ac3f-284495a89b10.jpg"
                }
            ]
        },
        {
            "stit_ID": 56,
            "stit_itemName": "ESTOM",
            "isMedicine": 1,
            "stit_SKU": "ESTOM OMEPRAZOLE Antacids, Antireflux Agents & Antiulcerants CAP 40mg Capsule ",
            "selling_prize": 0,
            "main_image": [
                {
                    "id": 203,
                    "product_id": 56,
                    "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-5e583121-798b-4f75-91e4-8be9a2576ff4.jpg",
                    "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-5e583121-798b-4f75-91e4-8be9a2576ff4.jpg"
                }
            ]
        },
        {
            "stit_ID": 57,
            "stit_itemName": "Refill Pack Kesar Badam",
            "isMedicine": 0,
            "stit_SKU": "For Children PediaSure Refill Pack Kesar Badam box of powders 200g",
            "selling_prize": 0,
            "main_image": [
                {
                    "id": 88,
                    "product_id": 57,
                    "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-9803be8b-2846-466c-8b5e-2313e4771c04.jpg",
                    "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-9803be8b-2846-466c-8b5e-2313e4771c04.jpg"
                }
            ]
        },
        {
            "stit_ID": 58,
            "stit_itemName": "Refill Pack Kesar Badam",
            "isMedicine": 0,
            "stit_SKU": "For Children PediaSure Refill Pack Kesar Badam box of powder 1 kg",
            "selling_prize": 0,
            "main_image": []
        },
        {
            "stit_ID": 59,
            "stit_itemName": "JLROZ",
            "isMedicine": 1,
            "stit_SKU": "JLROZ LANSOPRAZOLE Antacids, Antireflux Agents & Antiulcerants MD-TAB 15mg Tablet MD ",
            "selling_prize": 0,
            "main_image": []
        },
        {
            "stit_ID": 60,
            "stit_itemName": "7+ Nutrition Drink Chocolate with Oats & Almond",
            "isMedicine": 0,
            "stit_SKU": "For Children  PediaSure 7+ Nutrition Drink Chocolate with Oats & Almond powder 200 g",
            "selling_prize": 99.8,
            "main_image": [
                {
                    "id": 85,
                    "product_id": 60,
                    "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-48e5ffb8-2cb4-4c44-beb9-fe1a923489af.jpg",
                    "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-48e5ffb8-2cb4-4c44-beb9-fe1a923489af.jpg"
                }
            ]
        },
        {
            "stit_ID": 62,
            "stit_itemName": "Berberis Aquifolium Gel",
            "isMedicine": 0,
            "stit_SKU": "Skin Care Products SBL Berberis Aquifolium Gel tube of gel 25g",
            "selling_prize": 0,
            "main_image": [
                {
                    "id": 81,
                    "product_id": 62,
                    "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-23d9130d-3120-471e-b378-bb81f0794f99.jpg",
                    "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-23d9130d-3120-471e-b378-bb81f0794f99.jpg"
                }
            ]
        },
        {
            "stit_ID": 63,
            "stit_itemName": "ACTIPRAZ-LXR",
            "isMedicine": 1,
            "stit_SKU": "ACTIPRAZ-LXR ESOMEPRAZOLE Antacids, Antireflux Agents & Antiulcerants CAP 75mg/40mg Capsule SR ",
            "selling_prize": 0,
            "main_image": [
                {
                    "id": 236,
                    "product_id": 63,
                    "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-72e9eecf-7c71-49f8-abbc-354f19d6284b.jpg",
                    "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-72e9eecf-7c71-49f8-abbc-354f19d6284b.jpg"
                }
            ]
        },
        {
            "stit_ID": 64,
            "stit_itemName": "NEKSIUM",
            "isMedicine": 1,
            "stit_SKU": "NEKSIUM ESOMEPRAZOLE Antacids, Antireflux Agents & Antiulcerants INJ 40mg Injection ",
            "selling_prize": 0,
            "main_image": [
                {
                    "id": 199,
                    "product_id": 64,
                    "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-68ef76ee-97fd-47a3-a8b3-b43abcff54be.jpg",
                    "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-68ef76ee-97fd-47a3-a8b3-b43abcff54be.jpg"
                }
            ]
        },
        {
            "stit_ID": 65,
            "stit_itemName": "Calendula Cream",
            "isMedicine": 0,
            "stit_SKU": "Skin Care Products SBL Calendula Cream cream 25g",
            "selling_prize": 65.78,
            "main_image": [
                {
                    "id": 77,
                    "product_id": 65,
                    "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-b737fa37-4885-4779-a478-1f9f0cee5b19.jpg",
                    "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-b737fa37-4885-4779-a478-1f9f0cee5b19.jpg"
                }
            ]
        },
        {
            "stit_ID": 66,
            "stit_itemName": "PROPANZ",
            "isMedicine": 1,
            "stit_SKU": "PROPANZ PANTOPRAZOLE Antacids, Antireflux Agents & Antiulcerants INJ 40mg Injection ",
            "selling_prize": 0,
            "main_image": [
                {
                    "id": 235,
                    "product_id": 66,
                    "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-36b47d31-5e8a-4b0c-b13d-24d103593bd2.jpg",
                    "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-36b47d31-5e8a-4b0c-b13d-24d103593bd2.jpg"
                }
            ]
        },
        {
            "stit_ID": 67,
            "stit_itemName": "ESZOL (SAIN)",
            "isMedicine": 1,
            "stit_SKU": "ESZOL (SAIN) ESOMEPRAZOLE Antacids, Antireflux Agents & Antiulcerants TAB 40mg Tablet ",
            "selling_prize": 0,
            "main_image": [
                {
                    "id": 232,
                    "product_id": 67,
                    "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-77cef6f1-8a8b-4b76-b422-743855c8b6cf.jpg",
                    "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-77cef6f1-8a8b-4b76-b422-743855c8b6cf.jpg"
                }
            ]
        },
        {
            "stit_ID": 69,
            "stit_itemName": "Wipe Clear Ache Lotion",
            "isMedicine": 0,
            "stit_SKU": "Skin Care Products SBL Wipe Clear Ache Lotion  30ml",
            "selling_prize": 0,
            "main_image": [
                {
                    "id": 114,
                    "product_id": 69,
                    "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-b5de9964-9d9f-45a7-8df1-6db4fa5c58a3.jpg",
                    "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-b5de9964-9d9f-45a7-8df1-6db4fa5c58a3.jpg"
                }
            ]
        },
        {
            "stit_ID": 70,
            "stit_itemName": "WONON-D",
            "isMedicine": 1,
            "stit_SKU": "WONON-D PANTOPRAZOLE + DOMPERIDONE Antacids, Antireflux Agents & Antiulcerants TAB 10mg/40mg Tablet ",
            "selling_prize": 0,
            "main_image": [
                {
                    "id": 226,
                    "product_id": 70,
                    "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-d9e804a9-fe81-4f42-88a2-6a6d864780c3.jpg",
                    "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-d9e804a9-fe81-4f42-88a2-6a6d864780c3.jpg"
                }
            ]
        },
        {
            "stit_ID": 71,
            "stit_itemName": "Silk N Stay Berberis Soap",
            "isMedicine": 0,
            "stit_SKU": "Skin Care Products SBL Silk N Stay Berberis Soap soap 75g",
            "selling_prize": 0,
            "main_image": [
                {
                    "id": 74,
                    "product_id": 71,
                    "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-27d75672-775a-4802-8eb0-93e641898f0e.jpg",
                    "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-27d75672-775a-4802-8eb0-93e641898f0e.jpg"
                }
            ]
        },
        {
            "stit_ID": 72,
            "stit_itemName": "WONON-DSR",
            "isMedicine": 1,
            "stit_SKU": "WONON-DSR PANTOPRAZOLE + DOMPERIDONE Antacids, Antireflux Agents & Antiulcerants CAP Capsule ",
            "selling_prize": 0,
            "main_image": [
                {
                    "id": 224,
                    "product_id": 72,
                    "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-f94ef4d3-f3ba-4fea-a360-a3219560ac25.jpg",
                    "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-f94ef4d3-f3ba-4fea-a360-a3219560ac25.jpg"
                }
            ]
        },
        {
            "stit_ID": 73,
            "stit_itemName": "SOME",
            "isMedicine": 1,
            "stit_SKU": "SOME ESOMEPRAZOLE Antacids, Antireflux Agents & Antiulcerants CAP 40mg Capsule ",
            "selling_prize": 0,
            "main_image": []
        },
        {
            "stit_ID": 75,
            "stit_itemName": "POLYCLAV",
            "isMedicine": 1,
            "stit_SKU": "POLYCLAV AMOXICILLIN + CLAVULANIC ACID Penicillins TAB 625mg Tablet ",
            "selling_prize": 0,
            "main_image": [
                {
                    "id": 194,
                    "product_id": 75,
                    "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-e92e69b5-86af-4f4e-8e94-3903b3be66ef.jpg",
                    "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-e92e69b5-86af-4f4e-8e94-3903b3be66ef.jpg"
                }
            ]
        },
        {
            "stit_ID": 77,
            "stit_itemName": "MOMTAS",
            "isMedicine": 1,
            "stit_SKU": "MOMTAS MOMETASONE Antiasthmatic & COPD Preparations CRM Cream ",
            "selling_prize": 0,
            "main_image": [
                {
                    "id": 192,
                    "product_id": 77,
                    "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-7858d24f-8b01-4ca4-ab9b-2d60438f16a2.jpg",
                    "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-7858d24f-8b01-4ca4-ab9b-2d60438f16a2.jpg"
                }
            ]
        },
        {
            "stit_ID": 79,
            "stit_itemName": "LACICLAV",
            "isMedicine": 1,
            "stit_SKU": "LACICLAV AMOXICILLIN + CLAVULANIC ACID Penicillins D-SYR Dry Syrup ",
            "selling_prize": 0,
            "main_image": [
                {
                    "id": 191,
                    "product_id": 79,
                    "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-1595d562-ca46-49bb-9fc9-592670a7526f.jpg",
                    "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-1595d562-ca46-49bb-9fc9-592670a7526f.jpg"
                }
            ]
        },
        {
            "stit_ID": 80,
            "stit_itemName": "RIFLUX FORTE",
            "isMedicine": 1,
            "stit_SKU": "RIFLUX FORTE ALUMINIUM HYDROXIDE Antacids, Antireflux Agents & Antiulcerants O-SUSP Liquid ",
            "selling_prize": 0,
            "main_image": [
                {
                    "id": 188,
                    "product_id": 80,
                    "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-354f9522-2b83-4629-aa69-636b29080eed.jpg",
                    "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-354f9522-2b83-4629-aa69-636b29080eed.jpg"
                }
            ]
        },
        {
            "stit_ID": 81,
            "stit_itemName": "Lipid Care Capsule",
            "isMedicine": 0,
            "stit_SKU": "Cardiac Care Organic India Lipid Care Capsule capsule 60",
            "selling_prize": 0,
            "main_image": [
                {
                    "id": 60,
                    "product_id": 81,
                    "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-9e421c22-fcbc-499d-b6be-5ce557b54822.jpg",
                    "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-9e421c22-fcbc-499d-b6be-5ce557b54822.jpg"
                }
            ]
        },
        {
            "stit_ID": 82,
            "stit_itemName": "Flaxseed Oil Capsule",
            "isMedicine": 0,
            "stit_SKU": "Cardiac Care Organic India Flaxseed Oil Capsule capsules 60",
            "selling_prize": 0,
            "main_image": [
                {
                    "id": 63,
                    "product_id": 82,
                    "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-07171eeb-1e72-4f59-951a-2baecc319acf.jpg",
                    "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-07171eeb-1e72-4f59-951a-2baecc319acf.jpg"
                }
            ]
        },
        {
            "stit_ID": 83,
            "stit_itemName": "Heart Guard Capsule",
            "isMedicine": 0,
            "stit_SKU": "Cardiac Care Organic India Heart Guard Capsule bottle of capsule 60",
            "selling_prize": 0,
            "main_image": [
                {
                    "id": 110,
                    "product_id": 83,
                    "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-c6c92b18-68f9-4458-a392-3bbd3713092f.jpg",
                    "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-c6c92b18-68f9-4458-a392-3bbd3713092f.jpg"
                }
            ]
        },
        {
            "stit_ID": 84,
            "stit_itemName": "OXIPOD",
            "isMedicine": 1,
            "stit_SKU": "OXIPOD CEFPIROME Cephalosporins O-DPS 25mg Oral Drops ",
            "selling_prize": 0,
            "main_image": []
        },
        {
            "stit_ID": 85,
            "stit_itemName": "ZEDOCEF",
            "isMedicine": 1,
            "stit_SKU": "ZEDOCEF CEFPIROME Cephalosporins TAB 200 Tablet ",
            "selling_prize": 0,
            "main_image": [
                {
                    "id": 186,
                    "product_id": 85,
                    "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-0acb3797-5195-49d0-82a2-df7e0d383af0.jpg",
                    "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-0acb3797-5195-49d0-82a2-df7e0d383af0.jpg"
                }
            ]
        },
        {
            "stit_ID": 88,
            "stit_itemName": "FUCIDIN",
            "isMedicine": 1,
            "stit_SKU": "FUCIDIN FUSIDIC ACID Topical Antibiotics CRM Cream ",
            "selling_prize": 0,
            "main_image": [
                {
                    "id": 183,
                    "product_id": 88,
                    "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-c3e38d10-c3c8-4bf2-8423-a6b15f05715e.jpg",
                    "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-c3e38d10-c3c8-4bf2-8423-a6b15f05715e.jpg"
                }
            ]
        },
        {
            "stit_ID": 90,
            "stit_itemName": "OXIPOD",
            "isMedicine": 1,
            "stit_SKU": "OXIPOD CEFPIROME Cephalosporins DT-TAB 100mg Tablet DT ",
            "selling_prize": 0,
            "main_image": [
                {
                    "id": 181,
                    "product_id": 90,
                    "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-4f763a9e-ae50-4607-82bd-68e865ff8c18.jpg",
                    "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-4f763a9e-ae50-4607-82bd-68e865ff8c18.jpg"
                }
            ]
        },
        {
            "stit_ID": 91,
            "stit_itemName": "STORVAS-EZ 20",
            "isMedicine": 1,
            "stit_SKU": "STORVAS-EZ 20 ATORVASTATIN + EZETIMIBE Dyslipidaemic Agents TAB Tablet ",
            "selling_prize": 0,
            "main_image": [
                {
                    "id": 178,
                    "product_id": 91,
                    "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-97c742e2-4667-4b2a-b7d7-c4dae459885a.jpg",
                    "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-97c742e2-4667-4b2a-b7d7-c4dae459885a.jpg"
                }
            ]
        },
        {
            "stit_ID": 92,
            "stit_itemName": "Hepano Tablet",
            "isMedicine": 0,
            "stit_SKU": "Liver Care Dabur Hepano Tablet tablets 60 tabs",
            "selling_prize": 0,
            "main_image": [
                {
                    "id": 38,
                    "product_id": 92,
                    "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-7dc90410-9aaf-4fae-930c-a6c8311c3006.jpg",
                    "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-7dc90410-9aaf-4fae-930c-a6c8311c3006.jpg"
                }
            ]
        },
        {
            "stit_ID": 93,
            "stit_itemName": "Koflet Lozenges",
            "isMedicine": 1,
            "stit_SKU": "Koflet Lozenges PANTOPRAZOLE Antacids, Antireflux Agents & Antiulcerants TAB 40mg Tablet ",
            "selling_prize": 0,
            "main_image": [
                {
                    "id": 53,
                    "product_id": 93,
                    "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-1953bfda-499d-4c37-9bae-232443931441.jpg",
                    "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-1953bfda-499d-4c37-9bae-232443931441.jpg"
                }
            ]
        },
        {
            "stit_ID": 96,
            "stit_itemName": "Triphala Tablet",
            "isMedicine": 0,
            "stit_SKU": "Stomach Care Jiva Triphala Tablet  120 tab",
            "selling_prize": 0,
            "main_image": [
                {
                    "id": 34,
                    "product_id": 96,
                    "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-192f18cc-4b74-4ea4-9eb1-935b148a28f1.jpg",
                    "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-192f18cc-4b74-4ea4-9eb1-935b148a28f1.jpg"
                }
            ]
        },
        {
            "stit_ID": 98,
            "stit_itemName": "POWERCEF",
            "isMedicine": 1,
            "stit_SKU": "POWERCEF CEFTIZOXIME Cephalosporins INJ 250mg Injection ",
            "selling_prize": 0,
            "main_image": [
                {
                    "id": 171,
                    "product_id": 98,
                    "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-44ff8265-f664-4a2f-b57c-66210ec8405a.jpg",
                    "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-44ff8265-f664-4a2f-b57c-66210ec8405a.jpg"
                }
            ]
        },
        {
            "stit_ID": 100,
            "stit_itemName": "OMNICEF-O",
            "isMedicine": 1,
            "stit_SKU": "OMNICEF-O CEFIXIME Cephalosporins O-SUSP 50mg Oral Suspension ",
            "selling_prize": 0,
            "main_image": [
                {
                    "id": 168,
                    "product_id": 100,
                    "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-1a530602-376e-4230-8aac-dbe1c0cdf261.jpg",
                    "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-1a530602-376e-4230-8aac-dbe1c0cdf261.jpg"
                }
            ]
        },
        {
            "stit_ID": 102,
            "stit_itemName": "PRETIBENZYL",
            "isMedicine": 1,
            "stit_SKU": "PRETIBENZYL BENZOYL PEROXIDE Acne Treatment Preparations LOT Lotion ",
            "selling_prize": 0,
            "main_image": [
                {
                    "id": 167,
                    "product_id": 102,
                    "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-231f599b-f10e-4c9e-985d-224a8aba9d1c.jpg",
                    "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-231f599b-f10e-4c9e-985d-224a8aba9d1c.jpg"
                }
            ]
        },
        {
            "stit_ID": 103,
            "stit_itemName": "PEBLO",
            "isMedicine": 1,
            "stit_SKU": "PEBLO PANTOPRAZOLE Antacids, Antireflux Agents & Antiulcerants INJ Injection 40mg",
            "selling_prize": 0,
            "main_image": [
                {
                    "id": 58,
                    "product_id": 103,
                    "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-8716893f-ab31-4b46-b36e-6253a7a8651f.jpg",
                    "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-8716893f-ab31-4b46-b36e-6253a7a8651f.jpg"
                }
            ]
        },
        {
            "stit_ID": 104,
            "stit_itemName": "NADIM",
            "isMedicine": 1,
            "stit_SKU": "NADIM OTHER TOPICAL ANTIBIOTICS Topical Antibiotics CRM Cream ",
            "selling_prize": 0,
            "main_image": [
                {
                    "id": 165,
                    "product_id": 104,
                    "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-d94694bb-25ef-4c22-9967-16b3c0ed128b.jpg",
                    "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-d94694bb-25ef-4c22-9967-16b3c0ed128b.jpg"
                }
            ]
        },
        {
            "stit_ID": 105,
            "stit_itemName": "OLFI-O",
            "isMedicine": 1,
            "stit_SKU": "OLFI-O OFLOXACIN Eye Anti-Infectives & Antiseptics TAB Tablet ",
            "selling_prize": 0,
            "main_image": [
                {
                    "id": 163,
                    "product_id": 105,
                    "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-8ba53ade-11bb-4e60-8ab8-1a430b75e52e.jpg",
                    "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-8ba53ade-11bb-4e60-8ab8-1a430b75e52e.jpg"
                }
            ]
        },
        {
            "stit_ID": 106,
            "stit_itemName": "HARPOON-DD",
            "isMedicine": 1,
            "stit_SKU": "HARPOON-DD OFLOXACIN + TINIDAZOLE Antibacterial Combinations FC-TAB Tablet 200mg/600mg ",
            "selling_prize": 0,
            "main_image": [
                {
                    "id": 162,
                    "product_id": 106,
                    "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-7a6c55fc-a823-49fc-810b-5c1319589619.jpg",
                    "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-7a6c55fc-a823-49fc-810b-5c1319589619.jpg"
                }
            ]
        },
        {
            "stit_ID": 107,
            "stit_itemName": "Throat Aid Tablet",
            "isMedicine": 0,
            "stit_SKU": "Respiratory Wellness Bakson's Throat Aid Tablet  75tabs",
            "selling_prize": 0,
            "main_image": [
                {
                    "id": 19,
                    "product_id": 107,
                    "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-bca2cf73-0dea-4905-9041-25b36dba2133.jpg",
                    "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-bca2cf73-0dea-4905-9041-25b36dba2133.jpg"
                }
            ]
        },
        {
            "stit_ID": 109,
            "stit_itemName": "Acid Phosphoric Dilution 30 ",
            "isMedicine": 0,
            "stit_SKU": "Adult Diapers Imiana Acid Phosphoric Dilution 30   ",
            "selling_prize": 34.95,
            "main_image": [
                {
                    "id": 12,
                    "product_id": 109,
                    "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-45e8a492-42c3-4d63-a7e2-65b7a7d988bc.jpg",
                    "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-45e8a492-42c3-4d63-a7e2-65b7a7d988bc.jpg"
                }
            ]
        },
        {
            "stit_ID": 110,
            "stit_itemName": "Heart Guard Capsule",
            "isMedicine": 0,
            "stit_SKU": "Accu-Check Accu Chek Heart Guard Capsule Tablet 52tabs",
            "selling_prize": 0,
            "main_image": [
                {
                    "id": 9,
                    "product_id": 110,
                    "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-0ea1616c-9314-406f-965d-c90bda802818.jpg",
                    "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-0ea1616c-9314-406f-965d-c90bda802818.jpg"
                }
            ]
        },
        {
            "stit_ID": 112,
            "stit_itemName": "FITGEL",
            "isMedicine": 1,
            "stit_SKU": "FITGEL ALUMINIUM HYDROXIDE Antacids, Antireflux Agents & Antiulcerants O-SUSP tabs 10",
            "selling_prize": 0,
            "main_image": [
                {
                    "id": 307,
                    "product_id": 112,
                    "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-c4fbdf47-848d-444a-a6cb-0ce5e6347a42.jpg",
                    "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-c4fbdf47-848d-444a-a6cb-0ce5e6347a42.jpg"
                }
            ]
        },
        {
            "stit_ID": 114,
            "stit_itemName": "Aloe Vera Juice",
            "isMedicine": 0,
            "stit_SKU": "Ayurvedic Medicines Baby Staples Aloe Vera Juice 100 10",
            "selling_prize": 0,
            "main_image": [
                {
                    "id": 3,
                    "product_id": 114,
                    "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-057aec43-bf91-446a-b6c3-6bcf26edad7e.jpg",
                    "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-057aec43-bf91-446a-b6c3-6bcf26edad7e.jpg"
                }
            ]
        },
        {
            "stit_ID": 115,
            "stit_itemName": "Wipe Clear Ache Lotion",
            "isMedicine": 0,
            "stit_SKU": "Beauty Supplements Himalaya Wipe Clear Ache Lotion Cream 500 gm",
            "selling_prize": 0,
            "main_image": [
                {
                    "id": 1,
                    "product_id": 115,
                    "image_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/preview-cb8767db-7220-4908-805b-04f14010ad75.jpg",
                    "image_thumb_url": "https://gogomedsdev.s3.ap-southeast-1.amazonaws.com/products/thumbnail-cb8767db-7220-4908-805b-04f14010ad75.jpg"
                }
            ]
        },
        {
            "stit_ID": 116,
            "stit_itemName": "OLYO SUN",
            "isMedicine": 1,
            "stit_SKU": "OLYO SUN OTHER EMOLLIENTS & SKIN PROTECTIVES Emollients & Skin Protectives CRM Tablets 50 tablets",
            "selling_prize": 0,
            "main_image": []
        },
        {
            "stit_ID": 117,
            "stit_itemName": "SOME",
            "isMedicine": 1,
            "stit_SKU": "SOME ESOMEPRAZOLE Antacids, Antireflux Agents & Antiulcerants CAP 200mg ",
            "selling_prize": 0,
            "main_image": []
        },
        {
            "stit_ID": 118,
            "stit_itemName": "Vicks Action 500",
            "isMedicine": 1,
            "stit_SKU": "Vicks Action 500 CLOZAPINE Antipsychotics O-GEL  500 g",
            "selling_prize": 0,
            "main_image": []
        },
        {
            "stit_ID": 119,
            "stit_itemName": "Vicks Action 500",
            "isMedicine": 1,
            "stit_SKU": "Vicks Action 500 CLOZAPINE Antipsychotics O-GEL  1000 g",
            "selling_prize": 0,
            "main_image": []
        },
        {
            "stit_ID": 120,
            "stit_itemName": "Vicks Action 500",
            "isMedicine": 1,
            "stit_SKU": "Vicks Action 500 CLOZAPINE Antipsychotics O-GEL  100 g",
            "selling_prize": 0,
            "main_image": []
        }
    ]
}
```


