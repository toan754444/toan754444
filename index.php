<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Facebook Auto Tool</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background-color: #f4f7fa;
      font-family: 'Segoe UI', sans-serif;
      color: #333;
    }
    .header {
      text-align: center;
      padding: 30px 0;
      background-color: #006eff;
      color: #fff;
      margin-bottom: 30px;
    }
    .card {
      border: none;
      border-radius: 12px;
      background-color: #ffffff;
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
    }
    .form-label {
      font-weight: 600;
      color: #333;
    }
    .form-control, .form-select {
      background-color: #f9f9f9;
      border-radius: 6px;
    }
    .btn-success {
      background: #00c853;
      border: none;
      font-weight: bold;
    }
    .btn-danger {
      background: #ff5252;
      border: none;
      font-weight: bold;
    }
    #resultBox {
      background-color: #1c1e21;
      color: #9df59b;
      font-family: monospace;
      font-size: 14px;
      padding: 15px;
      border-radius: 6px;
    }
    #progressInfo {
      font-weight: 500;
      color: #ff9800;
    }
    .section-title {
      font-size: 1.25rem;
      margin-top: 30px;
    }
  </style>
</head>
<body>

<div class="header">
  <h1>🚀 Facebook Auto Share & Comment</h1>
  <p class="mb-0">Khong Truong Tool Auto Share & Comment</p>
</div>

<div class="container">
  <div class="card p-4">
    <form id="shareForm">
      <div class="row">
        <div class="col-md-6 mb-3">
          <label class="form-label">🔧 Chức năng:</label>
          <select name="action_type" class="form-select" required>
            <option value="share">🔥 Auto Share</option>
            <option value="comment">💬 Auto Comment</option>
          </select>
        </div>
        <div class="col-md-6 mb-3">
          <label class="form-label">📁 File Cookie (.txt):</label>
          <input type="file" name="cookie_file" class="form-control" required />
        </div>

        <div class="col-md-6 mb-3">
          <label class="form-label">🆔 ID bài viết:</label>
          <input type="text" name="id_share" class="form-control" placeholder="VD: 1000..." required />
        </div>
        <div class="col-md-6 mb-3" id="commentContentBox" style="display: none;">
          <label class="form-label">💬 Nội dung comment:</label>
          <input type="text" name="comment_content" class="form-control" placeholder="VD: Bài hay quá!" />
        </div>

        <div class="col-md-4 mb-3">
          <label class="form-label">⏱️ Delay (giây):</label>
          <input type="number" name="delay" class="form-control" value="2" min="1" required />
        </div>
        <div class="col-md-4 mb-3">
          <label class="form-label">🔁 Số lượt:</label>
          <input type="number" name="limit" class="form-control" value="10" min="1" required />
        </div>
        <div class="col-md-4 mb-3">
          <label class="form-label">⚙️ Số luồng:</label>
          <input type="number" name="threads" class="form-control" value="1" min="1" max="10" required />
        </div>
      </div>

      <div class="d-grid gap-2">
        <button type="submit" class="btn btn-success btn-lg">🚀 Bắt đầu</button>
        <button type="button" id="stopBtn" class="btn btn-danger btn-lg" disabled>⛔ Dừng</button>
      </div>
    </form>
  </div>

  <div class="section-title">📊 Tiến trình:</div>
  <div id="progressInfo" class="mb-2">⏳ Chưa bắt đầu</div>
  <div id="resultBox" style="height: 300px; overflow-y: auto;"></div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
let totalShared = 0;
let maxShare = 0;
let delayBetween = 0;
let autoRun = false;
let threads = 1;

function runThread(id, formDataClone) {
  if (!autoRun || totalShared >= maxShare) return;

  formDataClone.set('limit', 1);

  $.ajax({
    url: 'share.php',
    type: 'POST',
    data: formDataClone,
    contentType: false,
    processData: false,
    success: function (data) {
      $('#resultBox').append(`[Luồng ${id}] ${data}\n`);
      $('#resultBox').scrollTop($('#resultBox')[0].scrollHeight);
      totalShared++;
      updateProgress();

      if (autoRun && totalShared < maxShare) {
        setTimeout(() => runThread(id, formDataClone), delayBetween * 1000);
      }
    },
    error: function () {
      $('#resultBox').append(`[Luồng ${id}] ❌ Lỗi kết nối\n`);
    }
  });
}

function updateProgress() {
  const remaining = maxShare - totalShared;
  const timeLeft = Math.ceil(remaining / threads) * delayBetween;
  $('#progressInfo').text(`✅ Đã thực hiện ${totalShared}/${maxShare} — ⏳ Ước tính còn ${timeLeft}s`);
}

$('#shareForm').on('submit', function (e) {
  e.preventDefault();

  totalShared = 0;
  maxShare = parseInt($('input[name="limit"]').val());
  delayBetween = parseInt($('input[name="delay"]').val());
  threads = parseInt($('input[name="threads"]').val());
  autoRun = true;

  $('#resultBox').text('🚀 Đang chạy...\n');
  $('#stopBtn').prop('disabled', false);
  updateProgress();

  for (let i = 1; i <= threads; i++) {
    const formClone = new FormData($('#shareForm')[0]);
    runThread(i, formClone);
  }
});

$('#stopBtn').on('click', function () {
  autoRun = false;
  $('#progressInfo').text("🛑 Đã dừng quá trình.");
  $('#stopBtn').prop('disabled', true);
});

$('select[name="action_type"]').on('change', function () {
  $('#commentContentBox').toggle($(this).val() === 'comment');
});
</script>
</body>
</html>
