var examRecordDataTables;
$(document).ready(function () {
  $('body').on('click', '#exam-record #recordData tbody button.view', function () {
    const data = examRecordDataTables.row($(this).closest('tr')).data();
    $.ajax({
      url: '/admin/exam/record/' + data.id,
      type: 'POST',
      dataType: 'json',
      success: function (res) {
        var body = '<table class="table table-sm table-bordered table-hover"><thead><tr><th>问题</th><th>选项</th><th>正确答案</th><th>用户答案</th></tr></thead><tbody>';
        res.questions.forEach(function (value, key) {
          body += '<tr><td>' + value.question + '</td><td>' + value.answerItem.join('<br>') + '</td><td>' + value.answer.join(',') + '</td><td>' + res.answer[key] + '</td></tr>'
        });
        body += '</tbody></table>';
        modalDialog(data.name + '答题情况', body, 'modal-xl');
      },
      error: errorDialog
    });
  });
});
