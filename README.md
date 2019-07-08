# CORS Proxy

CORS Proxy is a proxy that relays cross-site HTTP requests and gets the response of the target URL.

Deploy to heroku and use it.


## Usage

### Setting and Deploy

Clone the repository and move it to the root directory.

```
$ git clone https://github.com/ushiboy/cors-proxy
$ cd cors-proxy
```

Create a heroku application.

```
$ heroku create
```

Execute the `bin/generate_key` command to generate the key and the digest.

```
$ bin/generate_key
[Key]: <generated_key>
[Digest]: <generated_key_digest>
```

Set `AUTH_KEY_DIGEST` and `ALLOW_ORIGIN` as environment variables of heroku application.

```
$ heroku config:set AUTH_KEY_DIGEST='<generated_key_digest>' ALLOW_ORIGIN=http://localhost:8080,http://192.168.1.100:8080
```

Push and deploy the repository.

```
$ git push heroku master
```

### Access by client

```
$ curl -H "Authorization: Bearer <generated_key>" \
    -H "Origin: http://localhost:8080" \
    -H "X-From-Charset: SJIS" \
    "https://xxxxxxxx.herokuapp.com/?q=http://www.example.com/data.txt"
```

## License

MIT
