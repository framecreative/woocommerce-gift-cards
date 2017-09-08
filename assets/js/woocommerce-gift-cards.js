jQuery(document).ready(function($) {
  $('a.showgiftcard').click(function() {
    $('.checkout_giftcard').slideToggle();
    $('#giftcard_code').focus();
    return false;
  });

  $('form.checkout_giftcard').submit(function() {
    var $form = $(this);

    if ($form.is('.processing')) return false;

    $form.addClass('processing').block({
      message: null,
      overlayCSS: {
        background: '#fff',
        opacity: 0.6,
      },
    });

    var data = {
      action: 'woocommerce_apply_giftcard',
      security: 'apply-giftcard',
      giftcard_code: $form.find('input[name=giftcard_code]').val(),
    };

    $.ajax({
      type: 'POST',
      url: woocommerce_params.ajax_url,
      data: data,
      success: function(code) {
        $('.woocommerce-error, .woocommerce-message').remove();
        $form.removeClass('processing').unblock();

        if (code) {
          $form.before(code);
          $form.slideUp();

          $('body').trigger('update_checkout');
        }
      },
      dataType: 'html',
    });
    return false;
  });
});
