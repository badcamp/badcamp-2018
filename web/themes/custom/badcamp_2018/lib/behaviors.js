(function(Drupal, $) {

  Drupal.behaviors.foundation = {
    attach:function(context) {
      $(context).foundation();
    }
  };

  Drupal.behaviors.equalheight = {
      attach:function(context) {
          $(document).ready(function(){
          });
      }
  }

}(Drupal, jQuery));