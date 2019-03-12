Build a REST API with Symfony && FOSRestBundle

* [x] Serialization
* [x] Deserialization
* [ ] Paginate a list of resources
* [x] Validate resources
* [ ]content negotiation
* [ ] Error Management
* [ ] Error handling via a listener on the event kernel.exception
* [ ] Api Documentation

Result test using Postman

GET URI

```curl
curl -X GET \
  http://127.0.0.1:8000/quote/5 \
  -H 'Cache-Control: no-cache' \
```

Response:

```json
{
    "id": 5,
    "text": "objects in a program should be replaceable with instances of their subtypes\nwithout altering the correctness of that program",
    "author": {
        "id": 4,
        "fullName": "Wikipedia"
    }
}
```


POST URI

```php
<?php

$request = new HttpRequest();
$request->setUrl('http://127.0.0.1:8000/quote/9');
$request->setMethod(HTTP_METH_GET);

$request->setHeaders(array(
  'Postman-Token' => 'f4e4e2b6-4438-4a6d-9174-ad2034e67fab',
  'Cache-Control' => 'no-cache',
  'Content-Type' => 'application/json'
));

try {
  $response = $request->send();

  echo $response->getBody();
} catch (HttpException $ex) {
  echo $ex;
}
```

Response:
```json
{
    "id": 2,
    "text": "Any fool can write code that a computer can understand. Good\nprogrammers write code that humans can understand.",
    "author": {
        "id": 2,
        "fullName": "Martin Fowler"
    }
}
```