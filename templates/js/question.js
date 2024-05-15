var questionDataTables;
$(document).ready(function () {
  $('body').on('click', '#exam-question #questionData tbody tr td.details-control', function () {
    const row = questionDataTables.row($(this).closest('tr'));
    const data = row.data();
    if (row.child.isShown()) {
      $(this).html('<i class="fas fa-plus-circle"></i>');
      row.child.hide();
    } else {
      $(this).html('<i class="fas fa-minus-circle"></i>');
      if (!row.child()) {
        row.child('<div class="row"><div class="col-sm-6">' + data.answerItem.join("<br>") + '</div>' +
          '<div class="col-sm-6">' + data.answer.join("<br>") + '</div></div>').show();
      } else {
        row.child.show();
      }
    }
  }).on('click', '#exam-question #questionData tbody button', function () {
    var data = questionDataTables.row($(this).parents('tr')).data();
    console.log(data);
    if ($(this).hasClass('edit')) {
      $('#exam-question #questionForm').attr('action', '/admin/exam/question/' + data.id).attr('method', 'PUT');
      $("#exam-question #questionForm textarea[name='question']").val(data.question);
      $("#exam-question #questionForm textarea[name='answerItem']").val(data.answerItem.join("\n"));
      $("#exam-question #questionForm textarea[name='answer']").val(data.answer.join("\n"));
      $("#exam-question #questionForm select[name='orderly']").val(data.orderly);
      $('#exam-question #modal-addQuestion').modal('show');
    }
    if ($(this).hasClass('del')) {
      var delRow = $(this).parents('tr');
      dialog('删除竞赛题目', '是否确认删除竞赛题目', function () {
        $.ajax({
          url: '/admin/exam/question/' + data.id,
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
    }
  }).on('submit', '#exam-question #questionForm', function (e) {
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
          if ($(e.target).attr('method') === 'PUT') {
            var id = $(e).attr('action').split('/').pop();
            datatables.row($("tr[id='" + id + "']")).data(data).draw(false);
          } else {
            datatables.row.add(data).draw(false);
          }
          $('#modal-addQuestion').modal('hide');
        },
        error: errorDialog
      });
      return false;
    }
  }).on('hidden.bs.modal', '#exam-question #modal-addQuestion', function () {
    $('#exam-question #questionForm').attr('action', '/admin/exam/question').attr('method', 'POST')[0].reset();
  });
});
