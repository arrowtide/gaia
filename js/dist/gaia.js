window.gaia = {
    onInteraction: () => {
        return new Promise(function(resolve, reject) {
            const events = ['touchstart', 'mouseover', 'scroll', 'keydown'];

            const complete = () => {
                events.forEach((event) => {
                    window.removeEventListener(event, complete);
                });

                resolve();
            }

            events.forEach((event) => {
                window.addEventListener(event, complete, { once: true });
            })
        });
    },

    loadScript: (url) => {
        return new Promise(function(resolve, reject) {
            let script = document.createElement('script');

            script.onload = function() { resolve(script); };
            script.onerror = function() { reject(new Error('Failed to load ' + url)); };

            script.src = url;
            script.type = 'module';
            document.head.appendChild(script);
        });
    },

    formatPrice: (price, currency) => {
        return price.toLocaleString("en-GB", { style: "currency", currency: "GBP" });
    },

    getCSRF: () => {
        return document.querySelector(`meta[name='csrf-token']`).getAttribute('content');
    },

    toast: (attributes) => {
        Alpine.store('toasts').addStatus({
            ...attributes
        });
    },

    uuid: () => {
        return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
            var r = Math.random() * 16 | 0,
                v = c === 'x' ? r : r & 0x3 | 0x8;
            return v.toString(16);
        });
    }
}



// doOnInteraction: () => {
//     return new Promise(function(resolve, reject) {
//         let script = document.createElement('script');

//         script.onload = function() { resolve(script); };
//         script.onerror = function() { reject(new Error('Failed to load ' + path_to_script)); };

//         script.src = path_to_script;
//         document.head.appendChild(script);
//     });
// },
