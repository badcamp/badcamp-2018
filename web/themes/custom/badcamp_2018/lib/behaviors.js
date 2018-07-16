(function(Drupal, $) {

  Drupal.behaviors.foundation = {
    attach:function(context) {
      $(context).foundation();
    }
  };

  Drupal.behaviors.equalheight = {
      attach:function(context) {
          $(document).ready(function(){
              if ($('.path-registration').length > 0) {
                  $('.sync-description-height').syncHeight({'updateOnResize': true});
                  $('h4').syncHeight({'updateOnResize': true});
              }
          });
      }
  }

}(Drupal, jQuery));