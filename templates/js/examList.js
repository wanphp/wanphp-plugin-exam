var examDataTables;
$(document).ready(function () {
  $('body').on('click', '#exam-list #examData tbody button.import', function () {
    var data = examDataTables.row($(this).closest('tr')).data();
    $.uploadFile({
      'url': basePath + '/admin/import/question/' + data.id,
      'accept': 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.ms-excel',
      'ext': 'xlsx'
    }, function (res) {
      if (res) {
        Toast.fire({icon: 'success', title: '导入完成'});
      } else {
        Toast.fire({icon: 'error', title: res.description});
      }
    });
  }).on('click', '#exam-list #examData tbody button.del', function () {
    var data = examDataTables.row($(this).closest('tr')).data();
    var delRow = $(this).closest('tr');
    dialog('删除知识竞赛', '是否确认删除知识竞赛,删除将同时删除竞赛题库及参与记录', function () {
      $.ajax({
        url: basePath + '/admin/exam/' + data.id,
        type: 'POST',
        headers: {"X-HTTP-Method-Override": "DELETE"},
        dataType: 'json',
        success: function (data) {
          datatables.row(delRow).remove().draw(false);
          Swal.fire({icon: 'success', title: '删除成功！', showConfirmButton: false, timer: 1500});
        },
        error: errorDialog
      });
    });
  });
});
