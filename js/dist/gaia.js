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

    formatPrice: (price, locale, currency) => {
        // Since the code comes from Statamic it should be in POSIX format, we 
        // will attempt to convert to a valid BCP 47 locale.
        locale = locale.replace('_', '-');
        
        return price.toLocaleString(locale, { style: "currency", currency: currency });
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
    },

    waitForAlpineStore(name, interval = 75) {
        return new Promise((resolve) => {

          // Check immediately if the store is available
          if (window.Alpine?.store && Alpine.store(name) !== undefined) {
            resolve();
            return;
          }
      
          // Otherwise, wait for the store to be defined
          const check  = setInterval(() => {
            if (window.Alpine?.store && Alpine.store(name) !== undefined) {
                clearInterval(check); // Stop checking once it's available
                resolve(); // Resolve the promise once store is found
            }
          }, interval);
        });
    },

    waitForAlpine() {
        return new Promise((resolve) => {

          // Check immediately if the store is available
          if (window.Alpine?.store) {
            resolve();
            return;
          }
      
          // Otherwise, wait for the store to be defined
          const check  = setInterval(() => {
            if (window.Alpine?.store) {
                clearInterval(check); // Stop checking once it's available
                resolve(); // Resolve the promise once store is found
            }
          }, 75);
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
