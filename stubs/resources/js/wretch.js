/**
 * @see https://github.com/elbywan/wretch
 */

import wretch from 'wretch';

gaia.fetch = wretch()
    .options({
        headers: {
            "Content-Type": "application/json",
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .errorType("json")
    .resolve(r => r.json());
