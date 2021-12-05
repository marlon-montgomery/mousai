# php-kiss-base58
KISS implementation of base58 and base58 check

## Usage

```php
Base58Codec::checkEncode('cd1400c7e9cd6e20a157750ce9172f6a8775112648e56e1aafc148496a513ed115f0af');
// = 3JuEUFpEgcrr2h3RkoyUwQo4SVvo3jQpFBS672mw1ffRh39y2gC7sM
```

```php
Base58Codec::encode('c7e9cd6e20a157750ce9172f6a8775112648e56e1aafc148496a513ed115f0af');
//  = 2z41adftbuTvXzkxxhhg8cFvZLKMzFv7dHgLFj8EeLEF8KvE9yRGqZjJDwyqPPZ7KKapu9rTPrbMeJFeDtVub4AZ
```

## Tests

- Check encode
- Check decode
- Encode
- Decode