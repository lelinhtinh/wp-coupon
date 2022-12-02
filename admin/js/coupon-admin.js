jQuery(function ($) {
  'use strict';

  const $form = $('#oms_coupon_form');
  const $helpText = $('#help_text');
  const $list = $('#the-list');
  const $code = $('#code');
  const currencySymbol = '₫';

  $list.on('click', '.oms-coupon-shortcode, .oms-coupon-copy', (e) => {
    e.preventDefault();
    const $shortcode = $(e.target);
    const shortcodeContent = $shortcode.text();

    navigator.clipboard
      .writeText(
        !$shortcode.hasClass('oms-coupon-copy')
          ? `[oms_coupon id="${$shortcode.data('id')}"]`
          : $shortcode.data('code').toUpperCase()
      )
      .then(
        () => {
          $shortcode.text('Coppied!');
          setTimeout(() => {
            $shortcode.text(shortcodeContent);
          }, 400);
        },
        () => {
          window.getSelection().selectAllChildren(e.target);
        }
      );
  });

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
        const { ID, code, value, type, limit, activated_at, expired_at } =
          data.data;
        if (ID === 0) {
          $helpText.text('Server error!');
          return;
        }

        $code.trigger('focus');
        $form[0].reset();
        const page = new URLSearchParams(location.search).get('page');
        const pageParam = encodeURIComponent(page);

        const row = `
<tr>
  <th scope="row" class="check-column">
    <input type="checkbox" name="coupon[]" value="${ID}" />
  </th>
  <td
    class="code column-code has-row-actions column-primary"
    data-colname="Coupon Code"
  >
    <span class="oms-coupon-code">${code}</span>
    <span data-id="${ID}" title="Click to Copy" class="oms-coupon-shortcode">[oms_coupon id="${ID}"]</span>
    <div class="row-actions">
      <span class="hide">
        <a
          href="admin.php?page=${pageParam}&amp;action=hide&amp;coupon=${ID}&amp;_wpnonce=${
          oms_coupon_admin_ajax.nonce
        }"
        >Hide</a>
      </span>
      |
      <span class="delete">
        <a
          href="admin.php?page=${pageParam}&amp;action=delete&amp;coupon=${ID}&amp;_wpnonce=${
          oms_coupon_admin_ajax.nonce
        }"
        >Delete</a>
      </span>
    </div>
  </td>
  <td class="value column-value" data-colname="Discount">
    ${
      !value
        ? ''
        : type === 'numeric'
        ? value.toString().replace(/\d(?=(\d{3})+$)/g, '$&,')
        : value
    }${!value ? '' : type === 'percentage' ? '%' : currencySymbol}
  </td>
  <td class="limit column-limit" data-colname="Usage Limit">
    <span class="oms-coupon-limit">${limit ?? ''}</span>
  </td>
  <td class="activated_at column-activated_at" data-colname="Activation Date">${
    activated_at ?? ''
  }</td>
  <td class="expired_at column-expired_at" data-colname="Expiration Date">
    <span class="oms-coupon-expired_at">${expired_at ?? ''}</span>
  </td>
  <td class="number_of_uses column-number_of_uses" data-colname="N. Uses">
    <span class="oms-coupon-number_of_uses">0</span>
  </td>
  <td class="used_by column-used_by" data-colname="Used By"></td>
</tr>
`;
        const $noItem = $list.find('.no-items');
        if ($noItem.length) {
          $noItem.replaceWith(row);
        } else {
          $list.prepend(row);
          $('.displaying-num').text((e, val) =>
            val.replace(/\d+/, (d) => parseInt(d, 10) + 1)
          );
        }
      })
      .catch((e) => {
        $helpText.text(e?.responseJSON?.message ?? 'An unknown error');
      });
  });
});
