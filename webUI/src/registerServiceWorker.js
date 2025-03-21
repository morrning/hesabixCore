if('serviceWorker' in navigator) {
    navigator.serviceWorker
        .register('/u/service-worker.js').then(registration => {
            console.log('Service worker registration succeeded:' , registration);
        }).catch(err => {
            console.log('Service worker registration failed:' , err);
        })
}