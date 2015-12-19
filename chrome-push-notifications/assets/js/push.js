'use strict';

var _webPushConfig = {
  debug: pn_vars.debug
};

window.addEventListener('load', function() {  

  if (window.location.protocol != "https:") {
    if(_webPushConfig.debug) console.log( 'Only Https is allowed.');
    return;
  }

  if ('serviceWorker' in navigator) {  
    navigator.serviceWorker.register(pn_vars.sw_path).then(init);  
  } else {  
     if(_webPushConfig.debug) console.warn('Service workers aren\'t supported in this browser.');  
  }  

  function init() {  
    if(_webPushConfig.debug) console.warn('Init started.');
    if (!('showNotification' in ServiceWorkerRegistration.prototype)) {  
      if(_webPushConfig.debug) console.warn('Notifications aren\'t supported.');  
      return;  
    }

    if (Notification.permission === 'denied') {  
      if(_webPushConfig.debug) console.warn('The user has blocked notifications.');  
      return;  
    }

    if (!('PushManager' in window)) {  
      if(_webPushConfig.debug) console.warn('Push messaging isn\'t supported.');  
      return;  
    }

      navigator.serviceWorker.ready.then(function(serviceWorkerRegistration) {  

        serviceWorkerRegistration.pushManager.getSubscription()  
          .then(function(subscription) {  
            if(_webPushConfig.debug) console.warn('Subscribed permisie. Getting current subscription status.');
            if (!subscription) {  
              if(_webPushConfig.debug) console.log('No subscription, executing subscribe().');
              subscribe(); 
              return;  
            }
          
            if(_webPushConfig.debug) console.log('Sending the SubscriptionId to the server.');
            return sendSubscriptionToServer(subscription);

          })  
          .catch(function(err) {  
            console.warn('Error during getSubscription()', err);  
          });  
      });  
  }

  function subscribe(){
    if(_webPushConfig.debug) console.warn('Subscription started.');
    navigator.serviceWorker.ready.then(function(serviceWorkerRegistration) {  
      serviceWorkerRegistration.pushManager.subscribe({userVisibleOnly: true})  
        .then(function(subscription) {  
          if(_webPushConfig.debug) console.warn('Before sending data to server..');
          return sendSubscriptionToServer(subscription);  
        })  
        .catch(function(e) {  
          if (Notification.permission === 'denied') {  
            if(_webPushConfig.debug) console.warn('Permission for Notifications was denied');  
          } else {      
            if(_webPushConfig.debug) console.error('Unable to subscribe to push.', e);  
          }  
        });  
    });  
  }

  function sendSubscriptionToServer(data){

    var data = {
      'action': 'pn_register_device',
      'regId': encodeURIComponent(data.endpoint)
    };

    if(_webPushConfig.debug) console.log('Sending data to server: ');
    if(_webPushConfig.debug) console.log(data);
    
    jQuery.post(pn_vars.ajaxurl, data, function(response) {
      console.log(response);
    });

  }

});