jQuery(function ($) {
  'use strict';

  $('.oms-coupon-wrapper').each((i, ele) => {
    const $wrapper = $(ele);
    const $activeTimer = $wrapper.find('.oms-coupon-timer');
    const $expireTimer = $wrapper.find('.oms-coupon-expire');

    const activationTime = $wrapper.data('activation-time');
    if (activationTime <= 0) {
      $activeTimer.slideUp();
    } else {
      new Timer(activationTime + 's', {
        immediateInterval: true,
        onTimeout: () => $activeTimer.slideUp(),
        onInterval: (t) => $activeTimer.text(t.toString('s')),
      });
    }

    const expirationTime = $wrapper.data('expiration-time');
    if (expirationTime > 0) {
      new Timer(expirationTime + 's', {
        immediateInterval: true,
        onTimeout: () => {
          $wrapper.addClass('oms-coupon-disable');
          $wrapper.find('.oms-coupon-user').attr('disabled', true);
          $expireTimer.empty();
        },
        onInterval: (t) => {
          if (expirationTime < 300) {
            $expireTimer.text(t.toString('s'));
          }
        },
      });
    }
  });

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
        $saveBtn.attr('disabled', true);
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
