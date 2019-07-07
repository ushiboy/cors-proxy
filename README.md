# CORS Proxy

CORS Proxy is a proxy that relays cross-site HTTP requests and gets the response of the target URL.

Deploy to heroku and use it.


## Usage

### Generate authentication key and Set environment variables

Execute the `bin/generate_key` command to generate the key and the digest.

```
$ bin/generate_key
[Key]: <generated_key>
[Digest]: <generated_key_digest>
```

Set `AUTH_KEY_DIGEST` and `ALLOW_ORIGIN` as environment variables of heroku application.

```
$ heroku config:set AUTH_KEY_DIGEST=<generated_key_digest> ALLOW_ORIGIN=http://localhost:8080,http://192.168.1.100:8080
```

## License

MIT
