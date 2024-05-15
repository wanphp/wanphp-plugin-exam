$(document).ready(function () {
  $('body').on('submit', '#exam-edit #examForm', function (e) {
    if (e.target.checkValidity()) {
      $.ajax({
        url: $(e.target).attr('action'),
        data: new FormData(e.target),
        type: 'POST',
        cache: false,
        contentType: false,
        processData: false,
        dataType: 'json',
        beforeSend: function (xhr) {
          xhr.setRequestHeader("X-HTTP-Method-Override", $(e.target).attr('method'));
        },
        success: function (data) {
          console.log(data);
          window.location.hash = '/admin/exam';
        },
        error: errorDialog
      });
      return false;
    }
  }).on('click', '#exam-edit #cropCover', function (e) {
    cropper('裁剪封面，微信分享时使用', basePath + '/admin/files', 240, 240, e.currentTarget, function (data, preview) {
      preview.html('<img src="' + data.url + '" class="w-100 h-100 object-fit-cover">').next('input').val(data.url);
    });
  });
});
