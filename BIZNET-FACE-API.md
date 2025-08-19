# Biznet Face API Documentation

## Face AI at Riset.ai

> Can codes improve human lives? At Riset.ai, we're creating a new form of artificial intelligence algorithm, most of the people will call it life.

Our face products use models based on locally developed data sets. These models are perfect solutions for seamless non-contact authentication. This technology can be integrated with other authentication factors and authentication mechanisms, creating a multimodal verification technique.

## Getting Started

### 1. Get Token ID
- Login into https://portal.biznetgio.com
- In the sidebar select AI and ML, then Face Recognition
- Click **Create New Face Recognition Service**
- Fill in Service Name and Select Package, then press next
- Choose a payment method, then click order, and make payment
- Click the service that has been created, on the service page there is Token ID information

### 2. API Topology Overview
The following is a generic top-level view of the system. You are the client.

- **Client**: The admin or the owner of several FaceGallery. The client is your program (application) that is accessing our API. The client may have access to multiple FaceGallery.
- **FaceGallery**: A collection of users from a place or area. Think of it as a "database".
- **User**: The person who uses the API for recognition. User will give their face (to be exact) in the database.

### 3. Create New Facegallery
FaceGallery is a collection of users, please refer to the Create Face Gallery API to make a new Facegallery.

### 4. Play with the API
Try to enroll a user and then try identification and verification.

### 5. Still in trouble?
Look at the tutorial for hitting API.

## Status Codes

| Status Code | Type | Description |
|-------------|------|-------------|
| 200 | Success | Success messages |
| 400 | General Error | Request malformed |
| 401 | General Error | Access token not authorized |
| 403 | General Error | Requested resource denied |
| 411 | Business Process Warning | Face not verified or unregistered |
| 412 | Business Process Warning | Face not detected |
| 413 | Business Process Warning | Face too small |
| 415 | Resource Not Found | user_id not found |
| 416 | Resource Not Found | facegallery_id not found |
| 451 | Resource Not Found | image is null |
| 452 | Data Format Error | user_id is null |
| 453 | Data Format Error | user_name is null |
| 454 | Data Format Error | facegallery_id is null |
| 455 | Data Format Error | target_image is null |
| 456 | Data Format Error | source_image is null |
| 490 | Image Error | Cannot decode image base64 |
| 491 | Image Error | image type not recognized |
| 492 | Image Error | Cannot decode target_image base64 |
| 493 | Image Error | image error |
| 494 | Image Error | Cannot decode source_image base64 |
| 495 | Image Error | source_image type not recognized |
| 500 | Procedural Error | Server error |

## API Endpoints

### Client Endpoints

#### GET Get Counters
**Endpoint:** `https://fr.neoapi.id/risetai/face-api/client/get-counters`

This API fetches client's API Counters Remaining Quota (API Hits, Num Faces Enrolled, & Num FaceGallery Owned).

**Headers:**
```
Accesstoken: TOKEN_ID
```

**Request Body:**
```json
{
    "trx_id": "alphanumericalstring1234"
}
```

**Request Parameters:**

| Key | Type | Description |
|-----|------|-------------|
| trx_id | string | Unique transaction identifier, for transaction logging and debugging purposes |

**Response:**

| Key | Type | Description |
|-----|------|-------------|
| status | string | Describing the condition of API hit, please refer to List of Status Code |
| status_message | string | The verbose message of API hit status, please refer to List of Status Code |
| remaining_limit | array | Contains counters remaining quota/limit |
| n_api_hits | int | API Hits remaining limit |
| n_face | int | Remaining number of faces eligible to enroll |
| n_facegallery | int | Remaining number of facegallery eligible to create |

**PHP Example:**
```php
<?php
$client = new Client();
$headers = [
  'Accesstoken' => 'TOKEN_ID'
];
$body = '{
    "trx_id": "alphanumericalstring1234"
}';
$request = new Request('GET', 'https://fr.neoapi.id/risetai/face-api/client/get-counters', $headers, $body);
$res = $client->sendAsync($request)->wait();
echo $res->getBody();
```

---

### Facegallery Endpoints

#### GET My Facegalleries
**Endpoint:** `https://fr.neoapi.id/risetai/face-api/facegallery/my-facegalleries`

This API gives the list of facegallery.

**Headers:**
```
Accesstoken: TOKEN_ID
```

**Response:**

| Key | Type | Description |
|-----|------|-------------|
| status | string | Describing the condition of API hit, please refer to List of Status Code |
| status_message | string | The verbose message of API hit status, please refer to List of Status Code |
| facegallery_id | list | List of facegallery |

**PHP Example:**
```php
<?php
$client = new Client();
$headers = [
  'Accesstoken' => 'TOKEN_ID'
];
$request = new Request('GET', 'https://fr.neoapi.id/risetai/face-api/facegallery/my-facegalleries', $headers);
$res = $client->sendAsync($request)->wait();
echo $res->getBody();
```

#### POST Create Facegallery
**Endpoint:** `https://fr.neoapi.id/risetai/face-api/facegallery/create-facegallery`

This API creates a new face gallery.

**Headers:**
```
Accesstoken: TOKEN_ID
```

**Request Body:**
```json
{
  "facegallery_id": "riset.ai@production",
  "trx_id": "alphanumericalstring1234"
}
```

**Request Parameters:**

| Key | Type | Description |
|-----|------|-------------|
| facegallery_id | string | Name of facegallery |
| trx_id | string | Unique transaction identifier, for transaction logging and debugging purposes |

**Response:**

| Key | Type | Description |
|-----|------|-------------|
| status | string | Describing the condition of API hit, please refer to List of Status Code |
| status_message | string | The verbose message of API hit status, please refer to List of Status Code |
| facegallery_id | string | Name of facegallery |

**PHP Example:**
```php
<?php
$client = new Client();
$headers = [
  'Accesstoken' => 'TOKEN_ID'
];
$body = '{
  "facegallery_id": "riset.ai@production",
  "trx_id": "alphanumericalstring1234"
}';
$request = new Request('POST', 'https://fr.neoapi.id/risetai/face-api/facegallery/create-facegallery', $headers, $body);
$res = $client->sendAsync($request)->wait();
echo $res->getBody();
```

#### DELETE Delete Facegallery
**Endpoint:** `https://fr.neoapi.id/risetai/face-api/facegallery/delete-facegallery`

This API deletes a facegallery.

**Headers:**
```
Accesstoken: TOKEN_ID
```

**Request Body:**
```json
{
  "facegallery_id": "riset.ai@production",
  "trx_id": "alphanumericalstring1234"
}
```

**Request Parameters:**

| Key | Type | Description |
|-----|------|-------------|
| facegallery_id | string | Name of facegallery |
| trx_id | string | Unique transaction identifier, for transaction logging and debugging purposes |

**Response:**

| Key | Type | Description |
|-----|------|-------------|
| status | string | Describing the condition of API hit, please refer to List of Status Code |
| status_message | string | The verbose message of API hit status, please refer to List of Status Code |
| facegallery_id | string | Name of deleted facegallery |

**PHP Example:**
```php
<?php
$client = new Client();
$headers = [
  'Accesstoken' => 'TOKEN_ID'
];
$body = '{
  "facegallery_id": "riset.ai@production",
  "trx_id": "alphanumericalstring1234"
}';
$request = new Request('DELETE', 'https://fr.neoapi.id/risetai/face-api/facegallery/delete-facegallery', $headers, $body);
$res = $client->sendAsync($request)->wait();
echo $res->getBody();
```

---

### User Endpoints

#### POST Enroll Face
**Endpoint:** `https://fr.neoapi.id/risetai/face-api/facegallery/enroll-face`

This API registers a user to the database.

**Headers:**
```
Accesstoken: TOKEN_ID
```

**Request Body:**
```json
{
  "user_id": "risetai1234",
  "user_name": "RisetAi Username1",
  "facegallery_id": "riset.ai@production",
  "image": "Base64 encoded",
  "trx_id": "alphanumericalstring1234"
}
```

**Request Parameters:**

| Key | Type | Description |
|-----|------|-------------|
| user_id | string | Unique user identifier, alphanumeric (eg. #NIK) |
| user_name | string | The name of the person who has the user_id |
| facegallery_id | string | Unique FaceGallery identifier, alphanumeric (eg. LocationName, CompanyName, etc) |
| image | string | Base64 encoded JPG or PNG image |
| trx_id | string | Unique transaction identifier, for transaction logging and debugging purposes |

**Response:**

| Key | Type | Description |
|-----|------|-------------|
| status | string | Describing the condition of API hit, please refer to List of Status Code |
| status_message | string | The verbose message of API hit status, please refer to List of Status Code |

**PHP Example:**
```php
<?php
$client = new Client();
$headers = [
  'Accesstoken' => 'TOKEN_ID'
];
$body = '{
  "user_id": "risetai1234",
  "user_name": "RisetAi Username1",
  "facegallery_id": "riset.ai@production",
  "image": "Base64 encoded",
  "trx_id": "alphanumericalstring1234"
}';
$request = new Request('POST', 'https://fr.neoapi.id/risetai/face-api/facegallery/enroll-face', $headers, $body);
$res = $client->sendAsync($request)->wait();
echo $res->getBody();
```

#### GET List Faces
**Endpoint:** `https://fr.neoapi.id/risetai/face-api/facegallery/list-faces`

This API gives a list of the registered users.

**Headers:**
```
Accesstoken: TOKEN_ID
```

**Request Body:**
```json
{
  "facegallery_id": "riset.ai@production",
  "trx_id": "alphanumericalstring1234"
}
```

**Request Parameters:**

| Key | Type | Description |
|-----|------|-------------|
| facegallery_id | string | Unique FaceGallery identifier, alphanumeric (eg. LocationName, CompanyName, etc) |
| trx_id | string | Unique transaction identifier, for transaction logging and debugging purposes |

**Response:**

| Key | Type | Description |
|-----|------|-------------|
| status | string | Describing the condition of API hit, please refer to List of Status Code |
| status_message | string | The verbose message of API hit status, please refer to List of Status Code |
| faces | list | List of registered users |

**PHP Example:**
```php
<?php
$client = new Client();
$headers = [
  'Accesstoken' => 'TOKEN_ID'
];
$body = '{
  "facegallery_id": "riset.ai@production",
  "trx_id": "alphanumericalstring1234"
}';
$request = new Request('GET', 'https://fr.neoapi.id/risetai/face-api/facegallery/list-faces', $headers, $body);
$res = $client->sendAsync($request)->wait();
echo $res->getBody();
```

#### POST Verify Face
**Endpoint:** `https://fr.neoapi.id/risetai/face-api/facegallery/verify-face`

This API verifies a user_id and an image with a registered user or it does 1:1 authentication.

**Headers:**
```
Accesstoken: TOKEN_ID
```

**Request Body:**
```json
{
  "user_id": "risetai1234",
  "facegallery_id": "riset.ai@production",
  "image": "Base64 encoded",
  "trx_id": "alphanumericalstring1234"
}
```

**Request Parameters:**

| Key | Type | Description |
|-----|------|-------------|
| user_id | string | Unique user identifier, alphanumeric (eg. #NIK) |
| facegallery_id | string | Unique FaceGallery identifier, alphanumeric (eg. LocationName, CompanyName, etc) |
| image | string | Base64 encoded JPG or PNG image |
| trx_id | string | Unique transaction identifier, for transaction logging and debugging purposes |

**Response:**

| Key | Type | Description |
|-----|------|-------------|
| status | string | Describing the condition of API hit, please refer to List of Status Code |
| status_message | string | The verbose message of API hit status, please refer to List of Status Code |
| user_name | string | Username |
| similarity | float | Describe the comparison of facial similarities, scale 0.0 to 1.0 (from 0% to 100% similar) |
| masker | boolean | If a person's face wearing a mask, will return True, else return False |
| verified | boolean | If similarity above set config parameter(eg. threshold = 0.75), return True, else return False |

**PHP Example:**
```php
<?php
$client = new Client();
$headers = [
  'Accesstoken' => 'TOKEN_ID'
];
$body = '{
  "user_id": "risetai1234",
  "facegallery_id": "riset.ai@production",
  "image": "Base64 encoded",
  "trx_id": "alphanumericalstring1234"
}';
$request = new Request('POST', 'https://fr.neoapi.id/risetai/face-api/facegallery/verify-face', $headers, $body);
$res = $client->sendAsync($request)->wait();
echo $res->getBody();
```

#### POST Identify Face
**Endpoint:** `https://fr.neoapi.id/risetai/face-api/facegallery/identify-face`

This API identifies an image with a registered user or it does 1:N authentication.

**Headers:**
```
Accesstoken: TOKEN_ID
```

**Request Body:**
```json
{
  "facegallery_id": "riset.ai@production",
  "image": "Base64 encoded",
  "trx_id": "alphanumericalstring1234"
}
```

**Request Parameters:**

| Key | Type | Description |
|-----|------|-------------|
| facegallery_id | string | Unique FaceGallery identifier, alphanumeric (eg. LocationName, CompanyName, etc) |
| image | string | Base64 encoded JPG or PNG image |
| trx_id | string | Unique transaction identifier, for transaction logging and debugging purposes |

**Response:**

| Key | Type | Description |
|-----|------|-------------|
| status | string | Describing the condition of API hit, please refer to List of Status Code |
| status_message | string | The verbose message of API hit status, please refer to List of Status Code |
| confidence_level | float | Describe confidence of model, scale 0.0 to 1.0 (from 0% to 100% confidence) |
| mask | boolean | If a person's face wearing a mask, will return True, else return False |
| user_id | string | Unique user identifier, alphanumeric (eg. #NIK) |
| user_name | string | The name of the person who has the user_id |

**PHP Example:**
```php
<?php
$client = new Client();
$headers = [
  'Accesstoken' => 'TOKEN_ID'
];
$body = '{
  "facegallery_id": "riset.ai@production",
  "image": "Base64 encoded",
  "trx_id": "alphanumericalstring1234"
}';
$request = new Request('POST', 'https://fr.neoapi.id/risetai/face-api/facegallery/identify-face', $headers, $body);
$res = $client->sendAsync($request)->wait();
echo $res->getBody();
```

#### DELETE Delete Face
**Endpoint:** `https://fr.neoapi.id/risetai/face-api/facegallery/delete-face`

This API deletes a user.

**Headers:**
```
Accesstoken: TOKEN_ID
```

**Request Body:**
```json
{
  "user_id": "risetai1234",
  "facegallery_id": "riset.ai@production",
  "trx_id": "alphanumericalstring1234"
}
```

**Request Parameters:**

| Key | Type | Description |
|-----|------|-------------|
| user_id | string | Unique user identifier, alphanumeric (eg. #NIK) |
| facegallery_id | string | Unique FaceGallery identifier, alphanumeric (eg. LocationName, CompanyName, etc) |
| trx_id | string | Unique transaction identifier, for transaction logging and debugging purposes |

**Response:**

| Key | Type | Description |
|-----|------|-------------|
| status | string | Describing the condition of API hit, please refer to List of Status Code |
| status_message | string | The verbose message of API hit status, please refer to List of Status Code |

**PHP Example:**
```php
<?php
$client = new Client();
$headers = [
  'Accesstoken' => 'TOKEN_ID'
];
$body = '{
  "user_id": "risetai1234",
  "facegallery_id": "riset.ai@production",
  "trx_id": "alphanumericalstring1234"
}';
$request = new Request('DELETE', 'https://fr.neoapi.id/risetai/face-api/facegallery/delete-face', $headers, $body);
$res = $client->sendAsync($request)->wait();
echo $res->getBody();
```

---

### Image Comparison Endpoint

#### POST Compare Images
**Endpoint:** `https://fr.neoapi.id/risetai/face-api/compare-images`

This API compares two images to determine if they are verified or not. This API does not use the information in the database.

**Headers:**
```
Accesstoken: TOKEN_ID
```

**Request Body:**
```json
{
  "source_image": "Base64 encoded",
  "target_image": "Base64 encoded",
  "trx_id": "alphanumericalstring1234"
}
```

**Request Parameters:**

| Key | Type | Description |
|-----|------|-------------|
| source_image | string | Base64 encoded JPG or PNG of compared image |
| target_image | string | Base64 encoded JPG or PNG of the reference image |
| trx_id | string | Unique transaction identifier, for transaction logging and debugging purposes |

**Response:**

| Key | Type | Description |
|-----|------|-------------|
| status | string | Describing the condition of API hit, please refer to List of Status Code |
| status_message | string | The verbose message of API hit status, please refer to List of Status Code |
| similarity | float | Describe the comparison of facial similarities, scale 0.0 to 1.0 (from 0% to 100% similar) |
| verified | boolean | If similarity above set config parameter(eg. threshold = 0.75), return True, else return False |
| masker | boolean | If a person's face wearing a mask, will return True, else return False |

**PHP Example:**
```php
<?php
$client = new Client();
$headers = [
  'Accesstoken' => 'TOKEN_ID'
];
$body = '{
  "source_image": "Base64 encoded",
  "target_image": "Base64 encoded",
  "trx_id": "alphanumericalstring1234"
}';
$request = new Request('POST', 'https://fr.neoapi.id/risetai/face-api/compare-images', $headers, $body);
$res = $client->sendAsync($request)->wait();
echo $res->getBody();
```

---

## Notes

- Replace `TOKEN_ID` with your actual access token
- All images must be Base64 encoded JPG or PNG format
- The `trx_id` parameter is used for transaction logging and debugging purposes
- Similarity and confidence levels are returned as float values between 0.0 and 1.0
- The default similarity threshold is typically set to 0.75 (75%)
