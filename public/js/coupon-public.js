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
        (t) => $countdown.slideUp(),
        () => $countdown.text(timer.toString('s'))
      );
      $countdown.text(timer.toString('s'));
    });
  });
})(jQuery);
