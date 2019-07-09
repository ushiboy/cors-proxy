# CORS Proxy

CORS Proxy is a proxy that relays cross-site HTTP requests and gets the response of the target URL.

Deploy to heroku and use it.

## Requirements

* PHP >= 7
* [The Heroku Command Line Interface](https://devcenter.heroku.com/articles/heroku-cli)

## Usage

### Setting and Deploy

1. Clone the repository and move it to the root directory.

```
$ git clone https://github.com/ushiboy/cors-proxy
$ cd cors-proxy
```

2. Create a heroku application.

```
$ heroku create
```

3. Execute the `bin/generate_key` command to generate the key and the digest.

```
$ bin/generate_key
[Key]: <generated_key>
[Digest]: <generated_key_digest>
```

4. Set `AUTH_KEY_DIGEST` and `ALLOW_ORIGIN` as environment variables of heroku application. When there are multiple permitted origins, specify `ALLOW_ORIGIN` separated by commas.

```
$ heroku config:set AUTH_KEY_DIGEST='<generated_key_digest>' ALLOW_ORIGIN=http://localhost:8080,http://192.168.1.100:8080
```

5. Push and deploy the repository.

```
$ git push heroku master
```

### Access by client

Send HTTP request to deployed application and use it.

#### Parameters

##### Headers

| name | value example | required | description |
| -- | -- | -- | -- |
| Authorization | Bearer <generated_key> | &#10003; | Registered authentication key. |
| Origin | http://localhost:8080 | &#10003; | Source origin (automatically given by browser). |
| X-From-Charset | SJIS |  | Character code of target content (see [Supported Character Encodings](https://www.php.net/manual/en/mbstring.supported-encodings.php)). CORS Proxy encodes from specified character code to UTF-8. |

##### Query String

| name | value example | required | description |
| -- | -- | -- | -- |
| q | http://www.example.com/data.txt | &#10003; | Target content URL. |


### Example Usage

#### fetch (browser)

```javascript
const result = await fetch(`https://<your_app_name>.herokuapp.com/?q=${encodeURIComponent('http://www.example.com/data.txt')}`, {
  headers: {
    'Authorization': `Bearer <generated_key>`,
    'X-From-Charset': 'SJIS'
  }
});
```

#### curl

```
$ curl -H "Authorization: Bearer <generated_key>" \
    -H "Origin: http://localhost:8080" \
    -H "X-From-Charset: SJIS" \
    "https://<your_app_name>.herokuapp.com/?q=http://www.example.com/data.txt"
```

## Change Log

### 0.1.0

Initial CORS Proxy release.

## License

MIT
