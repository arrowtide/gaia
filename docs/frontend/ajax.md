# AJAX

AJAX (Asynchronous JavaScript and XML) is used around the frontend to make requests to the backend and update the UI without refreshing the page.

We use the standard [`fetch()`](https://developer.mozilla.org/en-US/docs/Web/API/Fetch_API/Using_Fetch) API to make requests.

Here a simple example:

```js
const response =  await fetch('/!/gaia/account/wishlists', {
    method: 'GET',
    headers: {
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
    }
});
```
