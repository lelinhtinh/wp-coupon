(function ($) {
  'use strict';

  $(function () {
    const $form = $('#oms_coupon_form');
    const $helpText = $('#help_text');

    $form.on('submit', (e) => {
      e.preventDefault();
      $helpText.text('');
      $form[0].checkValidity();

      const data = $form.serializeArray().reduce((curr, { name, value }) => {
        curr[name] = value || null;
        return curr;
      }, {});

      $.post(oms_coupon_admin_ajax.ajax_url, {
        _ajax_nonce: oms_coupon_admin_ajax.nonce,
        action: 'oms_coupon_create',
        ...data,
      })
        .done((data) => {
          $form[0].reset();
          console.log('$form.on ~ data', data);
        })
        .catch((e) => {
          $helpText.text(e?.responseJSON?.message ?? 'An unknown error');
        });
    });
  });
})(jQuery);
