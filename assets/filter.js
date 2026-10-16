jQuery(function ($) {

  // FILTER
  $('.pf-filters').on('click', 'button', function () {
    var cat = $(this).data('cat');

    $.ajax({
      url: pfData.ajaxUrl,
      type: 'POST',
      data: {
        action: 'pf_filter',
        nonce: pfData.nonce,
        cat: cat
      },
      success: function (res) {
        // console.log(res);
        if (res.success) {
          $('#pf-grid').html(res.data.html);
        }
      },
      error: function () {
        console.log('Request failed');
      }
    });
  });
 
  // LIKE 
  $('#pf-grid').on('click', '.pf-like', function () {
    var $btn = $(this);

    $.post(
      pfData.ajaxUrl,
      {
        action: 'pf_like',
        nonce: pfData.nonce,
        id: $btn.data('id')
      },
      function (res) {
        if (res.success) {
          $btn.find('span').text(res.data.likes);
        }
      }
    );
  });

});