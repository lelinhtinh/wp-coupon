(function ($) {
  'use strict';

  $(function () {
    $('.oms-coupon-wrapper').each((i, ele) => {
      const $wrapper = $(ele);
      const $countdown = $wrapper.find('.oms-coupon-timer');
      const activationTime = $wrapper.data('activation-time');
      if (activationTime <= 0) {
        $countdown.slideUp();
        return;
      }

      const timer = new Timer(
        activationTime + 's',
        () => $countdown.slideUp(),
        (t) => $countdown.text(t.toString('s'))
      );
      $countdown.text(timer.toString('s'));
    });
  });

  $(function () {
    $(document).on('click', '.oms-coupon-user', (e) => {
      e.preventDefault();

      const $saveBtn = $(e.target);
      const $wrap = $saveBtn.closest('.oms-coupon-wrapper');
      const $helpText = $wrap.find('.oms-coupon-remaining');

      $.post(oms_coupon_user_ajax.ajax_url, {
        _ajax_nonce: oms_coupon_user_ajax.nonce,
        action: 'oms_coupon_save',
        id: e.target.dataset.id,
      })
        .done((data) => {
          if (data.status === 'ok') {
            $saveBtn.text('Saved!');
            $helpText.find('strong').text((i, v) => parseInt(v, 10) - 1);
          } else {
            $saveBtn.text('Oh no...');
            $helpText.text(data.message);
          }
        })
        .fail(() => {
          $saveBtn.text('Error!');
          setTimeout(() => {
            $saveBtn.text('Save');
            $wrap.removeClass('oms-coupon-disable');
          }, 1000);
        })
        .always(() => {
          $wrap.addClass('oms-coupon-disable');
        });
    });
  });
})(jQuery);
