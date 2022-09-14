(function (Drupal, once) {
  Drupal.behaviors.copytoken = {
    attach: function (context, settings) {
      if (settings.copytoken && settings.copytoken.value){
        if (settings.copytoken.value !== '') {
          var text = settings.copytoken.value;
          navigator.permissions.query({ name: "clipboard-write" }).then((result) => {
            if (result.state == "granted" || result.state == "prompt") {
              navigator.clipboard.writeText(text).then(() => {
                console.log('Adres dodany do clipboard ' , text);
              });
            }
          });
        }
      }
    }
  }

})(Drupal, once);