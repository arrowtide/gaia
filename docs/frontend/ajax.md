# AJAX
Gaia comes with a tiny Fetch wrapper called Wretch. This takes away some of the boilerplate code required to make a request.

Included is a preset function that fills in some of the options for you, further streamlining the request. It will automatically resolve as JSON, which is the equivilent of the `response.json()` method.

```js
gaia.fetch = wretch()
    .options({
        headers: {
            "Content-Type": "application/json",
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .errorType("json")
    .resolve(r => r.json());
```

You can use it in your application:

```js
const response = await gaia.fetch.url('/!/your-url').post({
    secret_sauce: 'extra-spicy',
    who: 'mystery-guest',
});
```

- [Wretch GitHub](https://github.com/elbywan/wretch)
- [Wretch Docs](https://elbywan.github.io/wretch/api/index.html)


